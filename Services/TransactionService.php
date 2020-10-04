<?php
namespace Main\Services;

use Exception;
use stdClass;
use Main\Models\Transactions;

class TransactionService extends BaseService
{
    /**
     * @var Transactions
     */
    private $transactions;

    public function __construct(Transactions $transactions)
    {
        $this->transactions = $transactions;
    }

    public function getAllTransactions()
    {
        $response = $this->createResponseObject();

        try {
            $trans = $this->transactions->getAllTransactions();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->code = $e->getCode();

            return $response;
        }

        $response = $this->normalizeResponse($trans);
        return $response;
    }
}