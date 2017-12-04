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
    
    public function createJwt(array $requestBody)
    {
        # verify the user's information correct
        
        $resultSet = $this->users->findUserByEmail($requestBody['email']);
        
        if ($resultSet === false) {
            return ['result' => 'No user found', ];
        }
        
        $account = $resultSet[0];
           
        # does the password from the body match the user's password?
        $match = password_verify($requestBody['password'], $account['upassword']);
        if (!$match) {
            return [
                'result' => 'No user found',
            ];
        }

        # the user's credentials match, create a JWT for the user
        $this->jwtService->createJwt($account['user_id'], $account['email']);
        
        return $this->jwtService->getJwt();
    }
}