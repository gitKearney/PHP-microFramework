<?php

require_once 'vendor/autoload.php';

use Main\Routers\RegexRouter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

include_once 'configs/credentials.php';

/**
 * @param mixed $var
 * @param string $msg
 * @param string $level
 */
function logVar($var, $msg = '', $level='debug')
{
    /**
     * @desc defined on line 93
     * @var Monolog\Logger
     */
    global $log;

    $string = $msg . ' - ';

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
 * @return stdClass
 */
function getAppConfigSettings()
{
    global $config;

    return $config;
}

$config = credentials();

# create a log channel
try {
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler($config->app_settings->log_location, Logger::DEBUG));
} catch (Exception $e) {
    die('INVALID CONFIGURATION: '.$e->getCode().'-'.$e->getMessage()."\n");
}

/**
 * @var \Pimple\Container
 */
$container = require_once __DIR__.'/Factories/Definitions.php';

/**
 * @var RegexRouter
 */
$router   = new RegexRouter;

# put routes here, make sure the default route is last
include_once __DIR__.'/Routes/AuthRoutes.php';
include_once __DIR__.'/Routes/UserRoutes.php';
include_once __DIR__.'/Routes/ProductRoutes.php';

# no more routes below here
include_once __DIR__.'/Routes/DefaultRoute.php';

$router->execute($_SERVER['REQUEST_URI'], $container);
