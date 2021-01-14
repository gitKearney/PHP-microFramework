<?php

namespace Main\Services;

use DateInterval;
use DateTime;
use \Firebase\JWT\JWT;
use stdClass;
use Exception;

class JwtService extends BaseService
{
    /**
     * @var string
     */
    private $webToken;

    public function __construct()
    {

    }

    /**
     * @param $userId
     * @param $userEmail
     * @return $this
     * @throws Exception
     */
    public function createJwt($userId, $userEmail)
    {
        $result = new stdClass();
        $result->success = false;
        $result->message = '';
        $result->results = [];

        # get the config
        $config    = getAppConfigSettings();

        $tokenUserData =  new stdClass();

        $tokenUserData->userId = $userId;
        $tokenUserData->email  = $userEmail;

        $responseToken = new stdClass();

        // create a datetime object to work with
        $currentTime = new DateTime("now");

        // set the issued at time
        $responseToken->iat = $currentTime->format('U');

        // set the not before time
        $responseToken->nbf = $currentTime->format('U');

        // set the issuer name and audience name
        $responseToken->iss = $config->jwt->issuer;
        $responseToken->aud = $config->jwt->audience;

        // set the expiry time (time to live) to x hour y minutes
        $ttl = "PT".$config->jwt->max_hours."H".$config->jwt->max_minutes."M";

        try
        {
            $interval = new DateInterval($ttl);
        }
        catch (Exception $e) {
            // default to 1 hour
            logVar($e->getMessage(), 'ERROR: '.$e->getCode());
            $interval = new DateInterval('PT1H');
        }

        $currentTime->add($interval);
        $responseToken->exp = $currentTime->format('U');

        // set a unique JSON token ID
        try {
            $responseToken->jti = base64_encode(random_bytes(32));
        } catch(Exception $e) {
            logVar($e->getMessage(), 'ERROR: '.$e->getCode());
            throw $e;
        }

        // set the user's info as our data
        $responseToken->data = $tokenUserData;

        // now, create a JSON Web Token!
        $this->webToken = JWT::encode($responseToken, $config->jwt->key);

        return $this;
    }

    /**
     * decodes a JWT
     * @param array $httpHeaders
     * @return stdClass
     */
    public function decodeWebToken(array $httpHeaders): stdClass
    {
        $config = getAppConfigSettings();
        $response = $this->createResponseObject();

        # is there an authorization header?
        if (! isset($httpHeaders['authorization'])) {
            $response->message = 'Access Denied';
            $response->code = 401;
            return $response;
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
            $response->message = 'Access Denied';
            $response->code = 401;
            return $response;
        }

        # make sure the JWT is good
        try {
            $decoded = JWT::decode($bearerToken, $config->jwt->key, array('HS256'));
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->code = 401;
            return $response;
        }

        $response->success = true;
        $response->results = $decoded;
        return $response;
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
