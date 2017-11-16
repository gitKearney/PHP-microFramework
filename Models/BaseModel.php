<?php

namespace Main\Models;

use Main\Models\dbConnectionTrait;

abstract class BaseModel
{
    use dbConnectionTrait;

    /**
     * @var array $results - store records pulled from selects
     */
    protected $results;

    /**
     * This is a generic insert method that assumes the INSERT statement
     * has been built, and the the values match properly in the array
     *
     * @param $query
     * @param array $values
     * @param string - name of the id column
     * @return string
     * @throws \Exception
     */
    public function insert($query, array $values = [])
    {
        # NOTE: the key names of the "values" parameter MUST be in the
        # form of ':key_name'
        # The insert query statement MUST have ':key_name' somewhere matching
        # in its string

        # Example: The insert statement MUST be in this form:
        # INSERT INTO table ('key') VALUES (:val)

        # The values array would be defined as: [':val' => 'val']

        try {
            $pdo = $this->getPdoConnection();
        } catch( \Exception $e) {
            throw $e;
        }

        try {
            $ps = $pdo->prepare($query);
            $resultSet = $ps->execute($values);

            if ($resultSet === false) {
                throw new \Exception('error inserting');
            }

        } catch (\Exception $e) {
            throw $e;
        }
        
        return true;
    }


    /**
     * @param $query
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function select($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection();

            logVar($pdo, 'PDO Object ');

            $statement = $pdo->prepare($query);

            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('query result is false');
                return false;
            }

        } catch( \Exception $e) {
            throw $e;
        }

        $this->results = [];

        while($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->results[] = $row;
        }

        return true;
    }

    /**
     * @param string $query
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function update($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection();
            $statement = $pdo->prepare($query);
            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('failed to update');
                throw new \Exception('failed to update');
            }
        } catch (\Exception $e) {
            throw new \Exception('error '.$e->getMessage());
        }

        return true;
    }

    /**
     * @param string $query
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function delete($query, array $params)
    {
        try {
            $pdo       = $this->getPdoConnection();
            $statement = $pdo->prepare($query);
            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('failed to delete');
                throw new \Exception('failed to delete');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function initializeResults()
    {
        $this->results = [];
        return $this;
    }
    
    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    public function setResult(array $values)
    {
        return $this->results = $values;
    }

}
