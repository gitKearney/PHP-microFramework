<?php
namespace Main\Controllers;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

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
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function delete(ServerRequest $request, Response $response);

    /**
     * Method to process HTTP GET requests
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
     * Method to process HTTP PATCH requests
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
        
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withStatus(404);

        $returnResponse->getBody()->write('Not Found');
        return $returnResponse;
    }

    /**
     * This method creates a ServerRequest (which is used on each method)
     * It pulls in all server variables into an array
     * @return Response
     */
    public function handleRequest()
    {
        $request = $this->processRequest();

        # establish a Response object
        $response = new Response;

        # Get the HTTP Request type, and forward to the correct method
        # Not sure if this belongs here, but if we keep with REST, then
        # all HTTP DELETE requests will be sent ot the "delete()" method
        switch ($request->getServerParams()['REQUEST_METHOD']) {
            case 'DELETE':
                return $this->delete($request, $response);
                break;
            case 'GET':
                return $this->get($request, $response);
                break;
            case 'HEAD':
                return $this->head($request, $response);
                break;
            case 'OPTIONS':
                return $this->options($request, $response);
                break;
            case 'PATCH':
                return $this->patch($request, $response);
                break;
            case 'POST':
                return $this->post($request, $response);
                break;
            case 'PUT':
                return $this->put($request, $response);
                break;
            default:
                # This route catches the HTTP request types that no one uses
                # like TRACE,
                return $this->unsupportedMethod();
        }
    }

    abstract protected function getUrlPathElements(ServerRequest $request);
}
