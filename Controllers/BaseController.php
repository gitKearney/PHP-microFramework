<?php
namespace Controllers;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Services\DebugLogger;

abstract class BaseController
{
    use RequestTrait;

    /**
     * @var ServerRequest
     */
     protected $request;

     /**
      * @var Response
      */
      protected $response;

     /**
      * @var Logger
      */
     protected $log;

     /**
      * @var DebugLogger
      */
     protected $debugLogger;

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function delete(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP GET reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function get(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function head(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP OPTION requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function options(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP PATCH reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function patch(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP POST requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function post(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP PUT requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function put(ServerRequest $request, Response $response);

    /**
     * Use this if HTTP request method is not supported
     * @return Response
     */
    public function unsupportedMethod()
    {
        $response = new Response;
        $jsonResponse = json_encode(['invalid HTTP request method' => 'We currently do not support this method']);

        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonResponse);
        return $returnResponse;
    }

    /**
     * This method creates a ServerRequest (which is used on each method)
     * It pulls in all server variables into an array
     * @return Response
     */
    public function handleRequest()
    {
        # Get the HTTP Request type, and forward to the correct method
        # Not sure if this belongs here, but if we keep with REST, then
        # all HTTP DELETE requests will be sent ot the "delete()" method
        switch ($this->request->getServerParams()['REQUEST_METHOD']) {
            case 'DELETE':
                return $this->delete($this->request, $this->response);
                break;
            case 'GET':
                return $this->get($this->request, $this->response);
                break;
            case 'HEAD':
                return $this->head($this->request, $this->response);
                break;
            case 'OPTIONS':
                return $this->options($this->request, $this->response);
                break;
            case 'PATCH':
                return $this->patch($this->request, $this->response);
                break;
            case 'POST':
                return $this->post($this->request, $this->response);
                break;
            case 'PUT':
                return $this->put($this->request, $this->response);
                break;
            default:
                # This route catches the HTTP request types that no one uses
                # like TRACE,
                return $this->unsupportedMethod();
        }
    }

    /**
     * @desc creates a debug logger
     * @return void
     */
    public function createLogger()
    {
        $this->log = new Logger('name');
        $this->log->pushHandler(new StreamHandler('/tmp/debug.log', Logger::DEBUG));
    }

    /**
     * Set up a debug logger to use debuggin app
     * @param string $logFileName
     * @return  BaseController
     */
    public function createDebugLogger($logFileName = null)
    {
        if (is_null($logFileName)) {
            $this->debugLogger = new DebugLogger;
        } else {
            $this->debugLogger = new DebugLogger($logFileName);
        }

        return $this;
    }
}
