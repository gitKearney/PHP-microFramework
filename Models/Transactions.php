<?php
namespace Main\Models;

use Exception;

class Transactions extends BaseModel
{
    public function __construct() {
        $this->setReadConnectionId('product_database');
        $this->setWriteConnectionId('product_database');
    }

    /**
     * @param array $formData
     * @throws Exception
     */
    public function addNewTransaction(array $formData)
    {
        $query = $this->buildInsertQuery($formData, 'transactions');
        $this->insert($query->sql, $query->params);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAllTransactions(): array
    {
        $query = <<<'QUERY'
            SELECT trans.transaction_id, trans.user_id, tp.product_id,tp.product_price,trans.created_at
            FROM transactions AS trans
                INNER JOIN transaction_products tp on trans.transaction_id = tp.transaction_id
            GROUP BY trans.transaction_id,trans.user_id,tp.product_id,tp.product_price,trans.created_at
QUERY;
        $params = [];

        $result = $this->select($query, $params);

        # group the transactions into a single array
        $records = [];
        foreach($result as $index => $row) {
            $trans_id = $row['transaction_id'];
            if (array_key_exists($trans_id, $records) === false) {
                $records[$trans_id] = [];
            }

            $records[$trans_id][] = [
                'product_id' => $row['product_id'],
                'price' => $row['product_price'],
                'order_date' => $row['created_at'],
                'id' => $row['user_id'],
            ];
        }

        return $records;
    }

    /**
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function getUsersTransactions($userId): array
    {
        $query = <<<'QUERY'
            SELECT trans.transaction_id, trans.user_id, tp.product_id,tp.product_price,trans.created_at
            FROM transactions AS trans
                INNER JOIN transaction_products tp on trans.transaction_id = tp.transaction_id
            WHERE trans.user_id = :user_id
            GROUP BY trans.transaction_id,trans.user_id,tp.product_id,tp.product_price,trans.created_at
QUERY;
        $params = [':user_id' => $userId];

        return $this->getTransactionBy($query, $params);
    }

    /**
     * @param string $transactionId
     * @return array
     * @throws Exception
     */
    public function getTransactionById(string $transactionId)
    {
        $query = <<<'QUERY'
SELECT trans.transaction_id, trans.user_id, tp.product_id,tp.product_price,trans.created_at
FROM transactions AS trans
INNER JOIN transaction_products tp on trans.transaction_id = tp.transaction_id
WHERE trans.transaction_id = :transaction_id
GROUP BY trans.transaction_id,trans.user_id,tp.product_id,tp.product_price,trans.created_at 
QUERY;

        $params = [':transaction_id' => $transactionId];
        return $this->getTransactionBy($query, $params);
    }

    /**
     * @param $query
     * @param $params
     * @return array
     * @throws Exception
     */
    private function getTransactionBy($query, $params): array
    {
        $result = $this->select($query, $params);

        # group the transactions into a single array
        $records = [];

        # if the result is not an array or arrays turn it into one
        if (!isset($result[0])) {
            $_r = [0 => $result];
            $result = $_r;
        }

        foreach($result as $index => $row) {
            $trans_id = $row['transaction_id'];
            if (array_key_exists($trans_id, $records) === false) {
                $records[$trans_id] = [];
            }

            $records[$trans_id][] = [
                'product_id' => $row['product_id'],
                'price' => $row['product_price'],
                'order_date' => $row['created_at'],
                'id' => $row['user_id'],
            ];
        }

        return $records;
    }
}