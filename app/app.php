<?php
$app = new Silex\Application();
$app['debug'] = true;


$app['em'] = $app->share(function() use ($entityManager) {
    return $entityManager;
});

$app['getid3'] = $app->share(function() {
    return new getID3();
});

$app['user.service.login_manager'] = $app->share(function($app) {
    return new Filehosting\Service\UserLoginManager($app['em'], $app['request']->cookies->all());
});

$app['user.service'] = function($app) {
    return new Filehosting\Service\UserService($app['em'], $app['user.service.login_manager']);
};

$app['user'] = $app->share(function($app) {
    return $app['user.service']->identifyUser($app['request']->cookies->all());
});

$app['user.logged_in'] = false;

$app['file.save_directory'] = dirname(__DIR__) . "/storage";

$app['file.service'] = function($app) {
    return new Filehosting\Service\FileService($app['getid3'], $app['em'], $app['user'], $app['file.save_directory']);
};

$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => dirname(__DIR__) . "/web/templates"));
