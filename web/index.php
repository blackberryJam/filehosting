<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . "/app/bootstrap.php";
require_once dirname(__DIR__) . "/app/app.php";


$app->mount('/', new Filehosting\Controller\IndexControllerProvider());

$app->mount('/file', new Filehosting\Controller\FileControllerProvider());

$app->mount('/signup', new Filehosting\Controller\SignUpControllerProvider());

$app->mount('/user', new Filehosting\Controller\UserControllerProvider());

$app->mount('/comment', new Filehosting\Controller\CommentControllerProvider());

$app->before(function(Request $request, Silex\Application $app) {
    $lm = $app['user.service.login_manager'];

    if ($lm->isLoggedIn()) {
        $id = $lm->extractUserIdFromCookies();
        $lm->logIn($id);
        $app['user.logged_in'] = true;
        return;
    }

    if (!isset($_COOKIE['token'])) {
        $app['user.service']->setCookieToken();
    }
});

$app->run();
