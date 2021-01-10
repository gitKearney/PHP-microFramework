<?php
namespace Main\Controllers;

use Main\Services\JwtService;
use Main\Services\TransactionService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class TransactionController extends BaseController
{
    /**
     * @var TransactionService
     */
    private TransactionService $transactionService;

    /** @var JwtService */
    private JwtService $jwtService;

    /** @var UserService */
    private UserService $userService;

    public function __construct(TransactionService $transactionService,
                                JwtService $jwtService,
                                UserService $userService)
    {
        $this->transactionService = $transactionService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;
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
        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request);
        if (!$id) {
            $transactions = $this->transactionService->getAllTransactions();
        } else {
            $transactions = $this->transactionService;
        }

        $body = json_encode($transactions);

        $response = $response->withStatus($transactions->code, 'OK')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($body)));

        $response->getBody()->write($body);
        return $response;
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
        $config = getAppConfigSettings();
        if ($config->debug->authUsers) {
            $decoded = $this->jwtService->decodeWebToken($request->getHeaders());

            if (!$decoded->success) {
                $body = json_encode($decoded);

                $response = $response
                    ->withStatus(401, $decoded->message)
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Content-Length', strval(strlen($body)));

                $response->getBody()->write($body);

                return $response;
            }

            # does the user have access to this method?
            $userId = $decoded->results->data->userId;
            $hasPermission = $this->userService->userAllowedAction($userId, 'create');
            if (!$hasPermission) {
                $response = $response
                    ->withStatus(100, 'Action Not Allowed')
                    ->withHeader('Content-Type', 'application/json');

                return $response;
            }
        }

        // TODO: send to the service the products, user ID
        $body = $this->parsePost($request);
        $res = $this->transactionService->addNewTransaction($body);

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