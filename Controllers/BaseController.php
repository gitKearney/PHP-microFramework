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
    protected ServerRequest $request;

    /**
     * @var Response
     */
    protected Response $response;

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function delete(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP GET requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function get(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function head(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP OPTION requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function options(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP PATCH requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function patch(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP POST requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function post(ServerRequest $request, Response $response): Response;

    /**
     * Method to process HTTP PUT requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    abstract public function put(ServerRequest $request, Response $response): Response;

    /**
     * Use this if HTTP request method is not supported
     * @return Response
     */
    public function unsupportedMethod(): Response
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
    public function handleRequest(): Response
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
            case 'GET':
                return $this->get($request, $response);
            case 'HEAD':
                return $this->head($request, $response);
            case 'OPTIONS':
                return $this->options($request, $response);
            case 'PATCH':
                return $this->patch($request, $response);
            case 'POST':
                return $this->post($request, $response);
            case 'PUT':
                return $this->put($request, $response);
            default:
                # This route catches the HTTP request types that no one uses
                # like TRACE
                return $this->unsupportedMethod();
        }
    }

    /**
     * Looks at the REQUEST_URI to see if it is /path/ or /path/{guid}
     * or /path/{guid}?param1=value1 or /path/?param1=value1
     * @param ServerRequest $request
     * @return null | string | array
     */
    protected function getUrlPathElements(ServerRequest $request, string $route)
    {
        $config = getAppConfigSettings();

        # split the URI field on the route
        $requestUri = $request->getServerParams()['REQUEST_URI'];
        $queryParams = $request->getQueryParams();;

        $params = preg_split("/\/$route\/?\??/", $requestUri);
        if (empty($params[1])) {
            # no second element found, path is /products
            return null;
        }

        $matches = [];
        preg_match($config->regex->guid, $params[1], $matches);

        if (!empty($matches[0])) {
            # we found a UUID
            # strip any ? though since our regex is inclusive. If the user
            # passed in a UUID **AND** query params, default to use the UUID
            return trim($matches[0], '?');
        }

        # no GUID, expand on the question mark and get the query params
        return $queryParams;
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function defaultOptions(ServerRequest $request, Response $response): Response
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

    /**
     * @param ServerRequest $request
     * @return array|mixed|object
     */
    public function parsePost(ServerRequest $request)
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
