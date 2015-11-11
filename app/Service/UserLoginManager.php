<?php
namespace Filehosting\Service;
use Filehosting\Model\User;

class UserLoginManager
{
    protected $em;
    protected $cookies;

    public function __construct(\Doctrine\ORM\EntityManager $entityManager, $cookies)
    {
        $this->em = $entityManager;
        $this->cookies = $cookies;
    }

    public function registerOrUpdateIfExists(User $user)
    {
        $this->em->persist($user);
    }

    public function logIn($id)
    {
        setcookie('id', $id, time() + 3600 * 24, '/', $_SERVER['HTTP_HOST']);
    }

    public function logOut()
    {
        setcookie('id', '', time() - 1, '/', $_SERVER['HTTP_HOST']);
    }

    public function extractUserIdFromCookies()
    {
        return $this->isLoggedIn() ? $this->cookies['id'] : null;
    }

    public function isLoggedIn()
    {
        return isset($this->cookies['id']);
    }

    public function getCookies()
    {
        return $this->cookies;
    }
}
