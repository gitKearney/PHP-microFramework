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
     * @return array
     * @throws Exception
     */
    public function findCartById(string $userId): array
    {
        $query = <<<'QUERY'
            SELECT uc.product_id, p.title, p.price FROM user_cart AS uc
            INNER JOIN products as p ON uc.product_id = p.product_id
            WHERE user_id = :user_id
QUERY;
        $params = [':user_id' => $userId];

        return $this->select($query, $params);
    }

    /**
     * @param array $userProduct
     * @return void
     * @throws Exception
     */
    public function deleteItem(array $userProduct): void
    {
        $query = <<<'QUERY'
            DELETE FROM user_cart
            WHERE user_id = :user_id AND product_id = :product_id
QUERY;

        $this->delete($query, $userProduct);
    }

    /**
     * @param string $userId
     * @throws Exception
     * @return void
     */
    public function clearCart(string $userId): void
    {
        $query = 'DELETE FROM user_cart WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        $this->delete($query, $params);
    }
}