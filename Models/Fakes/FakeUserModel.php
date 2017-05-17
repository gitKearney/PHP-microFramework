<?php

namespace Test;

/**
 * This is a test class that mimics calls to the database.
 * It's meant to be used for testing only.
 * The TestUserFactory should instantiate this class instead of the
 * actual Model/User class
 */
class FakeUserModel extends BaseModel
{
    protected $firstName;
    protected $lastName;
    protected $birthday;
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
        return [
            'id' => $id,
            'firstname' => 'Leroy',
            'lastname'  => 'Jenkins',
            'birthday'  => '04-18-1983',
        ];
    }

    /**
     * @param string $id
     * @return array
     */
    public function deleteUserById($id)
    {
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
                $updateValues[':'.$key] = $value;
                $where .= ' WHERE id = :id';
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

        $query = 'UPDATE users SET '.$set.' WHERE '.$where;

        $this->debugLogger->setMessage('UPDATE QUERY: ')->logVariable($query)->write();
        $this->debugLogger->setMessage('UPDATE ARRAY: ')->logVariable($updateValues)->write();

        try {
            $this->runQuery($query, $values);
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

            # TODO: run prepared statement
        } catch( \Exception $e) {
            $this->debugLogger->enableLogging();
            $this->debugLogger
                ->setMessage('failed getting PDO connection in insert')
                ->logVariable('')
                ->write();

            throw $e;
        }

        return true;
    }

    public function insert($query, array $values = [])
    {
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

        return [
            'id' => '12345678-1234-1234-1234-123456789abc',
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

        $query = 'INSERT INTO users (first_name, last_name, birthday)'
            .' VALUES (:first_name, :last_name, :birthday)';

        $values = [
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'birthday'   => $this->birthday,
        ];

        try {
            return $this->insert($query, $values);
        } catch(\Exception $e) {
            $this->debugLogger->setMessage('got an error')->logVariable('')->write();
            throw $e;
        }
    }

}
