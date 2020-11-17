<?php

use Pimple\Container;

// Controllers
use Main\Controllers\AuthController;
use Main\Controllers\ProductController;
use Main\Controllers\TransactionController;
use Main\Controllers\UserController;
use Main\Controllers\CartController;

// Services
use Main\Services\AuthService;
use Main\Services\JwtService;
use Main\Services\ProductService;
use Main\Services\TransactionService;
use Main\Services\UserService;
use Main\Services\UuidService;
use Main\Services\CartService;

// Models
use Main\Models\Users;
use Main\Models\Products;
use Main\Models\Transactions;
use Main\Models\TransactionProducts;
use Main\Models\Carts;

/**
 * @var Container
 */
$appContainer = new Container;

/**
 * @return AuthController
 */
$appContainer['AuthController'] = function() {
    global $appContainer;
    return new AuthController($appContainer['UserService'],
        $appContainer['AuthService']);
};

/**
 * @return AuthService
 */
$appContainer['AuthService'] = function() {
    global $appContainer;
    return new AuthService($appContainer['Users'], $appContainer['JwtService']);
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
 * @return UserController
 */
$appContainer['UserController'] = function() {
    global $appContainer;
    return new UserController($appContainer['UserService'], $appContainer['JwtService']);
};

/**
 * @return UuidService
 */
$appContainer['UuidService'] = function() {
    return new UuidService;
};

/**
 * @return UserService
 */
$appContainer['UserService'] = function() {
    global $appContainer;
    return new UserService($appContainer['Users'], $appContainer['UuidService']);
};

/**
 * @return Products
 */
$appContainer['Products'] = function() {
    return new Products;
};

/**
 * @return ProductService
 */
$appContainer['ProductService'] = function() {
    global $appContainer;
    return new ProductService($appContainer['Products'], $appContainer['UuidService']);
};

/**
 * @return ProductController
 */
$appContainer['ProductController'] = function() {
    global $appContainer;
    return new ProductController($appContainer['JwtService'],
        $appContainer['ProductService'], $appContainer['UserService']);
};

/**
 * @return Transactions
 */
$appContainer['Transactions'] = function() {
    return new Transactions();
};

/**
 * @return TransactionProducts
 */
$appContainer['TransactionProducts'] = function() {
    return new TransactionProducts();
};

/**
 * @return TransactionService
 */
$appContainer['TransactionService'] = function() {
    global $appContainer;
    return new TransactionService($appContainer['UuidService'],
        $appContainer['Transactions'], $appContainer['TransactionProducts']);
};

/**
 * @return TransactionController
 */
$appContainer['TransactionController'] = function() {
    global $appContainer;
    return new TransactionController($appContainer['TransactionService'],
        $appContainer['JwtService'], $appContainer['UserService']);
};

/**
 * @return Carts
 */
$appContainer['Carts'] = function() {
    return new Carts;
};

/**
 * @return CartService
 */
$appContainer['CartsService'] = function() {
    global $appContainer;

    return new CartService(($appContainer['Carts']));
};

$appContainer['CartController'] = function () {
    global $appContainer;

    return new CartController($appContainer['CartsService']);
};

return $appContainer;