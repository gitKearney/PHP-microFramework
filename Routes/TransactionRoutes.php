<?php

$transRegex = '/transactions(\/)?/';

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route($transRegex, function () {
    /**
     * @var \Pimple\Container $container defined in index.php line 92
     */
    global $container;

    $transactionController = $container['TransactionController'];

    /**
     * @var \Zend\Diactoros\Response
     */
    $response = $transactionController->handleRequest();

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
