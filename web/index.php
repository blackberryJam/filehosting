<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . "/app/bootstrap.php";
require_once dirname(__DIR__) . "/app/app.php";


$app->mount('/', new Filehosting\Controller\IndexControllerProvider());

$app->mount('/file', new Filehosting\Controller\FileControllerProvider());

$app->mount('/signup', new Filehosting\Controller\SignUpControllerProvider());

$app->mount('/user', new Filehosting\Controller\UserControllerProvider());

$app->before(function(Request $request, Silex\Application $app) {
    $cookies = $app['request']->cookies->all();
    $lm = $app['user.service.login_manager'];
    if ($lm->isLoggedIn()) {
        $id = $lm->extractUserIdFromCookies();
        $lm->logIn($id);
        $app['user.logged_in'] = true;
    }
});

/*
$app->get("/", function() {
    return file_get_contents(__DIR__ . "/web/templates/template.html.php");
});
$app->post("/", function(Request $request, Silex\Application $app) {
    $fileUploaded = $request->files->get("fileToUpload");
    //$responseText = "Файл \"{$fileUploaded->getClientOriginalName()}\" загружен!";
    $responseText = $fileUploaded->getRealPath();
    return new Response((string) $responseText, 200);
});
*/

$app->run();
