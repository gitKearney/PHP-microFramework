<?php


namespace Main\Models;


use Exception;

class TransactionProducts extends BaseModel
{
    public function __construct() {
        $this->setReadConnectionId('read_database');
        $this->setWriteConnectionId('write_database');
    }

    /**
     * @param array $values
     * @throws Exception
     * @return void
     */
    public function addTransactionProduct(array $values): void
    {
        $query = $this->buildInsertQuery($values, 'transaction_products');
        $this->insert($query->sql, $query->params);
    }
}