<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response;
use Phalcon\Config\ConfigFactory;
use Phalcon\Session\Manager as SessionManager;


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



$container->set('product', function () {
    return new App\Db\ProductHelper();
});
$container->set('order', function () {
    return new App\Db\OrderHelper();
});
$container->set('user', function () {
    return new App\Db\UserHelper();
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
$container->set('escaper', function () {
    return new App\Db\MyEscaper();
}, true);

//session
$container->set(
    'session',
    function () {
        $session = new SessionManager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();
        return $session;
    }
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
    case 'user':
        $user = new MicroCollection();
        $user->setHandler(
            UserController::class,
            true
        )->setPrefix('/user')
            ->get('/', 'index')
            ->get('/accesstoken', 'userAccesToken');

        $app->mount($user);
        break;
    case 'product':
        $product = new MicroCollection();
        $product->setHandler(
            ProductController::class,
            true
        )->setPrefix('/product')
            ->get('/search/{keyword}', 'search')
            ->get('/get', 'getAll')
            ->get('/get/{id}', 'getSingle')
            ->get('/get/{per_page}/{page}/{select}/{filters}', 'get')
            ->post('/post', 'addProduct')
            ->put('/put', 'updateProduct')
            ->delete('/delete/{id}', 'deleteProduct');

        $app->mount($product);
        break;
    case 'order':
        $order = new MicroCollection();
        $order->setHandler(
            OrderController::class,
            true
        )->setPrefix('/order')
            ->get('/get', 'getAll')
            ->get('/get/{start}/{end}', 'getDataByDate')
            ->get('/get/{start}/{end}/{filter}', 'getDataByDateFilter')
            ->post('/post', 'addOrder')
            ->put('/put', 'updateOrder');

        $app->mount($order);
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
        $app->get('/notFound', '');
}


$app->before(
    function () use ($app) {

        $controller = explode('/', $_SERVER["REQUEST_URI"])[1];

        if ($controller == 'user' || $controller == 'acl' || $controller[4] == '?') {
            return true;
        } else {
            $escaper = new App\Db\MyEscaper();
            $key = $escaper->sanitize($app->request->get('key'));
            $middlewareHelper = new \App\Db\MiddlewareHelper();
            if ($key) {
                if ($middlewareHelper->checkKey($key)) {

                    return true;
                } else {
                    $middlewareHelper->sendErrorResponse();
                }
            } else {
                $middlewareHelper->keyNotFound();
            }
        }
    }
);

$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404);
    $app->response->setJsonContent(["error" => "error 404 Page not found"]);
    $app->response->send();
});
$app->handle(
    $_SERVER["REQUEST_URI"]
);
