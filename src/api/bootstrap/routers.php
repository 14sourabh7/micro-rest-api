<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$uri = new \Phalcon\Http\Message\Uri($_SERVER['REQUEST_URI']);
$path = $uri->getPath();
$parts = explode("/", $path);

$collection = $parts[1];
$controller = ucfirst($collection);


//setting router
$router = new MicroCollection();
$router->setHandler(
    $controller . "Controller"::class,
    true
)->setPrefix("/$collection");
$routes = (array)$config->get('routes')->get($collection)->toArray();
foreach ($routes as $request => $url) {
    foreach ($url as $key => $value) {
        $router->$request($value[0], $value[1]);
    }
}
$app->mount($router);


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
