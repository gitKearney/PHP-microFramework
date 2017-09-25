<?php

namespace Main\Models;

use Main\Models\dbConnectionTrait;

abstract class BaseModel
{
    use dbConnectionTrait;

    /**
     * This is a generic insert method that assumes the INSERT statement
     * has been built, and the the values are match properly in the array
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
        # INSERT INTO t ('key') VALUES (:val)

        # The values array would be defined as: [':val' => 'val']
        $pdo = null;

        try {
            $pdo = $this->getPdoConnection();
        } catch( \Exception $e) {
            throw $e;
        }

        try {
            $ps = $pdo->prepare($query);
            $result = $ps->execute($values);

            if ($result === false) {
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
        $this->debugLogger->enableLogging();

        $this->debugLogger->setMessage('running select')->write();

        try {
            $pdo = $this->getPdoConnection();

            $statement = $pdo->prepare($query);

            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                $this->debugLogger->setMessage('query result is false')->write();
            }

        } catch( \Exception $e) {
            throw $e;
        }

        $results = [];

        while($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * @param string $logFileName default null
     * @return $this
     */
    public function setDebugLogger($logFileName = null)
    {
        if (is_null($logFileName)) {
            $this->debugLogger = new DebugLogger;
        } else {
            $this->debugLogger = new DebugLogger($logFileName);
        }

        return $this;
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
                $this->debugLogger->setMessage('failed to update')->write();
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
                $this->debugLogger->setMessage('failed to delete')->write();
                throw new \Exception('failed to delete');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }
}
