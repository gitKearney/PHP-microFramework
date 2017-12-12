<?php

use Pimple\Container;

// Controllers
use Main\Controllers\AuthController;
use Main\Controllers\ProductController;
use Main\Controllers\UserController;

// Services
use Main\Services\AuthService;
use Main\Services\JwtService;
use Main\Services\ProductService;
use Main\Services\UserService;
use Main\Services\UuidService;

// Models
use Main\Models\Users;
use Main\Models\Products;


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

/** ProductController setup */
$appContainer['Products'] = function(Container $container) {
    return new Products;
};

$appContainer['ProductService'] = function(Container $container) {
    return new ProductService($container['Products'], $container['UuidService']);
};

$appContainer['ProductController'] = function(Container $container) {
    return new ProductController($container['JwtService'], $container['ProductService']);
};

return $appContainer;