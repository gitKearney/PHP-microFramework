<?php

namespace Main\Services;

use \Firebase\JWT\JWT;

class JwtService
{
    /**
     * @var string
     */
    private $webToken;

    public function __construct()
    {

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
        $ttl = "PT".$config->jwt->max_hours."H".$config->jwt->max_minutes."M";

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

    /**
     * decodes a JWT
     * @param array $httpHeaders
     * @return \stdClass
     * @throws \Exception
     */
    public function decodeWebToken($httpHeaders)
    {
        $config = getAppConfigSettings();

        # is there an authorization header?
        if (! isset($httpHeaders['authorization'])) {
            throw new \Exception('Access Denied', 401);
        }

        $authHeaders = $httpHeaders['authorization'];
        $bearerToken = '';

        # search the auth headers for Bearer, if none found, error out
        foreach($authHeaders as $index => $value) {
            # search for the string "Bearer"
            if (strpos($value, 'Bearer') !== false) {
                sscanf($value, 'Bearer %s', $bearerToken);
                break;
            }
        }

        if (strlen($bearerToken) == 0) {
            throw new \Exception('Access Denied', 401);
        }

        # make sure the JWT is good
        try {
            $decoded = JWT::decode($bearerToken, $config->jwt->key, array('HS256'));
        } catch (\Exception $e) {
            throw $e;
        }

        return $decoded;
    }

    /**
     * gets the JWT string created by createJWT
     * @return string
     */
    public function getJwt()
    {
        return $this->webToken;
    }
}
