<?php
namespace Main\Utils;

/**
 * This file is loaded from the composer.json file. It's an example of how
 * to autoload a config file using PSR
 */
use stdClass;
use Main\Services\UserService;
use Main\Services\JwtService;

/**
 * @param array $requestHeaders
 * @param boolean $authUser
 * @param string $requiredRole
 * @param JwtService $jwtService
 * @param UserService $userService
 * @return stdClass
 */
function checkUserRole(array $requestHeaders,
                       string $requiredRole,
                       JwtService $jwtService,
                       UserService $userService): stdClass
{
    $response = createResponse();
    $config = getAppConfigSettings();

    if (!$config->debug->authUsers) {
        $response->true;
        return $response;
    }

    $user = $jwtService->decodeWebToken($requestHeaders);

    if(!$user->success) {
        $response->message = $user->message;
        $response->code = 401;
        return $response;
    }

    $userId = $user->results->data->userId;

    $hasPermission = $userService->userAllowedAction($userId, $requiredRole);
    if (!$hasPermission) {
        $response->message = 'Action Not Allowed';
        $response->code = 100;
        return $response;
    }

    $response->success = true;
    return $response;
}

function createResponse(): stdClass
{
    $response = new stdClass();
    $response->success = false;
    $response->message = '';
    $response->results = [];
    $response->code    = 200;

    return $response;
}