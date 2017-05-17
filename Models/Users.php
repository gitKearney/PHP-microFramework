<?php

namespace Models;

class Users extends BaseModel
{
    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $birthday;

    /**
     * @var string
     */
    protected $id;

    /**
     * Users constructor.
     */
    public function __construct()
    {
        $this->setDebugLogger();
        return $this;
    }

    /**
     * @param string $day
     * @return $this
     */
    public function setBirthday($day)
    {
        $this->birthday = $day;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setFirstName($name)
    {
        $this->firstName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setLastName($name)
    {
        $this->lastName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setUserId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return array
     */
    public function findUserById($id)
    {
        $this->debugLogger->enableLogging();

        $query = 'SELECT first_name, last_name, birthday FROM users'
            .' WHERE user_id = :user_id';
        $values[':user_id'] = $id;

        try {
            $pdo = $this->getPdoConnection(); 
            
            $pdoStatement = $pdo->prepare($query);
            $queryResult = $pdoStatement->execute($values);

            if ($queryResult === false) {
                $this->debugLogger->setMessage('select failed')
                    ->logVariable($pdo->errorInfo())
                    ->write();

                throw new \Exception('select failed');
            }
        } catch (\Exception $e) {
            return ['status' => 'success'];
        }

        # we don't care if multiple rows were returned, this method will
        # only return 1 record
        $result = $pdoStatement->fetch(\PDO::FETCH_ASSOC);

        $this->debugLogger->setMessage('result')
            ->logVariable($result)
            ->write();

        $res =  [
            'firstName' => '',
            'lastName'  => '',
            'birthday'  => '',
        ];

        if (isset($result['first_name'])) {
            $res['firstName'] = $result['first_name'];
        }

        if (isset($result['last_name'])) {
            $res['lastName'] = $result['last_name'];
        }

        if (isset($result['birthday'])) {
            $res['birthday'] = $result['birthday'];
        }

        return $res;
    }

    /**
     * @param string $id
     * @return array
     */
    public function deleteUserById($id)
    {
        $this->debugLogger->enableLogging();


        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $values = [':user_id' => $id];

        $this->debugLogger->setMessage('query: ')->logVariable($query)->write();
        $this->debugLogger->setMessage('values: ')->logVariable($values)->write();

        try {
            $this->runQuery($query, $values);
        } catch (\Exception $e) {
            return ['result' => 'success'];
        }

        return [
            'result' => 'success',
            'id'     => $id,
        ];
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function update(array $values = [])
    {
        $this->debugLogger->enableLogging();

        $where = '';
        $set = '';
        $updateValues = [];
        foreach ($values as $key => $value) {
            # we need to build the query string
            # if the key is the id, then, set the where clause, otherwise
            # add the key to the where clause
            if ($key == 'id') {
                $updateValues[':user_id'] = $value;
                $where .= 'user_id = :user_id';
                continue;
            }

            # remove the spaces from the value, we don't ever want to allow spaces
            $value = trim($value);
            if ( strlen($value) == 0) {
                # catch someone who just puts in empty spaces for the user
                # information, and ignore this field
                continue;
            }

            $set .= $key.' = :'.$key.',';
            $updateValues[':'.$key] = $value;
        }

        # remove the trailing comma from the set string
        $set = trim($set, ',');

        if (strlen($set) == 0) {
            # the user didn't pass anything in, send a success
            return ['status' => 'success'];
        }

        # update the updated_at column
        $set .= ', updated_at = :updated_at';
        $updateValues[':updated_at'] = date('Y-m-d H:i:s');

        $query = 'UPDATE users SET '.$set.' WHERE '.$where;

        $this->debugLogger->setMessage('UPDATE QUERY: ')->logVariable($query)->write();
        $this->debugLogger->setMessage('UPDATE ARRAY: ')->logVariable($updateValues)->write();

        try {
            $this->runQuery($query, $updateValues);
        } catch (\Exception $e) {
            return ['status' => 'success'];
        }

        return [
            'status'     => 'success',
            'id'         => $values['id'],
            'first_name' => $values['first_name'],
            'last_name'  => $values['last_name'],
            'birthday'   => $values['birthday'],
        ];
    }

    /**
     * @param $query
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function runQuery($query, array $params)
    {
        try {
            $pdo = $this->getPdoConnection();

            $statement = $pdo->prepare($query);

            $resultSet = $statement->execute($params);
            
            if ($resultSet === false) {
                $this->debugLogger
                    ->setMessage('query failed: ')
                    ->logVariable($pdo->errorInfo())
                    ->write();
            }

        } catch( \Exception $e) {
            $this->debugLogger
                ->setMessage('failed getting PDO connection')
                ->logVariable($query)
                ->write();

            throw $e;
        }

        return true;
    }

    public function insert($query, array $values = [])
    {
        $this->debugLogger->setMessage('query: ')->logVariable($query)->write();

        $this->debugLogger->setMessage('values: ')->logVariable($values)->write();

        try {
            $pdo = $this->getPdoConnection();
        } catch( \Exception $e) {
            $this->debugLogger->enableLogging();
            $this->debugLogger
                ->setMessage('failed getting PDO connection in insert')
                ->logVariable('')
                ->write();

            throw $e;
        }

        try {
            $ps = $pdo->prepare($query);
            $result = $ps->execute($values);

            if ($result === false) {
                $this->debugLogger
                    ->setMessage('insert result was false ')
                    ->logVariable($pdo->errorInfo())
                    ->write();
            }
        } catch (\Exception $e) {
            $this->debugLogger->enableLogging();
            $this->debugLogger
                ->setMessage('failed inserting PDO error: '.$ps->errorCode())
                ->logVariable($ps->errorInfo())
                ->write();

            throw $e;
        }

        return [
            'id'         => $this->id,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'birthday'   => $this->birthday,
        ];
    }

    public function select($query, array $values = [])
    {
        $pdo = $this->getPdoConnection();
    }

    public function addNewUser()
    {
        $this->debugLogger->enableLogging();

        $query = 'INSERT INTO users (user_id, first_name, last_name, birthday, created_at)'
            .' VALUES (:user_id, :first_name, :last_name, :birthday, :created_at)';

        $values = [
            ':user_id'    => $this->id,
            ':first_name' => $this->firstName,
            ':last_name'  => $this->lastName,
            ':birthday'   => $this->birthday,
            ':created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            return $this->insert($query, $values);
        } catch(\Exception $e) {
            $this->debugLogger->setMessage('got an error')->logVariable('')->write();
            throw $e;
        }
    }
}
