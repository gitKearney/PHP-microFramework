<?php

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route('/\//', function() {
    header("Access-Control-Allow-Origin: *");
    ob_start();
    echo '<pre>Index route</pre>';
    ob_end_flush();
});