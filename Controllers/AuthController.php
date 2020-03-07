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
    private $userService;

    /**
     * @var AuthService
     */
    private $authService;

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
    public function delete(ServerRequest $request, Response $response)
    {

        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('HTTP/1.0', '404 Not Found');
        $returnResponse->getBody()->write('');
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
    public function head(ServerRequest $request, Response $response)
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
    public function options(ServerRequest $request, Response $response)
    {
        $allowed = 'OPTIONS, HEAD, POST';

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
     * @throws \Exception
     */
    public function patch(ServerRequest $request, Response $response)
    {
        
        $returnResponse = $response->withStatus(404);;
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
    public function post(ServerRequest $request, Response $response)
    {
        $requestBody = $this->parsePost($request, $response);

        if (count($requestBody) == 0) {
            $res = ['error_code' => 400, 'error_msg' => 'No input data'];
            $jsonRes = json_encode($res);
            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);
            return $returnResponse;
        }

        try {
            $webToken = $this->authService->createJwt($requestBody);
        }
        catch (\TypeError $e) {
            $error = new \stdClass();
            $error->success = false;
            $error->message  = 'Failed to create web token';

            logVar('EXCEPTION CREATING TOKEN: ', $e->getMessage(), 'critical');
            $jsonRes = json_encode($error);

            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);

            return $returnResponse;
        }
        catch (\Exception $e) {
            $error = new \stdClass();
            $error->success = false;
            $error->message  = $e->getMessage();
            $jsonRes = json_encode($error);

            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);

            return $returnResponse;
        }

        $successResponse = new \stdClass();
        
        $successResponse->success = true;
        $successResponse->message = 'success';
        $successResponse->results = $webToken;

        $jsonRes = json_encode($successResponse);

        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json');

        $returnResponse->getBody()->write($jsonRes);

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
        $returnResponse = $response->withStatus(404);
        $returnResponse->getBody()->write('Not Found');
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
