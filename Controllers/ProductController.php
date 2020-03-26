<?php

namespace Main\Controllers;

use Exception;
use Main\Services\JwtService;
use Main\Services\ProductService;
use Main\Services\UserService;
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
     * @var UserService
     */
    private $userService;

    /**
     *
     * Handle HTTP request that goes to the "product" URI.
     *
     * NOTE: to keep with REST, forward all calls to the method
     *       that corresponds to the HTTP Request Method
     *
     * @param JwtService $jwtService
     * @param ProductService $productService
     * @param UserService $userService
     */
    public function __construct(JwtService $jwtService, ProductService $productService, UserService $userService)
    {
        $this->jwtService  = $jwtService;
        $this->productService = $productService;
        $this->userService = $userService;
    }

    /**
     * Method to process HTTP DELETES
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function delete(ServerRequest $request, Response $response)
    {
        $id = null;

        $id = $this->getUrlPathElements($request);

        $config = getAppConfigSettings();
        try {
            if ($config->debug->authUsers) {

                /**
                 * @var \stdClass
                 */
                $decodedJwt = $this->jwtService->decodeWebToken($request->getHeaders());

                # does the user have access to this method?
                $userId = $decodedJwt->data->userId;
                $hasPermission = $this->userService->userAllowedAction($userId, 'create');

                if (!$hasPermission) {
                    throw new Exception('Action Not Allowed', '100');
                }
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

        try {
            // NOTE: config is a global variable defined in credentials.php
            $config = getAppConfigSettings();
            if ($config->debug->authUsers) {
                $decodedJwt = $this->jwtService->decodeWebToken($request->getHeaders());

                # does the user have access to this method?
                $userId = $decodedJwt->data->userId;

                $hasPermission = $this->userService->userAllowedAction($userId, 'create');
                if (!$hasPermission) {
                    throw new Exception('Action Not Allowed', '100');
                }
            }
        } catch (Exception $e) {
            $body = json_encode([
                'error_code' => $e->getCode(),
                'error_msg'  => $e->getMessage(),
            ]);

            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json');

            $returnResponse->getBody()->write($body);

            return $returnResponse;
        }

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

        # is the ID a GUID or a query string?
        if (is_array($id)) {
            $res = $this->productService->getProductsByQueryString($id);
        } else {
            # pass the id to the service method, where we'll validate it
            $res = $this->productService->getProductInfo($id);
        }

        $jsonRes = json_encode($res);

        $returnResponse = $response->withHeader('Content-Type', 'application/json');
        $returnResponse->getBody()->write($jsonRes);

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
        return $this->defaultOptions($request, $response);
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
                $decodedJwt = $this->jwtService->decodeWebToken($request->getHeaders());

                # does the user have access to this method?
                $userId = $decodedJwt->data->userId;

                $hasPermission = $this->userService->userAllowedAction($userId, 'create');
                if (!$hasPermission) {
                    throw new Exception('Action Not Allowed', '100');
                }
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

        $requestBody = $this->parsePost($request, $response);

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
     * @return string | array
     */
    protected function getUrlPathElements(ServerRequest $request)
    {
        # split the URI field on the route
        $requestUri = $request->getServerParams()['REQUEST_URI'];
        $vals = preg_split('/\/products\/?\??/', $requestUri);
        if (empty($vals[1])) {
            # this means that there is no "second" element found so the user just passed in /products
            # to the URI - so return all products
            return '';
        }

        $matches = [];

        # search for only the GUID
        preg_match('/^[a-f\d]{8}-([a-f\d]{4}-){3}[a-f\d]{12}$/i', $vals[1], $matches);

        if (!empty($matches[0])) {
            # if we found a GUID return that GUID and search for the product with that ID
            $matches[0];
        }

        # we don't have a guid, expand on the question mark and split the
        $queryParams = [];
        parse_str($vals[1], $queryParams);

        return $queryParams;
    }
}