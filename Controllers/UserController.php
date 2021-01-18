<?php
namespace Main\Controllers;

use Main\Services\JwtService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

use Exception;
use InvalidArgumentException;

use function Main\Utils\checkUserRole as userCheck;

/**
 * Class UserController
 * @package Main\Controllers
 */
class UserController extends BaseController
{
    /**
     * @var UserService
     */
    protected UserService $userService;


    /**
     * @var JwtService
     */
    protected JwtService $jwtService;

    const ROUTE = 'users';

    /**
     *
     * All of our business logic resides in the service (UserService)
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     *
     * @param UserService $userService
     * @param JwtService $jwtService
     */
    public function __construct(UserService $userService, JwtService $jwtService)
    {
        $this->jwtService  = $jwtService;
        $this->userService = $userService;
    }

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function delete(ServerRequest $request, Response $response): Response
    {
        $auth = userCheck($request->getHeaders(),
            'create',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $id = $this->getUrlPathElements($request, self::ROUTE);
        if ($id === null || is_array($id)) {
            $response = $response
                ->withStatus(406)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen('Invalid User ID')));
            return $response;
        }

        # pass the id to the service method, where we'll validate if it's a
        # valid guid
        $result = $this->userService->deleteUserById($id);
        $res = json_encode($result);

        $response = $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($res)));
        $response->getBody()->write($res);
        return $response;
    }

    /**
     * Method to process HTTP GET requests
     * @param ServerRequest $request
     * @param Response $response
     * @param bool $headRequest
     * @return Response
     */
    public function get(ServerRequest $request, Response $response, $headRequest = false): Response
    {
        $auth = userCheck($request->getHeaders(),
            'read',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # read the URI string and see if a GUID was passed in
        $params = $this->getUrlPathElements($request, self::ROUTE);

        # if the URI is just /users/, then our ID will be null, get all records
        if ($params === null) {
            $users = $this->userService->getAllUsers();
        } else if (is_array($params)) {
            unset($params['guid']);
            $users = $this->userService->findUserByQueryString($params);
        } else {
            $users = $this->userService->findUserById($params);
        }

        unset($users->code);
        $body = json_encode($users);

        $response = $response
            ->withStatus(200, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        if (!$headRequest) {
            $response->getBody()->write($body);
        }

        return $response;
    }

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function head(ServerRequest $request, Response $response): Response
    {
        # the HEAD request should return just the headers that a GET request
        # would return
        return $this->get($request, $response, true);
    }

    /**
     * Method to process HTTP OPTION requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function options(ServerRequest $request, Response $response): Response
    {
        return $this->defaultOptions($request, $response);
    }

    /**
     * Method to process HTTP PATCH requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function patch(ServerRequest $request, Response $response): Response
    {
        $auth = userCheck($request->getHeaders(),
            'edit',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # get the POST body as a string: $request->getBody()->__toString()

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);

        $res = $this->userService->updateUser($requestBody);

        $body = json_encode($res);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        $response->getBody()->write($body);
        return $response;
    }

    /**
     * Method to process HTTP POST requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function post(ServerRequest $request, Response $response): Response
    {
        // DO NOT DO USER AUTH TO CREATE A NEW USER
        $requestBody = $this->parsePost($request);

        if (count($requestBody) == 0) {
            $body = json_encode([
                'success' => false,
                'message' => 'No input data',
            ]);

            $response = $response
                ->withStatus(406)
                ->withHeader('Content-Length', strval(strlen($body)))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);
            return $response;
        }

        $res = $this->userService->addNewUser($requestBody);
        $body = json_encode($res);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        $response->getBody()->write($body);
        return $response;
    }

    /**
     * Method to process HTTP PUT requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function put(ServerRequest $request, Response $response): Response
    {
        $headers = $request->getHeaders();
        $auth = userCheck($headers, 'edit', $this->jwtService, $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);
        $result = $this->userService->updateUser($requestBody);
        $body = json_encode($result);
        $response = $response
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));
        $response->getBody()->write($body);

        return $response;
    }
}
