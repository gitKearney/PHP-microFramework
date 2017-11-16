<?php
namespace Main\Controllers;

use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Firebase\JWT\JWT;

/**
 * The controller MUST extend BaseController
 *
 * This controller handles all routes for /user/
 */
class AuthController extends BaseController
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     *
     * All of our business logic resides in the service (UserService)
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function delete(ServerRequest $request, Response $response)
    {
        $id = null;

        # split the URI field on the route and save the ID from the uri
        $vals = preg_split('/\/users\//', $request->getServerParams()['REQUEST_URI']);
        $id = $vals[1];

        # pass the id to the service method, where we'll validate it's a get_required_files
        $res = json_encode($this->userService->deleteUserById($id));

        $returnResponse = $this->response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($res);
        return $returnResponse;
    }

    /**
     * Method to process HTTP GET reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function get(ServerRequest $request, Response $response)
    {
        # pull the ID from the uri
        $id = null;

        # split the URI field on the route
        $vals = preg_split('/\/auth\//', $request->getServerParams()['REQUEST_URI']);
        if (empty($vals[1])) {
            # no GUID was passed in, error out
            $res = json_encode([
                'error' => 'no server params',
                'request_uri' => $request->getServerParams()['REQUEST_URI'],
                'vals' => $vals,
            ]);
            
            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($res);
            return $returnResponse;
        }
        
        # validate credentials
        $res = json_encode([
            'vals' => $vals,
            'request_uri' => $request->getServerParams()['REQUEST_URI'],
        ]);

        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($res);

        return $returnResponse;
    }

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function head(ServerRequest $request, Response $response)
    {
        echo 'TODO: handle HEAD requests';
    }

    /**
     * Method to process HTTP OPTION requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function options(ServerRequest $request, Response $response)
    {
        echo 'TODO: handle OPTIONS requests';
    }

    /**
     * Method to process HTTP PATCH reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function patch(ServerRequest $request, Response $response)
    {

        # get the POST body as a string: $request->getBody()->__toString()

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);

        $res = $this->userService->updateUser($requestBody);

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * authenticate a user using credentials send in the body
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function post(ServerRequest $request, Response $response)
    {
        # get the body from the HTTP request
        $requestBody = $request->getParsedBody();

        # verify that the user is valid
        $tokenUserData =  new \stdClass();
        $tokenUserData->userId = '123'; // put GUID here
        $tokenUserData->email  = 'bob@example.com'; //put email here

        $responseToken = new \stdClass();

        // create a datetime object to work with
        $currentTime = new \DateTime("now");

        // set the issued at time
        $responseToken->iat = $currentTime->format('U');

        // set the not before time
        $responseToken->nbf = $currentTime->format('U');

        // set the issuer name
        $responseToken->iss = 'http://example.com';
        $responseToken->aud = 'http://example.com';

        // set the expiry time to 1 hour 30 minutes
        $interval = new \DateInterval('PT1H30M');
        $currentTime->add($interval);
        $responseToken->exp = $currentTime->format('U');
        
        // set a unique JSON token ID
        $responseToken->jti = base64_encode(random_bytes(32));
        
        // set the user's info as our data
        $responseToken->data = $tokenUserData;

        // now, create a JSON Web Token!
        $jot = JWT::encode($responseToken, '<3_my_iPhone');

        // $jsonRes = json_encode($requestBody);
        $returnResponse = $response->withHeader('Content-Type', 'application/text');
        $returnResponse->getBody()->write($jot);

        return $returnResponse;
    }

    /**
     * Method to process HTTP PUT requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function put(ServerRequest $request, Response $response)
    {
        # extract the HTTP BODY into an array
        # get the body from the HTTP request
        $requestBody = json_decode($request->getBody()->__toString());
        
        if (is_null($requestBody)) {
            # the body isn't a JSON string, it's a form URL encoded string
            # so, convert it here
            $requestBody = [];
            parse_str($request->getBody()->__toString(), $requestBody);
        }

        $jwt = $requestBody['jot'];

        $res = JWT::decode($jwt, '<3_my_iPhone', ['HS256']);

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }
}
