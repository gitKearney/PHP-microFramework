<?php

use Main\Controllers\AuthController;
use Main\Services\AuthService;
use Main\Services\JwtService;
use Main\Services\UserService;
use Main\Services\UuidService;
use Main\Controllers\UserController;
use Main\Models\Users;
use Pimple\Container;

$appContainer = new Container;

$appContainer['AuthController'] = function(Container $container) {
    return new AuthController($container['UserService'], $container['AuthService']);
};

$appContainer['AuthService'] = function(Container $container) {
    return new AuthService($container['Users'], $container['JwtService']);
};

$appContainer['JwtService'] = function(Container $container) {
    return new JwtService;
};

$appContainer['Users'] = function (Container $container) {
    return new Users;
};

$appContainer['UserController'] = function(Container $container) {
    return new UserController($container['UserService'], $container['JwtService']);
};

$appContainer['UuidService'] = function(Container $container) {
    return new UuidService;
};

$appContainer['UserService'] = function(Container $container) {
    return new UserService($container['Users'], $container['UuidService']);
};


return $appContainer;