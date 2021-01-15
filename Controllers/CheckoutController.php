<?php
namespace Main\Controllers;

use Main\Services\CheckoutService;
use Main\Services\JwtService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

use function Main\Utils\checkUserRole as userCheck;

class CheckoutController extends BaseController
{
    private CheckoutService $checkoutService;
    private JwtService $jwtService;
    private UserService $userService;

    public function __construct(CheckoutService $checkoutService, JwtService $jwtService, UserService $userService)
    {
        $this->checkoutService = $checkoutService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;
    }

    /**
     * @inheritDoc
     */
    public function delete(ServerRequest $request, Response $response): Response
    {
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function get(ServerRequest $request, Response $response): Response
    {
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function head(ServerRequest $request, Response $response): Response
    {
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function options(ServerRequest $request, Response $response): Response
    {
        $allowed = 'POST';

        # get the headers, if the request is a CORS preflight request OPTIONS method
        $httpHeaders = $request->getHeaders();

        # the Content-Length header MUST BE "0"
        if (! isset($httpHeaders['access-control-request-method'])) {
            $returnResponse = $response->withAddedHeader('Allow', $allowed)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        } else {
            $returnResponse = $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
                ->withHeader('Access-Control-Allow-Methods', $allowed)
                ->withHeader('Content-Type', 'text/plain')
                ->withHeader('Content-Length', "0");
        }

        $returnResponse->getBody()->write("");

        return $returnResponse;
    }

    /**
     * @inheritDoc
     */
    public function patch(ServerRequest $request, Response $response): Response
    {
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }

    /**
     * @inheritDoc
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
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $requestBody = $this->parsePost($request);

        $userId = $requestBody['id'];
        $res = $this->checkoutService->checkout($userId);
        $body = json_encode($res);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($body)));

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function put(ServerRequest $request, Response $response): Response
    {
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }
}