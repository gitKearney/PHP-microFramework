<?php

namespace Main\Services;

use Main\Models\Users;
use Main\Services\UuidService;
use Main\Services\JwtService;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequest;

class AuthService
{
    /**
     * @var Users
     */
    protected $users;

    /**
     * @var JwtService
     */
    protected $jwtService;

    /**
     * @var UuidService
     */
    protected $uuidService;

    public function __construct(Users $users, JwtService $jwtService)
    {
        $this->users = $users;
        $this->jwtService = $jwtService;
    }

    /**
     * @param array $requestBody
     * @return string
     * @throws \Exception
     */
    public function createJwt(array $requestBody)
    {
        $email = $requestBody['email'] ?? null;

        if (is_null($email)) {
            throw new \Exception('No email passed in', 404);
        }

        # verify the user's information correct
        $result = $this->users->findUserByEmail($requestBody['email']);

        if ($result->success === false || count($result->results) === 0) {
            throw new \Exception('No user found', 404);
        }

        $account = (object) $result->results;
        logVar($account, 'ACCOUNT PULLED: ');

        # does the password from the body match the user's password?
        $match = password_verify($requestBody['password'], $account->upassword);
        if (!$match) {
            throw new \Exception('No user found', 302);
        }

        # the user's credentials match, create a JWT for the user
        $this->jwtService->createJwt($account->user_id, $account->email);

        return $this->jwtService->getJwt();
    }
}
