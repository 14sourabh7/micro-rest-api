<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;

$container = new FactoryDefault();
$app = new Micro();

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

$container->set('mongo', function () {
    return
        new \MongoDB\Client("mongodb+srv://m001-student:12345@sandbox.h1mpq.mongodb.net/myFirstDatabase?retryWrites=true&w=majority");
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
            ->get('/search/{key}/{keyword}', 'search')
            ->get('/get/{key}/{per_page}/{page}/{select}/{filters}', 'get');

        $app->mount($product);
        break;
}


$app->handle(
    $_SERVER["REQUEST_URI"]
);
