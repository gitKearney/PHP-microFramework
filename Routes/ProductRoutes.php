<?php

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route('/products/', function() {
    /** @var \Pimple\Container defined in Factories/Definition */
    global $appContainer;

    /**
     * @var \Main\Controllers\ProductController
     */
    $authController = $appContainer['ProductController'];

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


    header("Access-Control-Allow-Origin: *");

    ob_start();
    echo $response->getBody()->__toString();;
    ob_end_flush();
});