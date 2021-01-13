<?php

namespace Main\Models;

use Exception;
use stdClass;

class Products extends BaseModel
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
        $this->setReadConnectionId('product_database');
        $this->setWriteConnectionId('product_database');
    }

    /**
     * @desc pull info from the request body
     *
     * For this default database, the user table only contains 4 fields
     * since the ID cannot be changed, that leaves only the first and last name
     * as changeable as well as the birthday column.
     *
     * Pull the params from the HTTP body and assign them to the model's data
     * @param array
     * @return array
     */
    public function setProductInfo($httpBody): array
    {
        $postValues = [];

        $postValues['product_id']  = $httpBody['id'] ?? null;
        $postValues['title']       = $httpBody['title'] ?? null;
        $postValues['price']       = $httpBody['price'] ?? null;
        $postValues['quantity']    = $httpBody['quantity'] ?? null;

        return $postValues;
    }

    /**
     * @param string $productId
     * @return array
     * @throws Exception
     */
    public function findProductById(string $productId)
    {
        $query = 'SELECT product_id as id, title, price, quantity,'
            .' created_at, updated_at'
            .' FROM products WHERE product_id = :product_id LIMIT 1';
        $params = [':product_id' => $productId];

        $result = $this->select($query, $params);

        return $result;
    }

    /**
     * @param $params
     * @return stdClass
     * @throws Exception
     */
    public function getProductByParams($params): stdClass
    {
        $query = $this->buildSearchString($params, 'products');
        $sql = 'SELECT product_id AS id, title, price, quantity, created_at'
            .' FROM products WHERE '.$query->sql;
        $query->sql = $sql;
        return $query;
    }

    /**
     * Returns all users form the database
     * @return array
     * @throws Exception
     */
    public function getAllProducts()
    {
        $query = 'SELECT product_id as id, title, price, quantity, created_at FROM products';
        $params = [];

        $result = $this->select($query, $params);
        return $result;
    }

    /**
     * @param string $productId
     * @return stdClass
     * @throws Exception
     */
    public function deleteProductById($productId): stdClass
    {
        $query = 'DELETE FROM products WHERE product_id = :product_id';
        $params = [':product_id' => $productId];


        $this->delete($query, $params);
    }

    /**
     * @param array $values
     * @return void
     */
    public function updateProduct(array $values)
    {
        $where = '';
        $set = '';
        $updateValues = [];
        foreach ($values as $key => $value) {
            # we need to build the query string

            # remove the spaces from the value, we don't ever want to allow spaces
            # TODO: we probably should allow them to "clear" out info
            #$value = trim($value);
            #if ( strlen($value) == 0) {
            #    # catch someone who just puts in empty spaces for the user
            #    # information, and ignore this field
            #    continue;
            #}

            # if the key is the id, then, set the where clause, otherwise
            # add the key to the where clause
            if ($key == 'id') {
                $updateValues[':product_id'] = $value;
                $where .= 'product_id = :product_id';
                continue;
            }

            # you can add extra protection here to make sure that the key is
            # on the approved list. But, since, we're using prepared statements
            # any junk the user enters will just return an error
            $set .= $key.' = :'.$key.',';
            $updateValues[':'.$key] = $value;
        }

        # remove the trailing comma from the set string
        $set = trim($set, ',');

        if (strlen($set) == 0) {
            # the user didn't pass anything in, send a success
            logVar('', 'No User Passed In Values');
            return;
        }

        $query = 'UPDATE products SET '.$set.' WHERE '.$where;

        $this->update($query, $updateValues);
    }

    /**
     * @desc inserts a user into the database
     * @param $formData
     * @return void
     * @throws Exception
     */
    public function addNewProduct($formData)
    {
        $query = $this->buildInsertQuery($formData, 'products');
        $this->insert($query->sql, $query->params);
    }

    /**
     * @param $title
     * @return array
     * @throws Exception
     */
    public function findProductByTitle($title)
    {
        $query  = 'SELECT * FROM products WHERE title LIKE  "%:title%"';
        $params = [':title' => $title,];

        $products = $this->select($query, $params);
        return $products;
    }
}
