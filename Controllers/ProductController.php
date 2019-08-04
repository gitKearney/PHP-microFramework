<?php

namespace Main\Controllers;

use Main\Services\JwtService;
use Main\Services\ProductService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * The controller MUST extend BaseController
 *
 * This controller handles all routes for /product/
 */
class ProductController extends BaseController
{

    /**
     * @var JwtService
     */
    private $jwtService;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     *
     * Handle HTTP request that goes to the "product" URI.
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     *
     * @param JwtService $jwtService
     * @param ProductService $productService
     */
    public function __construct(JwtService $jwtService, ProductService $productService)
    {
        $this->jwtService  = $jwtService;
        $this->productService = $productService;
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

        $id = $this->getUrlPathElements($request);

        $config = getAppConfigSettings();
        try {
            if ($config->debug->authUsers) {
                $user = $this->jwtService->decodeWebToken($request->getHeaders());
            }

        } catch (Exception $e) {
            $body = json_encode([
               'error_code' => $e->getCode(),
               'error_msg'  => $e->getMessage(),
           ]);

           $returnResponse = $response->withHeader('Content-Type', 'application/json');
           $returnResponse->getBody()->write($body);

           return $returnResponse;
        }

        # pass the id to the service method, where we'll validate if it's a
        # valid guid
        $res = json_encode($this->productService->deleteProductById($id));

        $returnResponse = $response->withHeader('Content-Type', 'application/json');
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
        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request);

        # if the URI is just /products/, then our ID will be null, get all records
        if ($id == null) {
            # no GUID was passed in, get all records
            $body = json_encode($this->productService->getAllProducts());

            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($body);
            return $returnResponse;
        }

        # pass the id to the service method, where we'll validate it
        $res = json_encode($this->productService->getProductInfo($id));

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
        $jsonRes = json_encode(['TODO' => 'NEED TO IMPLEMENT']);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

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
     * Method to process HTTP PATCH requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function patch(ServerRequest $request, Response $response)
    {
        $config = getAppConfigSettings();
        try {
            if ($config->debug->authUsers) {
                $user = $this->jwtService->decodeWebToken($request->getHeaders());
            }

        } catch (Exception $e) {
            $body = json_encode([
               'error_code' => $e->getCode(),
               'error_msg'  => $e->getMessage(),
           ]);

           $returnResponse = $response->withHeader('Content-Type', 'application/json');
           $returnResponse->getBody()->write($body);

           return $returnResponse;
        }

        # get the POST body as a string: $request->getBody()->__toString()

        # extract the HTTP BODY into an array
        $requestBody = $this->productService->parseServerRequest($request);

        $res = $this->productService->updateProduct($requestBody);

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
        $config = getAppConfigSettings();
        try {
            if ($config->debug->authUsers) {
                $user = $this->jwtService->decodeWebToken($request->getHeaders());
            }

        } catch (Exception $e) {
            $body = json_encode([
               'error_code' => $e->getCode(),
               'error_msg'  => $e->getMessage(),
           ]);

           $returnResponse = $response->withHeader('Content-Type', 'application/json');
           $returnResponse->getBody()->write($body);

           return $returnResponse;
        }

        # if the content type isn't set, default to empty string.
        $contentType = $request->getHeaders()['content-type'][0] ?? '';

        $requestBody =[];

        # if the header is JSON (application/json), parse the data using JSON decode
        if (strpos($contentType, 'application/json') !== false) {
            $requestBody = json_decode($request->getBody()->__toString(), true);

            logVar($requestBody, 'JSON POST BODY ');

        } else if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            # otherwise if the headers are application/x-www-form-urlencoded, everything
            # should already be in an array
            $requestBody = $request->getParsedBody();

            logVar($requestBody, 'URL FORM ENCODED POST BODY ');
        }

        if (count($requestBody) == 0) {
            $res = ['error_code' => 400, 'error_msg' => 'No input data'];
            $jsonRes = json_encode($res);
            $returnResponse = $response->withHeader('Content-Type', 'application/json');
            $returnResponse->getBody()->write($jsonRes);
            return $returnResponse;
        }

        $res = $this->productService->addNewProduct($requestBody);

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
        $requestBody = $this->productService->parseServerRequest($request);

        $result = $this->productService->updateProduct($requestBody);

        $jsonRes = json_encode($result);
        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * Looks at the REQUEST_URI to see if it is /path/ or /path/{guid}
     * @param ServerRequest $request
     * @return array
     */
    protected function getUrlPathElements(ServerRequest $request)
    {
        # split the URI field on the route
        $vals = preg_split('/\/products\//', $request->getServerParams()['REQUEST_URI']);
        if (empty($vals[1])) {

            return null;
        }

        return $vals[1];
    }
}