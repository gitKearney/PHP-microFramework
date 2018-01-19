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
        # test the GUID to see if it's good
        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            return [
                'result' => 'No user found',
            ];
        }

        try {
            $result = $this->userModel->findUserById($userId);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        if (!$result) {
            return [
                'result' => 'No user found',
            ];
        }

        return $result;
    }

    /**
     * @desc returns all users from database
     * @return array
     */
    public function getAllUsers()
    {
        $this->userModel->getAllUsers();

        return $this->userModel->getResults();
    }

    /**
     * @param string $userId
     * @return array
     */
    public function deleteUserById($userId)
    {
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

        # set data from the HTTP body to values their matching values on the model.
        # we are specifically avoiding just passing in the POST body.
        # This will make you read the BODY and only add the valid fields, 
        # and not the kdfsdfd field some hacker added to the post
        $this->userModel->setUserInfo($requestBody);

        return $this->userModel->addNewUser();
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
            return [
                'result' => 'No user found',
            ];
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

        try {
            return $this->userModel->updateUser($requestBody);
        } catch (\Exception $e) {
            $error = new \stdClass();
            $error->error_msg = $e->getMessage();
            $error->error_code = $e->getCode();
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
        # get the body from the HTTP request
        $requestBody = json_decode($request->getBody()->__toString());

        if (is_null($requestBody)) {
            # the body isn't a JSON string, it's a form URL encoded string
            # so, convert it here
            $requestBody = [];
            parse_str($request->getBody()->__toString(), $requestBody);
        }

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
