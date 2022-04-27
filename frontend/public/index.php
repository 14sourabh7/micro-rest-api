 <?php

    use Phalcon\Di\FactoryDefault;
    use Phalcon\Loader;
    use Phalcon\Mvc\View;
    use Phalcon\Mvc\Application;
    use Phalcon\Url;
    use Phalcon\Http\Response;
    use Phalcon\Debug;

    $debug = new Debug();
    $debug->listen(false, true);

    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');

    require '../vendor/autoload.php';




    // Register an autoloader
    $loader = new Loader();

    $loader->registerDirs(
        [
            APP_PATH . "/controllers/",
        ]
    );
    $loader->registerNamespaces(
        [
            "App\Components" => APP_PATH . "/components"
        ]
    );

    $loader->register();

    $container = new FactoryDefault();
    $application = new Application($container);

    $container->set(
        'view',
        function () {
            $view = new View();
            $view->setViewsDir(APP_PATH . '/views/');
            return $view;
        }
    );


    $container->set(
        'url',
        function () {
            $url = new Url();
            $url->setBaseUri('/');
            return $url;
        }
    );

    $container->set('response', function () {
        return new Response();
    });



    $container->set('locale', (new \App\Components\Locale())->getTranslator());
    //setting connection with mongodb
    $container->set(
        'mongo',
        function () {
            $mongo = new \MongoDB\Client("mongodb+srv://m001-student:12345@sandbox.h1mpq.mongodb.net/myFirstDatabase?retryWrites=true&w=majority");

            return $mongo;
        },
        true
    );

    //db helper
    $container->set('dbHelper', function () {
        return new \App\Components\MongoHelper();
    }, true);





    try {
        // Handle the request
        $response = $application->handle(
            $_SERVER["REQUEST_URI"]
        );
        $response->send();
    } catch (\Exception $e) {
        echo 'Exception: ', $e->getMessage();
    }
