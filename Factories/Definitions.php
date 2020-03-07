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

/**
 * @var Container
 */
$appContainer = new Container;

/**
 * @param Container $container
 * @return AuthController
 */
$appContainer['AuthController'] = function(Container $container) {
    return new AuthController($container['UserService'], $container['AuthService']);
};

/**
 * @param Container $container
 * @return AuthService
 */
$appContainer['AuthService'] = function(Container $container) {
    return new AuthService($container['Users'], $container['JwtService']);
};

/**
 * @param Container $container
 * @return JwtService
 */
$appContainer['JwtService'] = function(Container $container) {
    return new JwtService;
};

/**
 * @param Container $container
 * @return Users
 */
$appContainer['Users'] = function (Container $container) {
    return new Users;
};

/**
 * @param Container $container
 * @return UserController
 */
$appContainer['UserController'] = function(Container $container) {
    return new UserController($container['UserService'], $container['JwtService']);
};

/**
 * @param Container $container
 * @return UuidService
 */
$appContainer['UuidService'] = function(Container $container) {
    return new UuidService;
};

/**
 * @param Container $container
 * @return UserService
 */
$appContainer['UserService'] = function(Container $container) {
    return new UserService($container['Users'], $container['UuidService']);
};

/**
 * @param Container $container
 * @return Products
 */
$appContainer['Products'] = function(Container $container) {
    return new Products;
};

/**
 * @param Container $container
 * @return ProductService
 */
$appContainer['ProductService'] = function(Container $container) {
    return new ProductService($container['Products'], $container['UuidService'], $container['UserService']);
};

/**
 * @param Container $container
 * @return ProductController
 */
$appContainer['ProductController'] = function(Container $container) {
    return new ProductController($container['JwtService'], $container['ProductService'], $container['UserService']);
};

return $appContainer;