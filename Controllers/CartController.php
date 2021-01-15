<?php
namespace Main\Controllers;

use Main\Services\CartService;
use Main\Services\JwtService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use function Main\Utils\checkUserRole as userCheck;

class CartController extends BaseController
{
    private UserService $userService;
    private JwtService $jwtService;
    private CartService $cartService;

    const ROUTE = 'carts';

    public function __construct(CartService $cartService, JwtService $jwtService, UserService $userService)
    {
        $this->cartService = $cartService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;
    }

    /**
     * @desc This method isn't used - we just ignore it. To remove an item from
     * the cart, use PATCH
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function delete(ServerRequest $request, Response $response): Response
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
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
        $res = $this->cartService->getUsersCart($id);

        $body = json_encode($res);
        $returnResponse = $response
            ->withStatus(200, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        if ($headRequest) {
            return $returnResponse;
        }

        $returnResponse->getBody()->write($body);

        return $returnResponse;
    }

    /**
     * @inheritDoc
     */
    public function head(ServerRequest $request, Response $response): Response
    {
        return $this->get($request, $response, true);
    }

    /**
     * @inheritDoc
     */
    public function options(ServerRequest $request, Response $response): Response
    {
        $allowed = 'OPTIONS, GET, HEAD, PATCH, POST';

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
        $auth = userCheck($request->getHeaders(),
            'read',
            $this->jwtService,
            $this->userService);

        if (!$auth->success) {
            $body = json_encode($auth->message);

            $response = $response
                ->withStatus($auth->code, $auth->message)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json: charset=utf-8')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $userId = $this->getUrlPathElements($request, self::ROUTE);
        $requestBody = $this->parsePost($request);

        # next we need to remove the item from the user cart table
        $requestBody['user_id'] = $userId;
        $res = $this->cartService->deleteProductFromCart($requestBody);
        $body = json_encode($res);
        $response = $response
            ->withStatus($res->code, $res->message)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Content-Length', strval(strlen($body)));

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @desc takes in a user ID and product ID and adds it to the cart table
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
        $values = $this->cartService->getCartValue($requestBody);

        if ($values['product_id'] === null || $values['user_id'] === null) {
            $body = json_encode([
                'success' => false,
                'message' => 'No input data',
            ]);

            $response = $response
                ->withStatus(401)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Length', strval(strlen($body)))
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write($body);
            return $response;
        }

        $res = $this->cartService->addProductToCart($requestBody);

        $body = json_encode($res);
        $response = $response
            ->withAddedHeader($res->code, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
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