<?php
namespace Main\Controllers;

use Main\Services\CartService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class CartController extends BaseController
{
    private $userService;
    private $jwtService;
    private $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @inheritDoc
     */
    public function delete(ServerRequest $request, Response $response)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function get(ServerRequest $request, Response $response)
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
    public function head(ServerRequest $request, Response $response)
    {
        // TODO: Implement head() method.
    }

    /**
     * @inheritDoc
     */
    public function options(ServerRequest $request, Response $response)
    {
        // TODO: Implement options() method.
    }

    /**
     * @inheritDoc
     */
    public function patch(ServerRequest $request, Response $response)
    {
        // TODO: Implement patch() method.
    }

    /**
     * @desc takes in a user ID and product ID and adds it to the cart table
     */
    public function post(ServerRequest $request, Response $response)
    {
        # this takes in a user ID, and product ID

        #
    }

    /**
     * @inheritDoc
     */
    public function put(ServerRequest $request, Response $response)
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