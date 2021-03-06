<?php

namespace Main\Models;

use Exception;

class Users extends BaseModel
{
    /**
     * Users constructor.
     */
    public function __construct()
    {
        # In this example, the app will run selects on a different server
        # than were writes occur. So, we set the values of the connection to
        # be different.

        # You could also create a key/value pair called "user_database" and have
        # the login credentials there
        $this->setReadConnectionId('read_database');
        $this->setWriteConnectionId('write_database');
    }

    /**
     * @param string $userId
     * @return array
     * @throws Exception
     */
    public function findUserById(string $userId): array
    {
        $query = <<<QUERY
            SELECT user_id as id,first_name,last_name,birthday,roles,email
            FROM users WHERE user_id = :user_id LIMIT 1
QUERY;
        $params = [':user_id' => $userId];

        return $this->select($query, $params);
    }

    /**
     * Returns all users form the database
     * @return array
     * @throws Exception
     */
    public function getAllUsers(): array
    {
        $query = <<<QUERY
            SELECT user_id as id,first_name,last_name,birthday,email,created_at
            FROM users
QUERY;
        $params = [];

        return $this->select($query, $params);
    }

    /**
     * @param string $userId
     * @return void
     * @throws Exception
     */
    public function deleteUserById(string $userId)
    {
        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        $this->delete($query, $params);
    }

    /**
     * This is an example where we update the user by user ID
     * @param array $values
     * @return void
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
                $updateValues[':upassword'] = password_hash($value,  PASSWORD_ARGON2ID);
                $set .= 'upassword = :upassword,';
            }

            $set .= $key.' = :'.$key.',';
            $updateValues[':'.$key] = $value;
        }

        # remove the trailing comma from the set string
        $set = trim($set, ',');

        if (strlen($set) == 0) {
            logVar('', 'No User Passed In Values');
            return;
        }

        $query = 'UPDATE users SET '.$set.' WHERE '.$where;

       $this->update($query, $updateValues);
    }

    /**
     * @desc inserts a user into the database
     * @param array $values
     * @return void
     * @throws Exception
     */
    public function addNewUser(array $values)
    {
        $query = $this->buildInsertQuery($values, 'users');
        $this->insert($query->sql, $query->params);
    }

    /**
     * @param string $email
     * @return array
     * @throws Exception
     */
    public function findUserByEmail(string $email): array
    {
        $query  = 'SELECT * FROM users WHERE email = :email';
        $params = [':email' => $email,];

        return $this->select($query, $params);
    }
}
