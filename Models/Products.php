<?php

namespace Main\Models;

class Products extends BaseModel
{
    private $productId;
    private $title;
    private $price;
    private $quantity;
    private $createdAt;
    private $updatedAt;

    /**
     * Users constructor.
     */
    public function __construct()
    {
        // TODO: change product_database to name of key with your database 
        // credentials in config/credentials.php
        // In this example, the app will read from and write to the same server,
        // so, we set the connection IDs to be the same
        $this->readConnectionId  = 'product_database';
        $this->writeConnectionId = 'product_database';
    }

    /**
     * @param string $guid
     * @return $this
     */
    public function setProductId($guid)
    {
        $this->productId = $guid;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
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
     * @return bool
     */
    public function setProductInfo($httpBody)
    {
        # TODO: validate that the info is good and in here
        $this->setTitle($httpBody['title']);
        $this->setPrice($httpBody['price']);
        $this->setQuantity($httpBody['quantity']);
        $this->setProductId($httpBody['id']);

        return true;
    }

    /**
     * @param string $productId
     * @return \stdClass
     */
    public function findProductById($productId)
    {
        $query = 'SELECT product_id as id, title, price, quantity,'
            .' created_at, updated_at'
            .' FROM products WHERE product_id = :product LIMIT 1';
        $params = [':product_id' => $productId];

        $result = $this->select($query, $params);

        return $result;
    }

    /**
     * Returns all users form the database
     * @return \stdClass
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
     * @return \stdClass
     */
    public function deleteProductById($productId)
    {
        $query = 'DELETE FROM products WHERE product_id = :product_id';
        $params = [':product_id' => $productId];
        return $result = $this->delete($query, $params);
    }

    /**
     * @param array $values
     * @return \stdClass
     */
    public function updateProduct(array $values)
    {
        $result = new \stdClass();
        $result->success = false;
        $result->message = 'Nothing to Update';
        $result->results = [];

        $where = '';
        $set = '';
        $updateValues = [];
        foreach ($values as $key => $value) {
            # we need to build the query string

            # remove the spaces from the value, we don't ever want to allow spaces
            $value = trim($value);
            if ( strlen($value) == 0) {
                # catch someone who just puts in empty spaces for the user
                # information, and ignore this field
                continue;
            }

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
            $result->success =  false;
            $result->message = 'No Products Found';

            return $result;
        }

        $query = 'UPDATE products SET '.$set.' WHERE '.$where;

        return $this->update($query, $updateValues);
    }

    /**
     * @desc inserts a user into the database
     * @return \stdClass
     */
    public function addNewProduct()
    {
        $query = 'INSERT INTO products (product_id, title, price, quantity, '
            .'created_at) '
            .'VALUES (:product_id, :title, :price, :quantity, :created_at)';

        $values = [
            ':product_id' => $this->productId,
            ':title'      => $this->title,
            ':quantity'   => $this->quantity,
            ':price'      => $this->price,
            ':created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($query, $values);
    }

    /**
     * @param $title
     * @return \stdClass
     * @throws \Exception
     */
    public function findProductByTitle($title)
    {
        $query  = 'SELECT * FROM products WHERE title LIKE  "%:title%"';
        $params = [':title' => $title,];

        $result = $this->select($query, $params);
        return $result;
    }
}
