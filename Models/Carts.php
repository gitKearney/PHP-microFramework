<?php
namespace Main\Models;

use Exception;
use stdClass;

class Carts extends BaseModel
{
    /**
     * @desc set the database connection
     */
    public function __construct()
    {
        # In this example, the app will read from and write to the same server,
        # so, we set the connection IDs to be the same.

        # You can also use the read/write key values. But, here, we're simulating
        # using a different database.
        $this->setReadConnectionId('read_database');
        $this->setWriteConnectionId('write_database');
    }

    /**
     * @desc parses the HTTP body and gets the values
     * @param array $body
     * @return array
     * @TODO move to service class
     */
    public function getCartValue(array $body)
    {
        $values = [];
        $values['product_id'] = $body['product_id'] ?? null;
        $values['user_id']    = $body['id'] ?? null;
        return $values;
    }

    /**
     * @desc adds a product to the cart associated with a user
     * @param array $userProduct
     * @throws Exception
     * @return void
     */
    public function addToCart(array $userProduct)
    {
        $query = $this->buildInsertQuery($userProduct, 'user_cart');
        $this->insert($query->sql, $query->params);
    }

    /**
     * @param string $userId
     * @throws Exception
     */
    public function findCartById(string $userId)
    {
        $query = 'SELECT product_id, created_at, updated_at FROM user_cart'.
            ' WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        $result = $this->select($query, $params);

        return $result;
    }
}