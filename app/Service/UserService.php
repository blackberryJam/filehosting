<?php
namespace Filehosting\Service;
use Filehosting\Model\User;
use Filehosting\Service\UserLoginManager;

class UserService
{
    const VALIDATION_FAILED = 0;
    const AUTH_FAILED = 1;

    protected $em;
    protected $loginManager;

    protected $validationErrors = array();

    public function __construct(\Doctrine\ORM\EntityManager $entityManager, UserLoginManager $loginManager)
    {
        $this->em = $entityManager;
        $this->loginManager = $loginManager;
    }

    public function identifyUser($cookies)
    {
        $user = $this->identifyUserById($cookies);

        if ($user) {
            return $user;
        }

        $token = $this->getTokenOrCreateIfNotExists($cookies);
        return $this->getUserByTokenOrCreateNewIfNotExists($token);
    }

    public function login($formData)
    {
        $user = $this->authenticate($formData);

        if (!$user) {
            return self::AUTH_FAILED;
        }

        $this->loginManager->logIn($user->getId());
        return $user;
    }

    protected function authenticate(array $formData = array())
    {
        $user = $this->getUserByEmail($formData['email']);
        if (!$user) {
            return;
        }

        $result = password_verify($formData['password'], $user->getHashedPassword());
        if (!$result) {
            return;
        }

        return $user;
    }

    public function manageFormData(array $formData = array())
    {
        $isLoggedIn = $this->loginManager->isLoggedIn();

        if ($isLoggedIn) {
            $mode = 'edit';
        } else {
            $mode = 'register';
        }
        $this->validateFormData($formData, $mode);

        if (!empty($this->validationErrors)) {
            return self::VALIDATION_FAILED;
        }

        if ($isLoggedIn) {
            $user = $this->identifyUserById($this->loginManager->getCookies());
            $user = $this->createActualUserEntity($formData, $user);
        } else {
            $user = $this->createActualUserEntity($formData, new User());
        }

        $this->loginManager->registerOrUpdateIfExists($user);
        return $user;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    protected function validateFormData($formData, $mode)
    {
        if (empty($formData)) {
            $this->validationErrors['form'] = "POST-запрос не содержит данных.";
            return;
        }
        $this->validateUserName($formData['username']);
        $this->validateEmail($formData['email'], $mode);
        $this->validatePassword($formData['password'], $formData['password_repeat']);
    }

    protected function createActualUserEntity(array $formData = array(), User $user)
    {
        $user->setName($formData['username']);
        $user->setEmail($formData['email']);
        $user->setHashedPassword(password_hash($formData['password'], PASSWORD_DEFAULT));

        return $user;
    }

    protected function identifyUserById($cookies)
    {
        if (!isset($cookies['id'])) {
            return;
        }
        return $this->getUserByIdOrCreateNewIfNotExists($cookies['id']);
    }

    protected function validateUserName($userName)
    {
        $string = (string) $userName;
        $pattern = '/^[-`a-zA-Zа-яА-ЯёЁ\\s]{2,40}$/ui';
        if (!preg_match($pattern, $string)) {
            $this->validationErrors['username'] = "Неверный формат username.";
        }
    }

    protected function validateEmail($email, $mode)
    {
        $string = (string) $email;
        $pattern = '/^.{1,70}@.{1,70}$/ui';
        if (!preg_match($pattern, $string)) {
            $this->validationErrors['email'] = 'Неверный формат email.';
        }
        if ($this->isEmailReserved($email, $mode)) {
            $this->validationErrors['email'] = 'Указанный email занят.';
        }
    }

    protected function isEmailReserved($email, $mode)
    {
        $user = $this->getUserByEmail($email);
        if ($user && $mode === 'register') {
            return true;
        }
        if ($user && $mode === 'edit') {
            $id = $this->loginManager->extractUserIdFromCookies();
            if ($id != $user->getId()) {
                return true;
            }
        }
        return false;
    }

    protected function validatePassword($password)
    {
        $length = mb_strlen((string) $password);
        if ($length > 255 || $length < 6) {
            $this->validationErrors['password'] = "Неверный формат password.";
        }
    }

    protected function getTokenOrCreateIfNotExists($cookies)
    {
        if (!isset($cookies['token'])) {
            $token = $this->setCookieToken();
        } else {
            $token = $cookies['token'];
        }
        return $token;
    }

    protected function setCookieToken()
    {
        $string = $this->generateCookieToken();
        setcookie('token', $string, time() + 3600 * 24 * 365 * 10, '/', $_SERVER['HTTP_HOST']);
        return $string;
    }

    protected function generateCookieToken()
    {
        $string = "";
        for ($i = 0; $i < 5; $i++) {
            $string = md5((string) mt_rand());
            if (!$this->getUserByToken($string)) {
                return $string;
            }
        }
        throw new Exception('Не удалось сгенерировать токен.');
    }

    protected function getUserByToken($token)
    {
        $userRepo = $this->em->getRepository('\Filehosting\Model\User');
        $user = $userRepo->findOneBy(array('cookieToken' => $token));
        return $user;
    }

    protected function getUserByEmail($email)
    {
        $userRepo = $this->em->getRepository('\Filehosting\Model\User');
        $user = $userRepo->findOneBy(array('email' => $email));
        return $user;
    }

    public function getUserByIdOrCreateNewIfNotExists($id)
    {
        $user = $this->em->find('\Filehosting\Model\User', $id);
        if ($user) {
            return $user;
        }
        return new User();
    }

    protected function getUserByTokenOrCreateNewIfNotExists($token)
    {
        $user = $this->getUserByToken($token);
        if (!$user) {
            $user = new User();
            $user->setCookieToken($token);
            $this->saveUserToDataBase($user);
        }
        return $user;
    }

    protected function saveUserToDataBase(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
