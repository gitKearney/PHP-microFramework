<?php
use Pimple\Container as Container;

$router->route('/auth/', function(Container $container) {

    /**
     * @var \Main\Controllers\AuthController
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