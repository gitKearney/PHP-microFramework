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
     * @return \stdClass
     */
    public function findUserById($userId)
    {
        $result = new \stdClass();

        $result->success = false;
        $result->message = '';
        $result->results = [];

        # test the GUID to see if it's good
        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
           $result->message = 'No User Found';
           return $result;
        }

        $result = $this->userModel->findUserById($userId);
        unset($result->results['password']);
        return $result;
    }

    /**
     * @return \stdClass
     */
    public function getAllUsers()
    {
        $result = $this->userModel->getAllUsers();

        return $result;
    }

    /**
     * @param string $userId
     * @return \stdClass
     */
    public function deleteUserById($userId)
    {
        $result = new \stdClass();

        $result->success = false;
        $result->message = "$userId";
        $result->results = [];

        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            $result->message = 'Invalid User Found';
            return $result;
        }

        $result = $this->userModel->deleteUserById($userId);

        return $result;
    }

    /**
     * @param array $requestBody
     * @return \stdClass
     */
    public function addNewUser(array $requestBody)
    {
        # create a new GUID and add it to the body array
        $requestBody['id'] = $this->uuid->generateUuid()->getUuid();

        # set data from the HTTP body to values their matching values on the model.
        # we are specifically avoiding just passing in the POST body.
        # This will make you read the BODY and only add the valid fields, 
        # and not the JUNK field some hacker added to the post
        $this->userModel->setUserInfo($requestBody);

        return $this->userModel->addNewUser();
    }

    /**
     * @param array $requestBody
     * @return \stdClass
     */
    public function updateUser(array $requestBody)
    {
        if (! $this->uuid->isValidGuid($requestBody['id'])) {
            # user sent in an invalid GUID, return no records found
            $result = new \stdClass();

            $result->success = false;
            $result->message = 'No User Found';
            $result->results = [];
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

       return $this->userModel->updateUser($requestBody);
    }

    /**
     * This takes a ServerRequest and extracts all the relevant data from it
     * It should primarily be used on PUT and PATCH requests
     * @param ServerRequest $request
     * @return array
     */
    public function parseServerRequest(ServerRequest $request)
    {
        # get the body from the HTTP request
        $requestBody = json_decode($request->getBody()->__toString(), true);

        logVar($requestBody, 'requestBody: ');

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
