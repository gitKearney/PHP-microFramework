<?php

use Pimple\Container;

// Controllers
use Main\Controllers\AuthController;
use Main\Controllers\ProductController;
use Main\Controllers\TransactionController;
use Main\Controllers\UserController;

// Services
use Main\Services\AuthService;
use Main\Services\JwtService;
use Main\Services\ProductService;
use Main\Services\TransactionService;
use Main\Services\UserService;
use Main\Services\UuidService;

// Models
use Main\Models\Users;
use Main\Models\Products;
use Main\Models\Transactions;
use Main\Models\TransactionProducts;

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
 * @return JwtService
 */
$appContainer['JwtService'] = function() {
    return new JwtService;
};

/**
 * @return Users
 */
$appContainer['Users'] = function () {
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
 * @return UuidService
 */
$appContainer['UuidService'] = function() {
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
 * @return Products
 */
$appContainer['Products'] = function() {
    return new Products;
};

/**
 * @param Container $container
 * @return ProductService
 */
$appContainer['ProductService'] = function(Container $container) {
    return new ProductService($container['Products'], $container['UuidService']);
};

/**
 * @param Container $container
 * @return ProductController
 */
$appContainer['ProductController'] = function(Container $container) {
    return new ProductController($container['JwtService'], $container['ProductService'], $container['UserService']);
};

$appContainer['Transactions'] = function(Container $container) {
    return new Transactions();
};

$appContainer['TransactionProducts'] = function(Container $container) {
    return new TransactionProducts();
};

$appContainer['TransactionService'] = function(Container $container) {
    return new TransactionService($container['UuidService'],
        $container['Transactions'], $container['TransactionProducts']);
};

$appContainer['TransactionController'] = function(Container $container) {
    return new TransactionController($container['TransactionService']);
};

return $appContainer;