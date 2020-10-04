<?php


namespace Main\Controllers;


use Main\Services\TransactionService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class TransactionController extends BaseController
{
    /**
     * @var TransactionService
     */
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
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
        $config = getAppConfigSettings();

        $transactions = $this->transactionService->getAllTransactions();
        unset($transactions->code);

        $body = json_encode($transactions);

        $response = $response->withStatus(200, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($body)));

        return $response;
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
     * @inheritDoc
     */
    public function post(ServerRequest $request, Response $response)
    {
        // TODO: Implement post() method.
    }

    /**
     * @inheritDoc
     */
    public function put(ServerRequest $request, Response $response)
    {
        // TODO: Implement put() method.
    }

    protected function getUrlPathElements(ServerRequest $request)
    {
        // TODO: Implement getUrlPathElements() method.
    }
}