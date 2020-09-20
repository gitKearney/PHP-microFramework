<?php

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route('/auth/', function() {

    /**
     * @var \Pimple\Container
     */
    global $container;

    /**
     * @var \Main\Controllers\AuthController
     */
    $authController = $container['AuthController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $authController->handleRequest();

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