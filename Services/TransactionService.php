<?php
namespace Main\Services;

use Exception;
use stdClass;

use Main\Models\TransactionProducts;
use Main\Models\Transactions;

class TransactionService extends BaseService
{
    /**
     * @var Transactions
     */
    private Transactions $trans;

    /**
     * @var UuidService
     */
    private UuidService $uuidService;

    /**
     * @var TransactionProducts
     */
    private TransactionProducts $transProds;

    public function __construct(
        UuidService $uuidService,
        Transactions $transactions,
    TransactionProducts $transactionProducts)
    {
        $this->trans = $transactions;
        $this->uuidService = $uuidService;
        $this->transProds  = $transactionProducts;
    }

    /**
     * @return stdClass
     */
    public function getAllTransactions(): stdClass
    {
        $response = $this->createResponseObject();

        try {
            $trans = $this->trans->getAllTransactions();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->code = $e->getCode();

            return $response;
        }

        $response = $this->normalizeResponse($trans);
        return $response;
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function getUsersTransactions(string $userId): stdClass
    {
        $response = $this->createResponseObject();
        try {
            $transactions = $this->trans->getUsersTransactions($userId);
        } catch(Exception $e) {
            $response->message = $e->getMessage();
            $response->code = $e->getCode();
            return $response;
        }

       return $this->normalizeResponse($transactions);
    }

    public function addNewTransaction(array $requestBody): stdClass
    {
        $response = $this->createResponseObject();

        try {
            $transId = $this->uuidService->generateUuid()->getUuid();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->code = 500;

            return $response;
        }

        $values = $this->getTransactionInfo($requestBody);
        if ($values['user_id'] === null || $values['products'] === null) {
            $response->code = 406;
            return $response;
        }

        $transaction = [
            'transaction_id' => $transId,
            'user_id' => $values['user_id'],
        ];

        $transProducts = [];
        foreach($values['products'] as $index => $product) {
            $transProducts[] = [
                'transaction_id' => $transId,
                'product_id' => $product['id'],
                'product_price' => $product['price'],
            ];
        }

        try {
            $this->trans->addNewTransaction($transaction);
        } catch(Exception $e){
            $response->message = $e->getMessage();
            $response->code = 500;

            return $response;
        }

        try {
            foreach($transProducts as $index => $transProduct) {
                $this->transProds->addTransactionProduct($transProduct);
            }
        } catch(Exception $e) {
            $errMsg = 'Failed to insert trans product '
                .$transProducts[$index]['product_id'];

            logVar('', $errMsg, 'error');
            $response->message = $e->getMessage();
            $response->code = 500;

            return $response;
        }

        $response->success = true;
        $response->message = 'success';
        $response->results['id'] = $transId;

        return $response;
    }

    /**
     * @param string $transactionId
     * @return stdClass
     */
    public function getTransactionById(string $transactionId): stdClass
    {
        try {
            $result = $this->trans->getTransactionById($transactionId);
        } catch(Exception $e) {
            $result = $this->createResponseObject();
            $result->code = $e->getCode();
            $result->message = $e->getMessage();
            $result->success = false;
            return $result;
        }

        return $this->normalizeResponse($result);
    }

    /**
     * @param array $body
     * @return array
     */
    public function getTransactionInfo(array $body): array
    {
        $params = [];

        $params['user_id'] = $body['id'] ?? null;
        $params['products'] = $body['products'] ?? null;

        return $params;
    }
}