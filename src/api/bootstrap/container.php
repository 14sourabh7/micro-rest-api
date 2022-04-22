<?php
// files including all containers

use Phalcon\Di\FactoryDefault;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response;
use Phalcon\Config\ConfigFactory;
use Phalcon\Session\Manager as SessionManager;


$container = new FactoryDefault();

//db helper classes
$container->set('product', function () {
    return new App\Db\ProductHelper();
});
$container->set('order', function () {
    return new App\Db\OrderHelper();
});
$container->set('user', function () {
    return new App\Db\UserHelper();
});


//response
$container->set('response', function () {
    return new Response();
});


//config
$filename = './config/config.php';
$factory = new ConfigFactory();
$config =  $factory->newInstance('php', $filename);
$container->set(
    'config',
    $config,
    true
);


//conatiner di for escaper class
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



//db connection
$container->set('mongo', function () {
    global $config;
    return
        new \MongoDB\Client("mongodb+srv://" . $config->get('db')->get("username") . ":" . $config->get('db')->get("password") . "@sandbox.h1mpq.mongodb.net/myFirstDatabase?retryWrites=true&w=majority");
});
