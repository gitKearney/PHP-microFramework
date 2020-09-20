<?php
namespace Main\Controllers;

use Main\Services\JwtService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

use Exception;
use InvalidArgumentException;
use stdClass;

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
    protected $userService;


    /**
     * @var JwtService
     */
    protected $jwtService;

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
    public function delete(ServerRequest $request, Response $response)
    {
        $config = getAppConfigSettings();
        if ($config->debug->authUsers) {
            $user = $this->jwtService->decodeWebToken($request->getHeaders());
            if (!$user->success) {
                $body = json_encode($user);

                $response = $response
                    ->withStatus(401, $user->message)
                    ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write($body);

                return $response;
            }

            # does the user have access to this method?
            $userId = $user->results->data->userId;

            $hasPermission = $this->userService->userAllowedAction($userId, 'create');
            if (!$hasPermission) {
                $response = $response
                    ->withStatus(100, 'Action Not Allowed')
                    ->withHeader('Content-Type', 'application/json');

                return $response;
            }
        }

        $id = $this->getUrlPathElements($request);

        # pass the id to the service method, where we'll validate if it's a
        # valid guid
        $res = json_encode($this->userService->deleteUserById($id));

        $response = $response->withHeader('Content-Type', 'application/json');
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
    public function get(ServerRequest $request, Response $response, $headRequest = false)
    {
        $config = getAppConfigSettings();

        if ($config->debug->authUsers) {
            $user = $this->jwtService->decodeWebToken($request->getHeaders());
            if (!$user->success) {
                $body = json_encode($user);

                $response = $response
                    ->withStatus($user->code, $user->message)
                    ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write($body);

                return $response;
            }
        }

        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request);

        # if the URI is just /users/, then our ID will be null, get all records
        if ($id == null) {
            $users = $this->userService->getAllUsers();
            $res = json_encode($users);

            $response = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($res)));

            if (!$headRequest) {
                $response->getBody()->write($res);
            }

            return $response;
        }

        # pass the id to the service method, where we'll validate it
        if (is_array($id)) {
            $res = $this->userService->findUserByQueryString($id);
        } else {
            $res = $this->userService->findUserById($id);
        }

        $res = json_encode($res);

        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($res)));

        if (!$headRequest) {
            $response->getBody()->write($res);
        }

        return $response;
    }

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function head(ServerRequest $request, Response $response)
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
    public function options(ServerRequest $request, Response $response)
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
    public function patch(ServerRequest $request, Response $response)
    {
        $config = getAppConfigSettings();
        if ($config->debug->authUsers) {
            $user = $this->jwtService->decodeWebToken($request->getHeaders());
            if (!$user->success) {
                $body = json_encode($user);

                $response = $response
                    ->withStatus($user->code, $user->message)
                    ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write($body);

                return $response;
            }

            # does the user have access to this method?
            $userId = $user->results->data->userId;

            $hasPermission = $this->userService->userAllowedAction($userId, 'edit');
            if (!$hasPermission) {
                $response = $response
                    ->withStatus(100, 'Action Not Allowed')
                    ->withHeader('Content-Type', 'application/json');

                return $response;
            }
        }

        # get the POST body as a string: $request->getBody()->__toString()

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);

        $res = $this->userService->updateUser($requestBody);

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json');

        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * Method to process HTTP POST requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function post(ServerRequest $request, Response $response)
    {
        $requestBody = $this->parsePost($request, $response);

        if (count($requestBody) == 0) {
            $jsonRes = json_encode([
                'success' => false,
                'message' => 'No input data',
            ]);

            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);
            return $returnResponse;
        }

        $res = $this->userService->addNewUser($requestBody);

        $jsonRes = json_encode($res);
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
        $config = getAppConfigSettings();
//       ($request->getHeaders(),$config->debug->authUsers,
//            'edit', $this->jwtService, $this->userService);
        $user = userCheck($request->getHeaders(),$config->debug->authUsers,
            'edit', $this->jwtService, $this->userService);

        if (!$user->success) {
            $body = json_encode([
                'message' => $user->message,
            ]);

            $response = $response
                ->withStatus($user->code, $user->message)
                ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write($body);

            return $response;
        }

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);
        $result = $this->userService->updateUser($requestBody);
        $jsonRes = json_encode($result);
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * Looks at the REQUEST_URI to see if it is /users/ or /users/{guid}
     * @param ServerRequest $request
     * @return string | array
     */
    protected function getUrlPathElements(ServerRequest $request)
    {
        /** @var stdClass */
        $config = getAppConfigSettings();

        # split the URI field on the route
        $requestUri = $request->getServerParams()['REQUEST_URI'];
        $params = preg_split('/\/users[\/?]/', $requestUri);
        if (empty($params[1])) {
            return '';
        }

        $matches = [];

        # search for only the GUID
        preg_match($config->regex->uri_guid, $params[1], $matches);

        if (!empty($matches[0])) {
            # strip any ? though since our regex is inclusive
            return trim($matches[0], '?');
        }

        return $request->getQueryParams();
    }
}
