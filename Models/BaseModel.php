<?php

namespace Main\Models;

use Main\Models\dbConnectionTrait;

/**
 * Class BaseModel
 * @package Main\Models
 */
abstract class BaseModel
{
    use dbConnectionTrait;

    /**
     * @var array $results - store records pulled from selects
     */
    protected $results;

    /**
     * @var string
     */
    protected $readConnectionId;

    /**
     * @var
     */
    protected $writeConnectionId;

    /**
     * This is a generic insert method that assumes the INSERT statement
     * has been built, and the the values match properly in the array
     *
     * @param string $query
     * @param array $values
     * @return \stdClass
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
        $result = $this->setResponse();

        logVar($query, 'QUERY = ');
        logVar($values, 'PARAMS = ');

        try {
            $pdo = $this->getPdoConnection('write');
        } catch( \Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');
            $result->message = 'Error Establishing Connection to Database';
            return $result;
        }

        try {
            $ps = $pdo->prepare($query);
            $resultSet = $ps->execute($values);

            if ($resultSet === false) {
                logVar('INSERT FAILED', '', 'critical');
                $result->message = 'failed to insert record';
                return $result;
            }

        } catch (\Exception $e) {
            logVar($e->getCode(), 'EXCEPTION INSERTING: '.$e->getMessage(), 'critical');

            $result->message = 'Error Occurred Inserting Record';
            return $result;
        }

        $result->success = true;
        $result->message = 'success';

        return $result;
    }

    /**
     * @param $query
     * @param array $params
     * @return \stdClass
     */
    public function select($query, array $params)
    {
        $result = $this->setResponse();

        try {
            $pdo = $this->getPdoConnection('read');
        } catch( \Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');

            $result->message = 'Error Establishing Connection to Database';
            return $result;
        }

        try {
            logVar($query, 'query = ');
            logVar($params, 'params = ');

            $statement = $pdo->prepare($query);

            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('SELECT FAILED', '', 'critical');

                $result->message = 'Error Finding Records';
                return $result;
            }
        } catch( \Exception $e) {
            logVar($e->getCode(), 'EXCEPTION SELECTING: '.$e->getMessage(), 'critical');

            $result->message = 'Error Occurred While Finding Records';
            return $result;
        }

        $this->results = [];

        while($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->results[] = $row;
        }

        if (count($this->results) === 0) {
            $result->success = true;
            $result->message = 'No Results';
            return $result;
        }

        if (count($this->results) == 1) {
            $result->results = $this->results[0];
        } else {
            $result->results = $this->results;
        }

        $result->success = true;
        $result->message = 'success';

        return $result;
    }

    /**
     * @param string $query
     * @param array $params
     * @return \stdClass
     */
    public function update($query, array $params)
    {
        $result = $this->setResponse();

        try {
            $pdo = $this->getPdoConnection('write');
        } catch( \Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');

            $result->message = 'Error Establishing Connection to Database';
            return $result;
        }

        try {
            $statement = $pdo->prepare($query);
            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('UPDATE FAILED', '', 'critical');

                $result->message = 'Failed to Update User';
                return $result;
            }
        } catch (\Exception $e) {
            logVar($e->getCode(), 'EXCEPTION UPDATING: '.$e->getMessage(), 'critical');

            $result->message = 'Error Occurred Updating User';
            return $result;
        }

        $result->success = true;
        $result->message = 'success';

        return $result;
    }

    /**
     * @param string $query
     * @param array $params
     * @return \stdClass
     */
    public function delete($query, array $params)
    {
        $result = $this->setResponse();

        try {
            $pdo = $this->getPdoConnection('write');
        } catch( \Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');

            $result->message = 'Error Establishing Connection to Database';
            return $result;
        }

        try {
            $statement = $pdo->prepare($query);

            $resultSet = $statement->execute($params);

            if ($resultSet === false) {
                logVar('DELETE FAILED', '', 'critical');

                $result->message = 'Failed to Remove User';
                return $result;
            }
        } catch (\Exception $e) {
            logVar($e->getCode(), 'EXCEPTION DELETING: '.$e->getMessage(), 'critical');

            $result->message = 'Failed to Remove User';
            return $result;
        }

        $result->success = true;
        $result->message = 'success';

        return $result;
    }

    /**
     * Returns the array which stores results from selects
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * allows for the array which stores results to be set to something
     * different, or pre-populated with some data
     * @param array $values
     * @return $this
     */
    public function setResults(array $values)
    {
        $this->results = $values;
        return $this;
    }

    /**
     * @param string $configName
     * @return $this
     */
    public function setReadConnectionId($configName)
    {
        $this->readConnectionId = $configName;
        return $this;
    }

    /**
     * @param string $configName
     * @return $this
     */
    public function setWriteConnectionId($configName)
    {
        $this->writeConnectionId = $configName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReadConnectionId()
    {
        return $this->readConnectionId;
    }

    /**
     * @return string
     */
    public function getWriteConnectionId()
    {
        return $this->writeConnectionId;
    }

    /**
     * @return \stdClass
     */
    public function setResponse()
    {
        $response = new \stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        return $response;
    }
}
