<?php

require_once 'vendor/autoload.php';

# set the include so that when we import new clases they are already at
# the correct directory
set_include_path(get_include_path().PATH_SEPARATOR.'./');

use \Routers\RegexRouter;

# section to include the controllers
use \Controllers\UserController;
# end section to include new controllers

# this function attempts to find the classes from our framework to include
spl_autoload_register(function ($class_name) {
    $base_dir = __DIR__;
    $file = $base_dir.'/' . str_replace('\\', '/', $class_name) . '.php';

    file_put_contents(
        "/tmp/kearney.debug.log",
        "trying to include: " . $file . PHP_EOL,
        FILE_APPEND
    );

    include $file;
});

# create a REGEX router instance
$router = new RegexRouter();

# add all your routes here. Just use regex matching for a string
$router->route('/users/', function() {
    # always match the controller name with the route

    $userController = new UserController;
    $response = $userController->handleRequest();

    # buffer all output before we send it back to user

    # loop through the headers we've added to the response object, and
    # set the headers before we return a response. Specifically, we want
    # to tell the app we're using JSON
    foreach ($response->getHeaders() as $type => $header) {
        file_put_contents(
            "/tmp/kearney.debug.log",
            "header: " . $type . ' '. $header[0] . PHP_EOL,
            FILE_APPEND
        );

        header($type . ': '. $header[0]);
    }

    ob_start();
    echo $response->getBody()->__toString();
    ob_end_flush();
});

$router->route('/\//', function() {
    echo '<pre>Index route</pre>';
});

# don't add any routes below here!
$router->execute($_SERVER['REQUEST_URI']);

/*
$startTime = microtime(true);
$myController = new UserController;

$result = $myController->get(12);
echo print_r($result, true);
$endTime = microtime(true);

echo 'Total Runtime: ', $endTime - $startTime, 'us', PHP_EOL;
*/
