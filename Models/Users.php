<?php

namespace Main\Models;

class Users extends BaseModel
{
    protected $firstName;
    protected $lastName;
    protected $upassword;
    protected $email;
    protected $birthday;
    protected $id;
    protected $createdAt;
    protected $updatedAt;

    /**
     * Users constructor.
     */
    public function __construct()
    {
        // your database credentials in config/credentials.php
        // In this example, the app will run selects on a different server
        // than were writes occur. So, we set the values of the connection to 
        // be different
        $this->readConnectionId  = 'read_database';
        $this->writeConnectionId = 'write_database';
    }

    /**
     * @param string $userId
     * @return \stdClass
     */
    public function findUserById($userId)
    {
        $query = 'SELECT user_id as id, first_name, last_name, birthday,'
            .' email, upassword as password, created_at, updated_at'
            .' FROM users WHERE user_id = :user_id LIMIT 1';
        $params = [':user_id' => $userId];

        $result = $this->select($query, $params);

        return $result;
    }

    /**
     * Returns all users form the database
     * @return \stdClass
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
     * @return \stdClass
     */
    public function deleteUserById($userId)
    {
        $query = 'DELETE FROM users WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        $result = $this->delete($query, $params);

        return $result;
    }

    /**
     * @param array $values
     * @return \stdClass
     */
    public function updateUser(array $values)
    {
        $where = '';
        $set = '';
        $updateValues = [];
        foreach ($values as $key => $value) {
            # we need to build the query string

            # remove the spaces from the value, we don't ever want to allow spaces
            $value = trim($value);
            if ( strlen($value) == 0) {
                # catch someone who just puts in empty spaces for the user
                # information, and ignore this field
                continue;
            }

            # if the key is the id, then, set the where clause, otherwise
            # add the key to the where clause
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
            $result = new \stdClass();
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
     * @return \stdClass
     */
    public function addNewUser()
    {
        $result = new \stdClass();
        $result->success = false;
        $result->message = 'Nothing to Update';
        $result->results = [];

        $query = 'INSERT INTO users (user_id, first_name, last_name, upassword, '
            .'email, birthday, created_at) '
            .'VALUES (:user_id, :first_name, :last_name, :upassword, :email, '
            .':birthday, :created_at)';

        $createdDate = date('Y-m-d H:i:s');
        $values = [
            ':user_id'    => $this->id,
            ':first_name' => $this->firstName,
            ':last_name'  => $this->lastName,
            ':upassword'  => $this->upassword,
            ':email'      => $this->email,
            ':birthday'   => $this->birthday,
            ':created_at' => $createdDate,
        ];

        $result = $this->insert($query, $values);

        # if we got a success, return an object containing the
        # user's ID
        $result->results['id'] = $this->id;

        return $result;
    }

    /**
     * @param string $email
     * @return \stdClass
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
     * @return bool
     */
    public function setUserInfo($httpBody)
    {
        # TODO: validate that the info is good and in here
        $this->firstName = $httpBody['first_name'] ?? null;
        $this->lastName  = $httpBody['last_name'] ?? null;
        $this->email     = $httpBody['email'] ?? null;
        $this->birthday  = $httpBody['birthday'] ?? null;
        $this->id        = $httpBody['id'] ?? null;

        // pull the password from the body
        $password        = $httpBody['password'] ?? null;
        $this->upassword = $this->upassword = password_hash($password, PASSWORD_DEFAULT);

        return true;
    }
}
