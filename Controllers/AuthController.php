<?php
namespace Main\Controllers;

use Main\Services\AuthService;
use Main\Services\UserService;
use stdClass;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Exception;

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
    private UserService $userService;

    /**
     * @var AuthService
     */
    private AuthService $authService;

    /**
     *
     * All of our business logic resides in the service (UserService)
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     *
     * @param UserService $userService
     * @param AuthService $authService
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
    public function delete(ServerRequest $request, Response $response): Response
    {
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('HTTP/1.0', '404 Not Found');
        $returnResponse->getBody()->write('');
        return $returnResponse;
    }

    /**
     * Method to process HTTP GET requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function get(ServerRequest $request, Response $response): Response
    {
        // $returnResponse = $response
        //    ->withHeader('Access-Control-Allow-Origin', '*');

        $returnResponse = $response->withStatus(404);
        $returnResponse->getBody()->write('Not Found');
        return $returnResponse;
    }

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function head(ServerRequest $request, Response $response): Response
    {
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*');
        $returnResponse->getBody()->write('');
        return $returnResponse;
    }

    /**
     * Method to process HTTP OPTION requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function options(ServerRequest $request, Response $response): Response
    {
        $allowed = 'OPTIONS, POST';

        # get the headers, if the request is a CORS preflight request OPTIONS method
        $httpHeaders = $request->getHeaders();

        # the Content-Length header MUST BE "0"
        if (! isset($httpHeaders['access-control-request-method'])) {
            $returnResponse = $response->withAddedHeader('Allow', $allowed)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        } else {
            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
                ->withHeader('Access-Control-Allow-Methods', $allowed)
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        }

        $returnResponse->getBody()->write("");

        return $returnResponse;
    }

    /**
     * Method to process HTTP PATCH requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function patch(ServerRequest $request, Response $response): Response
    {
        
        $returnResponse = $response->withStatus(404);
        $returnResponse->getBody()->write('Not Found');
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
    public function post(ServerRequest $request, Response $response): Response
    {
        $requestBody = $this->parsePost($request);

        if (count($requestBody) == 0) {
            $res = ['message' => 'Invalid Data'];
            $body = json_encode($res);
            $response = $response
                ->withStatus(401, 'Invalid User')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));
            $response->getBody()->write($body);
            return $response;
        }

        $token = $this->authService->createJwt($requestBody);
        $body = json_encode($token);

        $returnResponse = $response
            ->withStatus(200, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        $returnResponse->getBody()->write($body);

        return $returnResponse;
    }

    /**
     * Method to process HTTP PUT requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function put(ServerRequest $request, Response $response): Response
    {
        $returnResponse = $response->withStatus(404);
        $returnResponse->getBody()->write('Not Found');
        return $returnResponse;
    }
}
