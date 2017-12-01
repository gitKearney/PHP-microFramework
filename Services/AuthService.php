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
    
    public function __construct(
        Users $users, 
        JwtService $jwtService,
        UuidService $uuidService
    ) {
        $this->users = $users;
        $this->jwtService = $jwtService;
        $this->uuidService = $uuidService;
    }
    
    public function createJwt(array $requestBody)
    {
        # verify the user's information correct
        
        $account = $users->findUserByEmail($requestBody['email']);
        
        # does the user's password match what was passed in?
        $match = password_verify($account['upassword'], $requestBody['password']);
        
        if (!$match) {
            # return an error that account not found
            return [
                'result' => 'No user found',
            ];
        }
        
        # the user's credentials match, create a JWT for the user
    }
}