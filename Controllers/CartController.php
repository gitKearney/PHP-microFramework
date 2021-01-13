<?php
namespace Main\Controllers;

use Main\Services\CartService;
use Main\Services\JwtService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class CartController extends BaseController
{
    private UserService $userService;
    private JwtService $jwtService;
    private CartService $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
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
    public function get(ServerRequest $request, Response $response): Response
    {
        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request);
        $res = $this->cartService->getUsersCart($id);

        $jsonRes= json_encode($res);
        $returnResponse = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $returnResponse->getBody()->write($jsonRes);

        return $returnResponse;
    }

    /**
     * @inheritDoc
     */
    public function head(ServerRequest $request, Response $response): Response
    {
        // TODO: Implement head() method.
    }

    /**
     * @inheritDoc
     */
    public function options(ServerRequest $request, Response $response): Response
    {
        // TODO: Implement options() method.
    }

    /**
     * @inheritDoc
     */
    public function patch(ServerRequest $request, Response $response): Response
    {
        # get the user's GUID from the URI
        $uriParts = preg_split('/\/carts\//', $request->getServerParams()['REQUEST_URI']);
        $userId = $uriParts[1];

        # get the body which contains the product GUID and the new quantity
        $requestBody = json_decode($request->getBody()->__toString(), true);

        # next we need to remove the item from the user cart table
        $params = [':user_id' => $userId, ':product_id' => $requestBody['product_id']];
        $res = $this->cartService->deleteProductFromCart($params);
        $body = json_encode($res);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
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
        $config = getAppConfigSettings();
        $requestBody = $this->parsePost($request);
        $values = $this->cartService->getCartValue($requestBody);

        if (count($values) === 0) {
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

        $res = $this->cartService->addProductToCart($requestBody);

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
        // TODO: Implement put() method.
    }

    /**
     * Looks at the REQUEST_URI to see if it is /path/ or /path/{guid}
     * @param ServerRequest $request
     * @return null | string | array
     */
    protected function getUrlPathElements(ServerRequest $request)
    {
        $config = getAppConfigSettings();

        # split the URI field on the route
        $requestUri = $request->getServerParams()['REQUEST_URI'];
        $pathValues = preg_split('/\/carts\/?\??/', $requestUri);
        if (empty($pathValues[1])) {
            # no second element found, path is /products
            return null;
        }

        $matches = [];

        # search for only the GUID
        preg_match($config->regex->guid, $pathValues[1], $matches);

        if (!empty($matches[0])) {
            # we found a GUID
            # strip any ? though since our regex is inclusive
            return trim($matches[0], '?');
        }

        # no GUID, expand on the question mark and get the query params
        return $request->getQueryParams();
    }
}