<?php
namespace Filehosting\Controller;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SignUpControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function(Application $app, Request $request) {
            $body = $app['twig']->render('signup.html', array('user' => array(
                'name' => '', 'email' => '', 'password' => '6 chars minimum'
            )));
            return new Response($body, 200);
        })
        ->before(function(Request $request, Application $app) {
            if ($app['user.logged_in']) {
                $app->redirect('/');
            }
        });

        $controllers->post('/', function(Application $app, Request $request) {
            $userService = $app['user.service'];
            $post = $request->request->all();

            $user = $userService->manageFormData($post);

            if (\Filehosting\Service\UserService::VALIDATION_FAILED === $user) {
                $body = "Validation failed.";
                return new Response($body, 200);
            }

            $app['em']->flush();

            if (null === $user->getId()) {
                throw new Exception("Не удалось сохранить пользователя в БД.");
            }

            $lm = $app['user.service.login_manager'];
            if (!$lm->isLoggedIn()) {
                $lm->logIn($user->getId());
            }

            return $app->redirect("/user/{$user->getId()}");
        });

        return $controllers;
    }
}
