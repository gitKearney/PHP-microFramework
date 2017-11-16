<?php

use Main\Controllers\AuthController;
use Main\Services\UserService;
use Main\Services\UuidService;
use Main\Controllers\UserController;
use Main\Models\Users;
use Pimple\Container;

$appContainer = new Container;

$appContainer['AuthController'] = function(Container $container) {
    return new AuthController($container['UserService']);
};

$appContainer['Users'] = function (Container $container) {
    return new Users;
};

$appContainer['UserController'] = function(Container $container) {
    return new UserController($container['UserService']);
};

$appContainer['UuidService'] = function(Container $container) {
    return new UuidService;
};

$appContainer['UserService'] = function(Container $container) {
    return new UserService($container['Users'], $container['UuidService']);
};


return $appContainer;