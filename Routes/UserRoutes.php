<?php
use Pimple\Container as Container;

/**
 * @var string $userRegex
 */
$userRegex = '/users(\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})?/';

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route($userRegex, function(Container $container) {

    /**
     * @var \Main\Controllers\UserController
     */
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