<?php
namespace Controllers;

use Factories\UserFactory;
use Services\UserService;
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
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * The constructor calls a factory to make the service and all models
     * associated with the User
     *
     * All of our business logic resides in the service (UserService)
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     */
    public function __construct()
    {
        # set up a logger so we can add debug messages
        $this->createLogger();

        # also create a debug logger for testing
        $this->createDebugLogger();

        # create a new database class and a service class
        $this->userFactory = new UserFactory;

        # populate the request property (which is actually a server request type
        $this->request = $this->processRequest();

        # establish a Response object
        $this->response = new Response;
    }

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function delete(ServerRequest $request, Response $response)
    {
        $userService = $this->userFactory->create();

        $this->log->debug("processing HTTP DELETE\n");

        # pull the ID from the uri
        $id = null;

        # split the URI field on the route
        $vals = preg_split('/\/users\//', $request->getServerParams()['REQUEST_URI']);
        $id = $vals[1];

        $this->debugLogger->enableLogging();

        # log the URI that we split
        $this->debugLogger->setMessage('splitting URI: ')->logVariable($vals)->write();

        # pass the id to the service method, where we'll validate it's a get_required_files
        $res = json_encode($userService->deleteUserById($id));

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
        $userService = $this->userFactory->create();

        $this->log->debug("processing HTTP GET\n");

        # pull the ID from the uri
        $id = null;

        # split the URI field on the route
        $vals = preg_split('/\/users\//', $request->getServerParams()['REQUEST_URI']);
        $id = $vals[1];

        $this->debugLogger->enableLogging();

        # log the URI that we split
        # TODO: how to handle multiple levels
        $this->debugLogger->setMessage('splitting URI: ')->logVariable($vals)->write();

        # pass the id to the service method, where we'll validate it's a get_required_files
        $res = json_encode($userService->findUserById($id));

        $returnResponse = $this->response->withHeader('Content-Type', 'application/json');
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
        # pass the $request to the service
        $userService = $this->userFactory->create();

        $this->debugLogger->enableLogging()->setMessage('PATCH BODY (from controller)')->logVariable($request->getBody()->__toString())->write();

        $res = $userService->updateUser($request);
        $jsonRes = json_encode($res);
        $returnResponse = $this->response->withHeader('Content-Type', 'application/json');
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
        # pass the $request to the service
        $userService = $this->userFactory->create();


        $res = $userService->addNewUser($request);
        $jsonRes = json_encode($res);
        $returnResponse = $this->response->withHeader('Content-Type', 'application/json');
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
        # pass the $request to the service
        $userService = $this->userFactory->create();

        $this->debugLogger->enableLogging()->setMessage('in the put method')->write();
        $this->debugLogger->enableLogging()->setMessage('PUT BODY (from controller)')->logVariable($request->getBody()->__toString())->write();


        $res = $userService->updateUser($request);
        $jsonRes = json_encode($res);
        $returnResponse = $this->response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);
        return $returnResponse;
    }
}
