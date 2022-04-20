<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response;
use Phalcon\Config\ConfigFactory;

$container = new FactoryDefault();

$app = new Micro();

define('BASE_PATH', dirname(__DIR__));
require './vendor/autoload.php';

//loader to register controller dir
$loader = new Loader();
$loader->registerDirs([
    './controllers'
]);

$loader->registerNamespaces([
    'App\Db' => './components'
]);

$loader->register();



$container->set('response', function () {
    return new Response();
});

$session = new Manager();
$files = new Stream(
    [
        'savePath' => '/tmp',
    ]
);
$session->setAdapter($files);

$container->set('session', $session);

$container->set('db', function () {
    return new App\Db\MongoHelper();
});

$container->set('response', function () {
    return new Response();
});

$filename = './config/config.php';
$factory = new ConfigFactory();
$config =  $factory->newInstance('php', $filename);
$container->set(
    'config',
    $config,
    true
);


$container->set('mongo', function () {
    global $config;
    return
        new \MongoDB\Client("mongodb+srv://" . $config->get('db')->get("username") . ":" . $config->get('db')->get("password") . "@sandbox.h1mpq.mongodb.net/myFirstDatabase?retryWrites=true&w=majority");
});



$uri = new \Phalcon\Http\Message\Uri($_SERVER['REQUEST_URI']);
$path = $uri->getPath();
$parts = explode("/", $path);
$collection = $parts[1];

switch ($collection) {
    case 'product':
        $product = new MicroCollection();
        $product->setHandler(
            ProductController::class,
            true
        )->setPrefix('/product')
            ->get('/', 'index')
            ->get('/search/{keyword}', 'search')
            ->get('/get', 'getAll')
            ->get('/get/{per_page}/{page}/{select}/{filters}', 'get');

        $app->mount($product);
        break;
    case 'acl':
        $acl = new MicroCollection();
        $acl->setHandler(
            AclController::class,
            true
        )->setPrefix('/acl')
            ->get('/', 'index');

        $app->mount($acl);
        break;

    default:
        $notFoundHandler = new MicroCollection();
        $notFoundHandler->setHandler(
            ProductController::class,
            true
        )->setPrefix($_SERVER["REQUEST_URI"])
            ->get('/', 'notfound');
        $app->mount($notFoundHandler);
}


$app->handle(
    $_SERVER["REQUEST_URI"]
);
