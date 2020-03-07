<?php

namespace Main\Models;

use Exception;
use stdClass;

class Users extends BaseModel
{
    /**
     * Users constructor.
     */
    public function __construct()
    {
        // TODO: change user_database to name of key with your database
        // credentials in config/credentials.php

        # In this example, the app will run selects on a different server
        # than were writes occur. So, we set the values of the connection to
        # be different.

        # You could also create a key/value pair called "user_database" and have
        # the login credentials there
        $this->readConnectionId  = 'read_database';
        $this->writeConnectionId = 'write_database';
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function findUserById($userId)
    {
        $query = 'SELECT user_id as id, first_name, last_name, birthday,'
            .' roles, email, upassword as password, created_at, updated_at'
            .' FROM users WHERE user_id = :user_id LIMIT 1';
        $params = [':user_id' => $userId];

        $result = $this->select($query, $params);

        return $result;
    }

    /**
     * Returns all users form the database
     * @return stdClass
     */
    public function getAllUsers()
    {
        $query = 'SELECT user_id as id, first_name, last_name, birthday'
            .' email, created_at'
            .' FROM users';
        $params = [];

        $result = $this->select($query, $params);

        return $result;
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function deleteUserById($userId)
    {
        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        $result = $this->delete($query, $params);

        return $result;
    }

    /**
     * This is an example where we update the user by user ID
     * @param array $values
     * @return stdClass
     */
    public function updateUser(array $values)
    {
        $where = '';
        $set = '';
        $updateValues = [];
        foreach ($values as $key => $value) {
            # we need to build the query string

            # our users table has no nullable fields, so, if the value is an
            # empty string just skip it
            $value = trim($value);
            if ( strlen($value) == 0) {
                continue;
            }

            # in this example, we are only updating by the user id
            if ($key == 'id') {
                $updateValues[':user_id'] = $value;
                $where .= 'user_id = :user_id';
                continue;
            }

            if ($key == 'password') {
                $updateValues[':upassword'] = password_hash($value, PASSWORD_DEFAULT);
                $set .= 'upassword = :upassword,';
            }

            $set .= $key.' = :'.$key.',';
            $updateValues[':'.$key] = $value;
        }

        # remove the trailing comma from the set string
        $set = trim($set, ',');

        if (strlen($set) == 0) {
            # the user didn't pass anything in, send a success
            $result = new stdClass();
            $result->success = false;
            $result->message = 'Nothing to Update';
            $result->results = [];

            return $result;
        }

        $query = 'UPDATE users SET '.$set.' WHERE '.$where;

        $results = $this->update($query, $updateValues);

        return $results;
    }

    /**
     * @desc inserts a user into the database
     * @array $values
     * @return stdClass
     * @throws Exception
     */
    public function addNewUser($values)
    {
        try {
            $query = $this->buildInsertQuery($values, 'users');
            $result = $this->insert($query->sql, $query->params);
        } catch (Exception $e) {
            throw $e;
        }

        # if we got a success, return an object containing the
        # user's ID
        $result->results['id'] = $values['user_id'];

        return $result;
    }

    /**
     * @param string $email
     * @return stdClass
     */
    public function findUserByEmail($email)
    {
        $query  = 'SELECT * FROM users WHERE email = :email';
        $params = [':email' => $email,];

        $results = $this->select($query, $params);

        return $results;
    }

    /**
     * @desc pull info from the request body
     *
     * Pull the params from the HTTP body and assign them to the model's data
     * @param array
     * @return array
     * @throws Exception
     */
    public function setUserInfo($httpBody)
    {
        $postValues = [];

        # TODO: validate that the info is good and in here
        $postValues['first_name'] = $httpBody['first_name'] ?? null;
        $postValues['last_name']  = $httpBody['last_name'] ?? null;
        $postValues['email']      = $httpBody['email'] ?? null;
        $postValues['birthday']   = $httpBody['birthday'] ?? null;
        $postValues['user_id']    = $httpBody['id'] ?? null;
        $password                 = $httpBody['password'] ?? null;

        if (!$password) {
            throw new Exception('password not set', 400);
        }

        $postValues['upassword'] = password_hash($password, PASSWORD_ARGON2ID);
        return $postValues;
    }
}
