<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;

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
        )->setPrefix('/user');
        $routes = (array)$config->get('routes')->get($collection)->toArray();
        foreach ($routes as $request => $url) {
            foreach ($url as $key => $value) {
                $user->$request($value[0], $value[1]);
            }
        }

        $app->mount($user);
        break;
    case 'product':
        $product = new MicroCollection();
        $product->setHandler(
            ProductController::class,
            true
        )->setPrefix('/product');

        $routes = (array)$config->get('routes')->get($collection)->toArray();
        foreach ($routes as $request => $url) {
            foreach ($url as $key => $value) {
                $product->$request($value[0], $value[1]);
            }
        }
        $app->mount($product);
        break;
    case 'order':
        $order = new MicroCollection();
        $order->setHandler(
            OrderController::class,
            true
        )->setPrefix('/order');
        $routes = (array)$config->get('routes')->get($collection)->toArray();
        foreach ($routes as $request => $url) {
            foreach ($url as $key => $value) {
                $order->$request($value[0], $value[1]);
            }
        }
        $app->mount($order);
        break;
    case 'acl':
        $acl = new MicroCollection();
        $acl->setHandler(
            AclController::class,
            true
        )->setPrefix('/acl');
        $routes = (array)$config->get('routes')->get($collection)->toArray();
        foreach ($routes as $request => $url) {
            foreach ($url as $key => $value) {
                $acl->$request($value[0], $value[1]);
            }
        }
        $app->mount($acl);
        break;

    default:
        $app->get('/notFound', '');
}




//middleware to verify token
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

//if route not found
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404);
    $app->response->setJsonContent(["error" => "error 404 Page not found"]);
    $app->response->send();
});
