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
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $id = $this->getUrlPathElements($request, self::ROUTE);
        if ($id === null || is_array($id)) {
            $response = $response
                ->withStatus(406)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen('Invalid Product')));
            return $response;
        }

        # pass the id to the service method, where we'll validate if it's a
        # valid guid
        $result = $this->productService->deleteProductById($id);
        $body = json_encode($result);

        $response = $response
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));
        $response->getBody()->write($body);
        return $response;
    }

    /**
     * Method to process HTTP GET reqeusts
     * @param ServerRequest $request
     * @param Response $response
     * @param bool $headRequest
     * @return Response
     */
    public function get(ServerRequest $request, Response $response, $headRequest = false): Response
    {
        $auth = userCheck($request->getHeaders(),
            'read',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
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
                ->withStatus(200)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));
            $returnResponse->getBody()->write($body);

            return $returnResponse;
        } else if (is_array($id)) {
            # is the ID a GUID or a query string?
            $res = $this->productService->getProductsByQueryString($id);
        } else {
            # pass the id to the service method, where we'll validate it
            $res = $this->productService->getProductInfo($id);
        }

        $body = json_encode($res);

        $returnResponse = $response
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        if ($headRequest) {
            return $response;
        }

        $returnResponse->getBody()->write($body);

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
        return $this->get($request, $response, true);
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
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # extract the HTTP BODY into an array
        $requestBody = $this->productService->parseServerRequest($request);

        $res = $this->productService->updateProduct($requestBody);

        $body = json_encode($res);
        $returnResponse = $response
            ->withStatus($res->code)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));
        $returnResponse->getBody()->write($body);

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
            'read',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
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
                ->withStatus(406)
                ->withHeader('Content-Length', strval(strlen($body)))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);
            return $response;
        }

        $res = $this->productService->addNewProduct($requestBody);

        $body = json_encode($res);
        $response = $response
            ->withStatus($res->code)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
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
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        # extract the HTTP BODY into an array
        $requestBody = $this->productService->parseServerRequest($request);
        $result = $this->productService->updateProduct($requestBody);
        $body = json_encode($result);
        $response = $response
            ->withStatus($auth->code)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));
        $response->getBody()->write($body);

        return $response;
    }
}