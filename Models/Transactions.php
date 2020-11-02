<?php
namespace Main\Models;

use Exception;
use stdClass;

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
    public function getAllTransactions()
    {
        $query = 'SELECT trans.transaction_id,trans.user_id,tp.product_id
            ,tp.product_price,trans.created_at
             FROM transactions AS trans
             INNER JOIN transaction_products tp on trans.transaction_id = tp.transaction_id
             GROUP BY trans.transaction_id,trans.user_id,tp.product_id
            ,tp.product_price,trans.created_at';
        $params = [];

        $result = $this->select($query, $params);

        # group the transactions into a single array
        $records = [];
        foreach($result as $index => $row) {
            $trans_id = $row['transaction_id'];
            if (array_key_exists($trans_id, $records)) {
                $records[$trans_id][] = [
                    'product_id' => $row['product_id'],
                    'price' => $row['product_price'],
                    'order_date' => $row['created_at'],
                    'id' => $row['user_id'],
                ];

                continue;
            }

            $records[$trans_id] = [];
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