<?php

namespace Main\Models;

use Exception;
use PDO;
use stdClass;

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
     * @return void
     * @throws Exception
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
            $pdo = $this->getPdoConnection('write');
        } catch( Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');
            throw new Exception('Error Establishing Connection to Database');
        }

        try {
            $ps = $pdo->prepare($query);
            $success = $ps->execute($values);
        } catch (Exception $e) {
            logVar($e->getCode(), 'EXCEPTION INSERTING: '.$e->getMessage(), 'critical');
            throw new Exception('Error Occurred Inserting Record');
        }

        if ($success === false) {
            logVar('INSERT FAILED', '', 'critical');
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function select($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection('read');
        } catch( Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');
            throw new Exception('Error Establishing Connection to Database');
        }

        try {
            $statement = $pdo->prepare($query);
            $resultSet = $statement->execute($params);
        } catch( Exception $e) {
            logVar($e->getCode(), 'EXCEPTION SELECTING: '.$e->getMessage(), 'critical');
            throw new Exception('Error Occurred While Finding Records');
        }

        if ($resultSet === false) {
            logVar('SELECT FAILED', '', 'critical');
            throw new Exception('Error Finding Records');
        }

        $this->results = [];

        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->results[] = $row;
        }

        if (count($this->results) == 1) {
            $this->results = $this->results[0];
        }

        return $this->results;
    }

    /**
     * @param string $query
     * @param array $params
     * @return void
     */
    public function update($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection('write');
        } catch( Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');
            return;
        }

        try {
            $statement = $pdo->prepare($query);
            $success = $statement->execute($params);
        } catch (Exception $e) {
            logVar($e->getCode(), 'EXCEPTION UPDATING: '.$e->getMessage(), 'critical');
            return;
        }

        if ($success === false) {
            logVar('UPDATE FAILED', '', 'critical');
            return;
        }
    }

    /**
     * @param string $query
     * @param array $params
     * @return void
     */
    public function delete($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection('write');
        } catch( Exception $e) {
            logVar($e->getMessage(), 'Failed to establish connection to database', 'emergency');
            throw new Exception('Error Establishing Connection to Database');
        }

        try {
            $statement = $pdo->prepare($query);
            $resultSet = $statement->execute($params);
        } catch (Exception $e) {
            logVar($e->getCode(), 'EXCEPTION DELETING: '.$e->getMessage(), 'critical');
            throw new Exception('Error removing record');
        }

        if ($resultSet === false) {
            logVar('DELETE FAILED', '', 'critical');
        }
    }

    /**
     * @param array $values
     * @param string $tableName
     * @return stdClass
     * @throws Exception
     */
    public function buildInsertQuery(array $values, $tableName)
    {
        $sqlQuery = new stdClass;
        $sqlQuery->sql = '';
        $sqlQuery->params = [];

        if (count($values) === 0) {
            throw new Exception('Empty Body');
        }
        # add a create_at field to to the insert, update the value if it exists
        $values['created_at'] = date('Y-m-d H:i:s');

        # this will store the column names in the values list
        $valueQuery = '';

        # this will store the array key names that correspond to the column name
        $columnQuery = '';

        foreach ($values as $columnName => $value) {
            # create PDO column name by prepending a colon
            $pdoColumn = ':'.$columnName;

            # add the key to an array and set its value
            $sqlQuery->params[$pdoColumn] = $value;

            # build the query string
            $valueQuery  .= $pdoColumn.',';
            $columnQuery .= $columnName.',';
        }

        # remove the trailing comma from the set string
        $valueQuery = '('.trim($valueQuery, ',') . ')';
        $columnQuery = '('.trim($columnQuery, ','). ')';

        $sqlQuery->sql = 'INSERT INTO '.$tableName.' '.$columnQuery.' VALUES '.$valueQuery;

        return $sqlQuery;
    }

    /**
     * @param array $params
     * @param string $tableName
     * @return stdClass
     * @throws Exception
     */
    public function buildSearchString(array $params, $tableName)
    {
        $sqlQuery = new stdClass;
        $sqlQuery->sql = '';
        $sqlQuery->params = [];

        if (empty($params)) {
            throw new Exception('Empty Search Params');
        }

        $where = '';

        foreach ($params as $columnName => $columnValue) {
            $pdoColumn = ':'.$columnName;
            $sqlQuery->params[$pdoColumn] = $columnValue;

            $where .= $columnName.' = '.$pdoColumn.' AND ';
        }

        # strip the trailing ANDs from the where clause
        $where = trim($where, 'AND ');

        $sqlQuery->sql = 'SELECT user_id AS id, first_name, last_name, email,'
            .' birthday, roles AS role, active'
            .' FROM '.$tableName.' WHERE '.$where;

        return $sqlQuery;
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
}
