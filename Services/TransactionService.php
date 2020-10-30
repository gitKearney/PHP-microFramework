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
    private $trans;

    /**
     * @var UuidService
     */
    private $uuidService;

    /**
     * @var TransactionProducts
     */
    private $transProds;

    public function __construct(
        UuidService $uuidService,
        Transactions $transactions,
    TransactionProducts $transactionProducts)
    {
        $this->trans = $transactions;
        $this->uuidService = $uuidService;
        $this->transProds  = $transactionProducts;
    }

    public function getAllTransactions()
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

    public function addNewTransaction(array $requestBody)
    {
        $response = $this->createResponseObject();

        // TODO: create a new transaction UUID
        try {
            $transId = $this->uuidService->generateUuid()->getUuid();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->code = 500;

            return $response;
        }

        $transaction = [
            'transaction_id' => $transId,
            'user_id' => $requestBody['user_id'],
        ];

        // TODO: insert record into the transaction table
        $transProducts = [];
        foreach($requestBody['products'] as $index => $product) {
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
}