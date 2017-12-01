<?php

namespace Main\Services;

class JwtService
{
    protected $webToken;
    
    public function __construct()
    {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
    }
    
    public function createJwt($userId, $userEmail)
    {
        # get the config
        $config    = getAppConfigSettings();
        
        $tokenUserData =  new \stdClass();
        
        $tokenUserData->userId = $userId;
        $tokenUserData->email  = $userEmail;
        
        $responseToken = new \stdClass();
        
        // create a datetime object to work with
        $currentTime = new \DateTime("now");
        
        // set the issued at time
        $responseToken->iat = $currentTime->format('U');
        
        // set the not before time
        $responseToken->nbf = $currentTime->format('U');
        
        // set the issuer name and audience name
        $responseToken->iss = $config->jwt->issuer;
        $responseToken->aud = $config->jwt->audience;
        
        // set the expiry time to x hour y minutes
        $ttl = "PT".$config->jwt->max_hour."H".$config->jwt->max_minute."M";
        
        $interval = new \DateInterval($ttl);
        $currentTime->add($interval);
        $responseToken->exp = $currentTime->format('U');
        
        // set a unique JSON token ID
        $responseToken->jti = base64_encode(random_bytes(32));
        
        // set the user's info as our data
        $responseToken->data = $tokenUserData;
        
        // now, create a JSON Web Token!
        $this->webToken = JWT::encode($responseToken, $config->jwt->key);
        
        return $this;
    }
    
    public function getJwt()
    {
        return this->webToken;
    }
}
