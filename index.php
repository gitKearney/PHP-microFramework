<?php

require_once 'vendor/autoload.php';

use Main\Routers\RegexRouter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;
use Pimple\Container;

require_once('configs/credentials.php');

/**
 * @param mixed $var
 * @param string $msg
 * @param string $level
 */
function logVar($var, $msg = '', $level='debug')
{
    global $log;

    $string = $msg;

    if (is_null($var)) {
        $string .= 'null';
    }

    if (is_array($var)) {
        $string .= print_r($var, true);
    }

    if (is_bool($var)) {
        $string .= ($var)?'true':'false';
    }

    if (is_int($var) || is_float($var)) {
        $string .= strval($var);
    }

    if (is_object($var)) {
        $string .= print_r($var, true);
    }

    if (is_string($var)) {
        $string .= $var;
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

/**
 * returns the configuration settings for our app. Think of this like getenv()
 * only, it returns all the settings and you have to know what you're looking
 *
 * Hey! This is faster than reading environment variables
 */
function getAppConfigSettings()
{
    global $config;

    return $config;
}

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler($config->app_settings->log_location, Logger::DEBUG));

# read in our configuration file
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$container = require_once __DIR__.'/Factories/Definitions.php';

$router = new RegexRouter;

$userRegex = '/users(\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})?/';
$router->route($userRegex, function(Container $container) {
    $userController = $container['UserController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $userController->handleRequest();

    # you MUST output the header before any HTML
    foreach($response->getHeaders() as $index => $value) {
        header($index.': '.$value[0]);
    }

    ob_start();
    echo $response->getBody()->__toString();
    ob_end_flush();
    });

$router->route('/auth/', function(Container $container) {

    /**
     * @var AuthController
     */
    $authController = $container['AuthController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $authController->handleRequest();

    # you MUST output the header before any HTML
    foreach($response->getHeaders() as $index => $value) {
        header($index.': '.$value[0]);
    }

    ob_start();
    echo $response->getBody()->__toString();
    ob_end_flush();
});

$router->route('/products/', function(Container $container) {

    /**
     * @var AuthController
     */
    $authController = $container['ProductController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $authController->handleRequest();

    # you MUST output the header before any HTML
    foreach($response->getHeaders() as $index => $value) {
        header($index.': '.$value[0]);
    }

    header("Access-Control-Allow-Origin: *");
    ob_start();
    echo $response->getBody()->__toString();;
    ob_end_flush();
});

$router->route('/\//', function() {
    header("Access-Control-Allow-Origin: *");
    ob_start();
    echo '<pre>Index route</pre>';
    ob_end_flush();
});

$router->execute($_SERVER['REQUEST_URI'], $container);
logVar($_SERVER['REQUEST_URI'], 'REQUEST_URI = ');

