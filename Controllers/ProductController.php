<?php

namespace Main\Controllers;

use Exception;
use Main\Services\JwtService;
use Main\Services\ProductService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

use function Main\Utils\checkUserRole as userCheck;

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
    private JwtService $jwtService;

    /**
     * @var ProductService
     */
    private ProductService $productService;

    /**
     * @var UserService
     */
    private UserService $userService;

    const ROUTE = 'products';

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
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $qp = $this->getUrlPathElements($request, self::ROUTE);
        if ($qp === null) {
            $response = $response->withStatus(100, 'Not Allowed')
                ->withHeader('Content-Type', 'application/json');
            return $response;
        }

        # pass the id to the service method, where we'll validate if it's a
        # valid guid
        $result = $this->productService->deleteProductById($qp['guid']);
        $res = json_encode($result);

        $response = $response
            ->withStatus($result->code, $result->message)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($res);
        return $response;
    }

    /**
     * Method to process HTTP GET reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function get(ServerRequest $request, Response $response): Response
    {
        $auth = userCheck($request->getHeaders(),
            'read',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request, self::ROUTE);

        # if the URI is just /products/, then our ID will be null, get all records
        if ($id === null) {
            # no GUID was passed in, get all records
            $res = $this->productService->getAllProducts();
            $body = json_encode($res);

            $returnResponse = $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $returnResponse->getBody()->write($body);
            return $returnResponse;
        } else if (is_array($id)) {
            # is the ID a GUID or a query string?
            $res = $this->productService->getProductsByQueryString($id);
        } else {
            # pass the id to the service method, where we'll validate it
            $res = $this->productService->getProductInfo($id);
        }

        $jsonRes = json_encode($res);

        $returnResponse = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * Method to process HTTP HEAD
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function head(ServerRequest $request, Response $response): Response
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
    public function options(ServerRequest $request, Response $response): Response
    {
        return $this->defaultOptions($request, $response);
    }

    /**
     * Method to process HTTP PATCH requests
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
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
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
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
    public function post(ServerRequest $request, Response $response): Response
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
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $requestBody = $this->parsePost($request);

        if (count($requestBody) === 0) {
            $body = json_encode([
                'success' => false,
                'message' => 'No input data',
            ]);

            $response = $response
                ->withHeader('Content-Length', strval(strlen($body)))
                ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write($body);
            return $response;
        }

        $res = $this->productService->addNewProduct($requestBody);

        $body = json_encode($res);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
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
        $auth = userCheck($request->getHeaders(),
            'edit',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # extract the HTTP BODY into an array
        $requestBody = $this->userService->parseServerRequest($request);
        $result = $this->userService->updateUser($requestBody);
        $body = json_encode($result);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($body)));
        $response->getBody()->write($body);

        return $response;
    }
}