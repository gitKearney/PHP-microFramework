<?php
namespace Main\Utils;

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
                       bool $authUser,
                       string $requiredRole,
                       JwtService $jwtService,
                       UserService $userService)
{
    $response = createResponse();

    if (!$authUser) {
        $response->true;
        return $response;
    }

    $user = $jwtService->decodeWebToken($requestHeaders);

    if(!$user->success) {
        $response->message = $user->message;
        $response->code = 401;
        return $response;
    }

    $userId = $response->results->data->userId;

    $hasPermission = $userService->userAllowedAction($userId, $requiredRole);
    if (!$hasPermission) {
        $response->message = 'Action Not Allowed';
        $response->code = 100;
        return $response;
    }

    $response->success = true;
    return $response;
}

function createResponse()
{
    $response = new stdClass();
    $response->success = false;
    $response->message = '';
    $response->results = [];
    $response->code    = 200;

    return $response;
}