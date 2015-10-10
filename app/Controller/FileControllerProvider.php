<?php
namespace Filehosting\Controller;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Filehosting\Model\File;

class FileControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/{file}', function(Application $app, File $file) {
            if (null === $file->getId()) {
                $body = 'Файл не найден.';
                return new Response($body, 404); //redirect
            }

            $values = $this->createArrayOfValues($file, $app);

            $body = $app['twig']->render('file_page.html', $values);
            return new Response($body, 200);
        })
        ->assert('file', '\d+')
        ->convert('file', 'file.service:getFileByIdOrCreateNewIfNotExists');

        $controllers->get('/{file}/remove', function(Application $app, Request $request, File $file) {
            if (null === $file->getId()) {
                $body = 'Файл не найден.';
                return new Response($body, 404); //redirect
            }

            $visitor = $app['user'];
            if ($visitor->getId() != $file->getUser()->getId()) {
                return $app->redirect("/file/{$file->getId()}");
            }

            $fileService = $app['file.service'];
            $fileService->removeFile($file);
            $app['em']->flush();

            $body = "Файл успешно удалён!";
            return new Response($body, 200);

        })
        ->assert('file', '\d+')
        ->convert('file', 'file.service:getFileByIdOrCreateNewIfNotExists');

        $controllers->get('/{file}/download', function(Application $app, File $file) {
            if (null === $file->getId()) {
                $body = 'Файл не найден.';
                return new Response($body, 404); //redirect
            }
            return new Response('', 200, array(
                "X-Sendfile" => "{$app['file.save_directory']}/{$file->getPath()}",
                "Content-Type" => "application/octet-stream",
                "Content-Disposition" => "attachment; filename=\"{$file->getOriginalName()}\""
            ));
        })
        ->assert('file', '\d+')
        ->convert('file', 'file.service:getFileByIdOrCreateNewIfNotExists');

        return $controllers;
    }

    protected function createArrayOfValues(File $file, Application $app)
    {
        $user = $file->getUser();
        $userName = $user->getName();
        $mediaInfo = $app['file.service']->getArrayOfMediaInfo($file);

        $values = array(
            'downloadUrl' => "{$app['request']->getUri()}/download",
            'removeUrl' => "{$app['request']->getUri()}/remove",
            'fileName' => $file->getOriginalName(),
            'dateUpload' => $file->getDateUpload()->format("Y-m-d, H:i:s"),
            'userName' => $userName === null ? "Anonymous" : $userName,
            'fileSize' => $file->getSize(),
            'mediaInfo' => $mediaInfo,
            'mediaInfoAudioKeys' => isset($mediaInfo['audio']) ? array_keys($mediaInfo['audio']) : array(),
            'mediaInfoVideoKeys' => isset($mediaInfo['video']) ? array_keys($mediaInfo['video']) : array(),
            'userId' => $user->getId()
        );

        return $values;
    }
}