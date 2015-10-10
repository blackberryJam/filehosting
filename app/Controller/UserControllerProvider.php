<?php
namespace Filehosting\Controller;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/login', function(Application $app, Request $request) {
            $userService = $app['user.service'];
            $post = $request->request->all();

            if (empty($post['email']) && empty($post['password'])) {
                return $app->redirect('/');
            }

            $user = $userService->logIn($post);

            if (\Filehosting\Service\UserService::AUTH_FAILED === $user) {
                $body = "Authentication failed.";
                return new Response($body, 200);
            }

            return $app->redirect("/user/{$user->getId()}");
        });

        $controllers->get('/logout', function(Application $app) {
            $lm = $app['user.service.login_manager'];

            if ($lm->isLoggedIn()) {
                $lm->logOut();
                $app['user.logged_in'] = false;
            }

            return $app->redirect('/');
        });

        $controllers->get('/{user}', function(Application $app, Request $request, \Filehosting\Model\User $user) {
            $visitorId = $app['user.service.login_manager']->extractUserIdFromCookies();

            if ($visitorId != $user->getId()) {
                if ($visitorId === null) {
                    return $app->redirect('/');
                }
                return $app->redirect("/user/{$visitorId}");
            }

            $files = $user->getFiles();

            $body = $app['twig']->render('user_page.html', array(
                'user' => array(
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => 'Change password...'
                ),
                'files' => $files));
            return new Response($body, 200);
        })
        ->assert('user', '\d+')
        ->convert('user', 'user.service:getUserByIdOrCreateNewIfNotExists');

        return $controllers;
    }
}
