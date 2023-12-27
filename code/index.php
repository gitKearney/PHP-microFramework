<?php
require_once 'vendor/autoload.php';

use App\Router\RegexRouter;

// GLOBALS (EWW!) TOO BAD PHP DEVS DON'T LIKE GLOBALS
include_once 'Utils/setServerResponse.php';
include_once 'Utils/getServerRequest.php';
include_once 'Utils/querySelect.php';

$router = new RegexRouter;
include_once './Routes/createUserRoutes.php';

$router->execute($_SERVER['REQUEST_URI']);