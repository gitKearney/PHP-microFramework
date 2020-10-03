<?php

$transRegex = '/transactions(\/)?/';

$router->route($transRegex, function () {
    /**
     * @var \Pimple\Container $container defined in index.php line 92
     */
    global $container;
});

