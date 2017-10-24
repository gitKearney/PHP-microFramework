<?php
namespace Main\Controllers;

use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * The controller MUST extend BaseController
 *
 * This controller handles all routes for /user/
 */
class UserController extends BaseController
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
        $vals = preg_split('/\/users\//', $request->getServerParams()['REQUEST_URI']);
        if (empty($vals[1])) {
            # no GUID was passed in, get all records
            $res = json_encode($this->userService->getAllUsers());
            
            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($res);
            return $returnResponse;
        }
        
        $id = $vals[1];
        
        # log the URI that we split
        logVar($vals, 'splitting URI: ');

        # pass the id to the service method, where we'll validate it
        $res = json_encode($this->userService->findUserById($id));

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
     * Method to process HTTP POST requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function post(ServerRequest $request, Response $response)
    {
        # get the body from the HTTP request
        $requestBody = $request->getParsedBody();

        $res = $this->userService->addNewUser($requestBody);

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
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
        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);

        $res = $this->userService->updateUser($requestBody);

        $jsonRes = json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }
}
