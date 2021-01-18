<?php
namespace Main\Controllers;

use Main\Services\JwtService;
use Main\Services\TransactionService;
use Main\Services\UserService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

use function Main\Utils\checkUserRole as userCheck;

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

    const ROUTE = 'transactions';

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
        $response = $response->withStatus(404);
        $response->getBody()->write('Not Found');
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function get(ServerRequest $request, Response $response, $headRequest=false): Response
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
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }
        # read the URI string and see if a GUID was passed in
        $id = $this->getUrlPathElements($request, self::ROUTE);
        if (!$id) {
            $transactions = $this->transactionService->getAllTransactions();
        } else if (is_array($id)) {
            $transactions = $this->transactionService->getTransactionById($id['transaction']);
        } else {
            $transactions = $this->transactionService->getUsersTransactions($id);
        }

        $body = json_encode($transactions);

        $response = $response
            ->withStatus($transactions->code)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strval(strlen($body)));

        if($headRequest) {
            return $response;
        }

        $response->getBody()->write($body);
        return $response;
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
        return $this->defaultOptions($request, $response);
    }

    /**
     * @inheritDoc
     */
    public function patch(ServerRequest $request, Response $response): Response
    {
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('HTTP/1.0', '404 Not Found');
        $returnResponse->getBody()->write('');
        return $returnResponse;
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
                ->withStatus($auth->code)
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Length', strval(strlen($body)));

            $response->getBody()->write($body);

            return $response;
        }

        $body = $this->parsePost($request);
        $res = $this->transactionService->addNewTransaction($body);

        $body = json_encode($res);
        $response = $response
            ->withStatus($res->code)
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
        $returnResponse = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('HTTP/1.0', '404 Not Found');
        $returnResponse->getBody()->write('');
        return $returnResponse;
    }
}