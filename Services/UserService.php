<?php

namespace Main\Services;

use Main\Models\Users;
use Main\Services\UuidService;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequest;

class UserService extends BaseService
{
    /**
     * @var Users
     */
    protected $userModel;

    /**
     * @var UuidService
     */
    protected $uuid;

    /**
     * This contains all the business logic associated with our users.
     * The Factory\UserFactory class creates all the necessary classes that
     * this service class needs.
     *
     * Then, this class calls the appropriate service or model to update the
     * user or do anything else: like, send a password reset email, or reset
     * the password to a default value
     *
     * @param Users $users
     * @param UuidService $uuidService
     */
    public function __construct(Users $users, UuidService $uuidService)
    {
        $this->userModel = $users;
        $this->uuid = $uuidService;

        return $this;
    }

    /**
     * @desc pull the GUID from the URI
     * @param string $userId
     * @return array
     */
    public function findUserById($userId)
    {
        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found

            return [
                'result' => 'No user found',
            ];
        }

        return $this->userModel->findUserById($userId);
    }
    
    /**
     * @desc returns all users from database
     * @return array
     */
    public function getAllUsers()
    {
        return $this->userModel->getAllUsers();
    }

    /**
     * @param string $userId
     * @return array
     */
    public function deleteUserById($userId)
    {
        $this->debugLogger->enableLogging();

        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            return [
                'result' => 'No user found',
            ];
        }
        
        return $this->userModel->deleteUserById($userId);
    }

    /**
     * @param array $requestBody
     * @return array
     */
    public function addNewUser(array $requestBody)
    {
        # create a new GUID and add it to the body array
        $requestBody['id'] = $this->uuid->generateUuid()->getUuid();

        # set data from the HTTP body to values their matching values on the model
        $this->setUserInfo($requestBody);

        return $this->userModel->addNewUser();
    }

    /**
     * @desc pull info from the request body
     *
     * For this default database, the user table only contains 4 fields
     * since the ID cannot be changed, that leaves only the first and last name
     * as changeable as well as the birthday column.
     *
     * Pull the params from the HTTP body and assign them to the model's data
     * @param array
     * @return bool
     */
    public function setUserInfo($httpBody)
    {
        # TODO: validate that the info is good and in here
        $this->userModel->setFirstName($httpBody['first_name']);
        $this->userModel->setLastName($httpBody['last_name']);
        $this->userModel->setBirthday($httpBody['birthday']);

        if (isset($httpBody['id'])) {
            $this->userModel->setUserId($httpBody['id']);
        }

        return true;
    }

    /**
     * @param array $requestBody
     * @return array
     * @throws \Exception
     */
    public function updateUser(array $requestBody)
    {

        if (! $this->uuid->isValidGuid($requestBody['id'])) {
            # user sent in an invalid GUID, return no records found
            logVar("invalid GUID: " . $requestBody['id']);

            return [
                'result' => 'No user found',
            ];
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

        try {
            return $this->userModel->updateUser($requestBody);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * This takes a ServerRequest and extracts all the relevant data from it
     * It should primarly be used on PUT and PATCH requests
     * @param ServerRequest $request
     * @return array
     */
    public function parseServerRequest(ServerRequest $request)
    {
        $this->debugLogger->enableLogging();

        # get the body from the HTTP request
        $requestBody = json_decode($request->getBody()->__toString());

        if (is_null($requestBody)) {
            # the body isn't a JSON string, it's a form URL encoded string
            # so, convert it here
            $this->debugLogger
                ->setMessage('REQUEST OBJECT BODY IS NOT JSON')
                ->write();

            $requestBody = [];
            parse_str($request->getBody()->__toString(), $requestBody);
        }

        $this->debugLogger
            ->setMessage('HTTP PUT/PATCH BODY')
            ->logVariable($requestBody)
            ->write();

        # check to see if the body contains an id, if not, process this
        # as a PATCH request instead of a PUT request
        if (! isset($requestBody['id'])) {
            # pull the id from the URI by splitting the URI field on the route
            $uriParts = preg_split('/\/users\//', $request->getServerParams()['REQUEST_URI']);

            if (count($uriParts) <= 1) {
                # there was no id sent in the PATCH/PUT request, just return
                # with a success message
                return ['status' => 'success'];
            }

            $requestBody['id']  = $uriParts[1];
        }

        return $requestBody;
    }
}

