<?php

use App\Actions\UserActions;

function createUserRoutes(): void
{
    global $router;

    $router->route('/user/i', function () {
        $controller = new UserActions();
        $controller->handleRequest(getServerRequest());
    });
}

createUserRoutes();