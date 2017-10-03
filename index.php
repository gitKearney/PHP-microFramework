<?php

require_once 'vendor/autoload.php';

use Main\Routers\RegexRouter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Main\Services\DebugLogger;

/**
 * @param string $string
 * @param string $level
 */
function logVar($var, $level='debug')
{
    global $log;

    $string = $var;

    if (is_null($var)) {
        $string = 'null';
    }

    if (is_array($var)) {
        $string = print_r($var, true);
    }

    if (is_bool($var)) {
        $string = ($var)?'true':'false';
    }

    if (is_int($var) || is_float($var)) {
        $string = strval($var);
    }

    if (is_object($var)) {
        $string = print_r($var, true);
    }

    switch ($level) {
        case 'debug':
            $log->debug($string);
            break;
        case 'info':
            $log->info($string);
            break;
        case 'notice':
            $log->notice($string);
            break;
        case 'warning':
            $log->warning($string);
            break;
        case 'error':
            $log->error($string);
            break;
        case 'critical':
            $log->critical($string);
            break;
        case 'alert':
            $log->alert($string);
            break;
        case 'emergency':
            $log->emergency($string);
            break;
    }
}

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler('/tmp/php.debug.log', Logger::WARNING));

$container = require_once __DIR__.'/Factories/Definitions.php';

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
