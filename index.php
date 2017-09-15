<?php

require_once 'vendor/autoload.php';

function logger($stringToLog)
{
    file_put_contents('/tmp/debug.log', $stringToLog, FILE_APPEND);
}

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function logVar($string)
{

}

$container = require_once __DIR__.'/Factories/Definitions.php';

use Main\Routers\RegexRouter;

$router = new RegexRouter;


$router->route('/users/', function(\Pimple\Container $container) {
    $userController = $container['UserController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $userController->handleRequest();

    ob_start();
    echo $response->getBody()->__toString();
    ob_end_flush();
});

$router->route('/\//', function() {
    echo '<pre>Index route</pre>';
});

$router->execute($_SERVER['REQUEST_URI'], $container);
