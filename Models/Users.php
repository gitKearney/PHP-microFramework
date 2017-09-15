<?php

namespace Main\Models;

class Users extends BaseModel
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
     * @param string $userId
     * @return array
     */
    public function findUserById($userId)
    {
        $query = 'SELECT user_id as id, first_name, last_name, birthday'
            .' FROM users WHERE user_id = :user_id LIMIT 1';
        $params = [':user_id' => $userId];

        try {
            $result = $this->select($query, $params);
        } catch(\Exception $e) {
            return ['result' => 'error'];
        }

        return $result;
    }
    
    /**
     * Returns all users form the database
     * @return array
     */
    public function getAllUsers()
    {

        $query = 'SELECT user_id as id, first_name, last_name, birthday'
            .' FROM users';
        $params = [];
        
        try {
            $result = $this->select($query, $params);
        } catch(\Exception $e) {
            return ['result' => 'error'];
        }
        
        return $result;
    }

    /**
     * @param string $userId
     * @return array
     */
    public function deleteUserById($userId)
    {
        $this->debugLogger->enableLogging();
        
        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        
        try {
            $this->delete($query, $params);
        } catch(\Exception $e) {
            return ['result' => 'error'];
        }

        return ['result' => 'success'];
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function updateUser(array $values)
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
                $where .= 'user_id = :id';
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
            return ['result' => 'no user found'];
        }

        $query = 'UPDATE users SET '.$set.' WHERE '.$where;

        $this->debugLogger->setMessage('UPDATE QUERY: ')->logVariable($query)->write();
        $this->debugLogger->setMessage('UPDATE ARRAY: ')->logVariable($updateValues)->write();

        try {
            $this->update($query, $updateValues);
        } catch (\Exception $e) {
            return ['result' => 'error'];
        }

        return ['result' => 'success'];
    }

    /**
     * @desc inserts a user into the database
     * @return array
     * @throws \Exception
     */
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
            $res = $this->insert($query, $values);
        } catch(\Exception $e) {
            $this->debugLogger->setMessage('got an error')->logVariable('')->write();
            return ['result' => 'error'];
        }

        return ['result' => 'success'];
    }

}
