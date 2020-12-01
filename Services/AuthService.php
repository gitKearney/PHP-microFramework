<?php

namespace Main\Services;

use Main\Models\Users;
use stdClass;
use Exception;

class AuthService extends BaseService
{
    /**
     * @var Users
     */
    protected $users;

    /**
     * @var JwtService
     */
    protected $jwtService;

    public function __construct(Users $users, JwtService $jwtService)
    {
        $this->users = $users;
        $this->jwtService = $jwtService;
    }

    /**
     * @param array $requestBody
     * @return stdClass
     * @throws Exception
     */
    public function createJwt(array $requestBody)
    {
        $email = $requestBody['email'] ?? null;

        $response = $this->createResponseObject();

        if (is_null($email)) {
            $response->code = 401;
            $response->message = 'Invalid User';
            return $response;
        }

        # verify the user's information correct
        $user = $this->users->findUserByEmail($requestBody['email']);

        if (count($user) === 0) {
            $response->code = 401;
            $response->message = 'Invalid User';
            return $response;
        }

        # does the password from the body match the user's password?
        $match = password_verify($requestBody['password'], $user['upassword']);
        if (!$match) {
            $response->code = 401;
            $response->message = 'Invalid User';
            return $response;
        }

        # the user's credentials match, create a JWT for the user
        $this->jwtService->createJwt($user['user_id'], $user['email']);

        $response->results = $this->jwtService->getJwt();
        $response->success = true;
        return $response;
    }
}
