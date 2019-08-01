<?php

$router->route('/products/', function(Container $container) {

    /**
     * @var \Main\Controllers\AuthController
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