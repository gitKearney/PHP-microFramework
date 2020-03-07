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

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function defaultOptions(ServerRequest $request, Response $response)
    {
        $allowed = 'OPTIONS, GET, POST, PATCH, PUT, DELETE, HEAD';

        # get the headers, if the request is a C.O.R.S. pre-flight request OPTIONS method
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

    public function parsePost(ServerRequest $request, Response $response)
    {
        # if the content type isn't set, default to empty string.
        $contentType = $request->getHeaders()['content-type'][0] ?? '';

        $requestBody =[];

        # if the header is JSON (application/json), parse the data using JSON decode
        if (strpos($contentType, 'application/json') !== false) {
            $requestBody = json_decode($request->getBody()->__toString(), true);
        } else if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            # otherwise if the headers are application/x-www-form-urlencoded, everything
            # should already be in an array
            $requestBody = $request->getParsedBody();
        }

        return $requestBody;
    }
}
