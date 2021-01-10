<?php

/**
 * @var \Main\Routers\RegexRouter $router
 */
$router->route('/\/?/', function() {
    header('HTTP/1.1 200 OK');
    header("Access-Control-Allow-Origin: *");
    ob_start();
    echo '<pre>Index route</pre>';
    ob_end_flush();
});