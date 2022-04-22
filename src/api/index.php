<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;





define('BASE_PATH', dirname(__DIR__));




//loader to register controller dir
$loader = new Loader();
$loader->registerDirs([
    './controllers',
]);

$loader->registerNamespaces([
    'App\Db' => './components'
]);

$loader->registerFiles([
    './vendor/autoload.php',
]);

$loader->register();



//app
$app = new Micro();

//container
include './bootstrap/container.php';


//routers
include './bootstrap/routers.php';



//handling uri
$app->handle(
    $_SERVER["REQUEST_URI"]
);
