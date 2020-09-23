<?php

/**
 * @var string $userRegex
 */
$userRegex = '/users(\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})?/';

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route($userRegex, function() {
    // if you have an aversion to global, then you could pass in $container to
    // the closure like so
    // function(Container $container) instead of using global
    
    /**
     * @var \Pimple\Container $container defined in index.php line 92
     */
    global $container;

    $userController = $container['UserController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $userController->handleRequest();

    # you MUST output the header before any HTML
    $status = sprintf("HTTP/%s %s %s",
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    );

    header($status);

    foreach($response->getHeaders() as $index => $value) {
        header($index.': '.$value[0]);
    }

    ob_start();
    echo $response->getBody()->__toString();
    ob_end_flush();
});