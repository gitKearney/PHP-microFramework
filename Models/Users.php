<?php

namespace Main\Models;

class Users extends BaseModel
{
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $birthday;
    protected $id;

    /**
     * Users constructor.
     */
    public function __construct()
    {
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

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword($password)
    {
        $this->upassword = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function getPassword()
    {
        return $this->upassword;
    }

    /**
     * @param string $userId
     * @return array
     */
    public function findUserById($userId)
    {
        $query = 'SELECT user_id as id, first_name, last_name, birthday, '
            .'email, created_at '
            .'FROM users WHERE user_id = :user_id LIMIT 1';
        $params = [':user_id' => $userId];

        try {
            $result = $this->select($query, $params);
        } catch(\Exception $e) {
            return ['result' => 'error'];
        }

        return true;
    }

    /**
     * Returns all users form the database
     * @return array
     */
    public function getAllUsers()
    {

        $query = 'SELECT user_id as id, first_name, last_name, birthday'
            .' email, created_at'
            .' FROM users';
        $params = [];

        try {
            $this->select($query, $params);
        } catch(\Exception $e) {
            return ['result' => 'error'];
        }

        return true;
    }

    /**
     * @param string $userId
     * @return array
     */
    public function deleteUserById($userId)
    {
        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        try {
            $this->delete($query, $params);
        } catch(\Exception $e) {
            $this->results = ['result' => 'error'];
            return false;
        }

        $this->results = ['result' => 'success'];
        return true;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function updateUser(array $values)
    {
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

        logVar($query, 'UPDATE QUERY: ');
        logVar($updateValues, 'update values');

        try {
            $this->update($query, $updateValues);
        } catch (\Exception $e) {
            throw new \Exception('Error Updating User');
        }

        $this->results = ['result' => 'success'];
        return true;
    }

    /**
     * @desc inserts a user into the database
     * @return array
     * @throws \Exception
     */
    public function addNewUser()
    {
        $query = 'INSERT INTO users (user_id, first_name, last_name, upassword, '
            .'email, birthday, created_at) '
            .'VALUES (:user_id, :first_name, :last_name, :upassword, :email, '
            .':birthday, :created_at)';

        $values = [
            ':user_id'    => $this->id,
            ':first_name' => $this->firstName,
            ':last_name'  => $this->lastName,
            ':upassword'  => $this->upassword,
            ':email'      => $this->email,
            ':birthday'   => $this->birthday,
            ':created_at' => date('Y-m-d H:i:s'),
        ];

        logVar($query);
        logVar($values);

        try {
            $res = $this->insert($query, $values);
        } catch(\Exception $e) {
            logVar($e->getMessage(), 'got an error:');
            return ['result' => 'error'];
        }

        $this->results = ['result' => 'success'];
        return true;
    }

}
