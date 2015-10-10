<?php
namespace Filehosting\Controller;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function(Application $app) {
            $body = $app['twig']->render('upload.html');
            return new Response($body, 200);
        });

        $controllers->post('/', function(Application $app, Request $request) {
            $uploadedFile = $request->files->get("fileToUpload");

            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return new Response('Ошибка загрузки файла.', 400);
            }

            $fileService = $app['file.service'];
            $fileService->manageUploadedFile($uploadedFile);

            if (!$fileService->getManagedFile()) {
                throw new Exception('Ошибка обработки файла.');
            }

            $app['em']->flush();

            $body = "http://{$_SERVER['HTTP_HOST']}/file/{$fileService->getManagedFileId()}";

            return new Response($body, 200);
        });

        return $controllers;
    }
}
