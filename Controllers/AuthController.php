<?php
namespace Main\Controllers;

use Main\Services\AuthService;
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
    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
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
        $allowed = 'OPTIONS, GET, POST, PATCH, PUT, DELETE, HEAD';

        # get the headers, if the request is a CORS preflight request OPTIONS method
        $httpHeaders = $request->getHeaders();

        # the Content-Length header MUST BE "0"
        if (! isset($httpHeaders['access-control-request-method'])) {
            $returnResponse = $response->withAddedHeader('Allow', $allowed)
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        } else {

            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', $allowed)
                ->withHeader('Access-Control-Allow-Headers',
                    'application/x-www-form-urlencoded, X-Requested-With, content-type, Authorization')
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        }

        $returnResponse->getBody()->write("");

        return $returnResponse;
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
     * authenticate a user using credentials send in the body, return a JWT
     * that the user can use on every request
     *
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function post(ServerRequest $request, Response $response)
    {
        # get the body from the HTTP request
        $requestBody = $request->getParsedBody();

        try {
            $webToken = $this->authService->createJwt($requestBody);
        }
        catch (\Exception $e) {
            $error = new \stdClass();
            $error->error_code = $e->getCode();
            $error->error_msg  = $e->getMessage();
            $jsonRes = json_encode($error);

            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);

            return $returnResponse;
        }

        $returnResponse = $response->withHeader('Content-Type', 'application/text');
        $returnResponse->getBody()->write($webToken);

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
        logVar($requestBody['jot'], 'jot = ');

        $res = new \stdClass();

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * Parse the URI for path elements like /auth/ or /auth/{GUID}
     */
    public function getUrlPathElements(ServerRequest $request)
    {
        # split the URI field on the route
        $vals = preg_split('/\/auth\//', $request->getServerParams()['REQUEST_URI']);
        if (empty($vals[1])) {
            # if the URI is just 'http://example.com/auth/' then we won't have
            # anything to return, return null
            return null;
        }

        # if the URI is 'http://example.com/auth/12345', then return '12345'
        # TODO: if your paths are something like http://example.com/auth/12345/valid
        #       you may want to do additional parsing
        return $vals[1];
    }
}
