<?php
namespace Main\Controllers;

use Main\Services\CheckoutService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class CheckoutController extends BaseController
{
    private CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * @inheritDoc
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
        // TODO: Implement get() method.
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
        // TODO: Implement patch() method.
    }

    /**
     * @inheritDoc
     */
    public function post(ServerRequest $request, Response $response): Response
    {
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
        // TODO: Implement put() method.
    }

    protected function getUrlPathElements(ServerRequest $request)
    {
        // TODO: Implement getUrlPathElements() method.
    }
}