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

    public function addNewTransaction($formData)
    {
        $query = $this->buildInsertQuery($formData, 'transactions');
        $this->insert($query->sql, $query->params);
    }
}