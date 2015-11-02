<?php
namespace Filehosting\Controller;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Filehosting\Model\Comment;

class CommentControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->post('/send', function(Application $app, Request $request) {
            $commentService = $app['comment.service'];
            $formData = $request->request->all();

            $file = $app['em']->find('\Filehosting\Model\File', (int) $formData['file']);

            $commentService->setUser($app['user']);
            $commentService->setFile($file);

            $comment = $commentService->manageFormData($formData);

            if ($comment === \Filehosting\Service\CommentService::VALIDATION_FAILED) {
                return $app->json("validation_failed");
            }

            $app['em']->flush();

            return $app->json($comment);
        });

        return $controllers;
    }
}
