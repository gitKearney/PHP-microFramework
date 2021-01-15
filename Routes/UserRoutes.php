<?php

/**
 * @var string $userRegex
 */
$userRegex = '/users(\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})?/';

/**
 * @var Main\Routers\RegexRouter $router
 */
$router->route($userRegex, function() {
    /** @var \Pimple\Container defined in Factories/Definition */
    global $appContainer;

    $userController = $appContainer['UserController'];

    /** @var \Zend\Diactoros\Response */
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