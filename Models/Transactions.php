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
        $query = 'SELECT * FROM transactions GROUP BY transaction_id';
        $params = [];

        $result = $this->select($query, $params);
        return $result;
    }
}