<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

error_reporting(-1);
mb_internal_encoding('utf-8');

require_once dirname(__DIR__) . "/vendor/autoload.php";

$isDevMode = true;
$config = Setup::CreateAnnotationMetadataConfiguration(array(__DIR__ . "/Model"),
    $isDevMode, null, null, false); // Fifth arg forces Doctrine to use AnnotationReader.

$connection = array(
    'dbname' => 'filehosting',
    'user' => 'root',
    'password' => 'ghbdtndfcz',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
    'charset' => 'utf8'
);

$entityManager = EntityManager::create($connection, $config);
