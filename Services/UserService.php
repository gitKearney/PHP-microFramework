<?php

namespace Main\Services;

use Main\Models\Users;
use Zend\Diactoros\ServerRequest;
use stdClass;
use Exception;

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
     * @return stdClass
     */
    public function findUserById($userId)
    {
        $response = $this->createResponseObject();

        # test the GUID to see if it's good
        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            $response->message = 'No User Found';
           return $response;
        }

        try {
            $this->userModel->findUserById($userId);
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        $this->normalizeResponse($this->userModel, $response);
        unset($response->results['password']);

        return $response;
    }

    /**
     * @param array $queryParams
     * @return stdClass
     */
    public function findUserByQueryString(array $queryParams)
    {
        $response = $this->createResponseObject();

        try {
            $sql = $this->userModel->buildSearchString($queryParams, 'users');
            $this->userModel->select($sql->sql, $sql->params);
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            return $response;
        }

        $this->normalizeResponse($this->userModel, $response);
        return $response;
    }

    /**
     * @return stdClass
     */
    public function getAllUsers()
    {
        $response = $this->createResponseObject();
        try {
            $this->userModel->getAllUsers();
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        $this->normalizeResponse($this->userModel, $response);
        return $response;
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function deleteUserById($userId)
    {
        $result = new stdClass();

        $result->success = false;
        $result->message = "$userId removed";
        $result->results = [];

        if (! $this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            $result->message = 'No User Found';
            return $result;
        }

        $result = $this->userModel->deleteUserById($userId);

        return $result;
    }

    /**
     * @param array $requestBody
     * @throws Exception
     * @return stdClass
     */
    public function addNewUser(array $requestBody)
    {
        # create a new GUID and add it to the body array
        $requestBody['id'] = $this->uuid->generateUuid()->getUuid();
        $result = $this->createResponseObject();

        try {
            $values = $this->userModel->getNewUserInfo($requestBody);
            $this->userModel->addNewUser($values);
            $result->results['id'] = $requestBody['id'];
            $result->success = true;
        } catch(Exception $e) {
            $result->message = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param array $requestBody
     * @return void
     * @throws Exception
     */
    public function updateUser(array $requestBody)
    {
        $userId = isset($requestBody['id']) ? $requestBody['id'] : '';

        if (!$this->uuid->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            throw new Exception('No User Found', 400);
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

       $this->userModel->updateUser($requestBody);
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
        if (!isset($requestBody['id'])) {
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

    /**
     * @param string $guid
     * @param string $requiredRole
     * @return bool
     * @throws Exception
     */
    public function userAllowedAction($guid, $requiredRole)
    {
        $user = $this->userModel->findUserById($guid);

        $userRole = $user->results['roles'] ?? 'read';

        if (strcasecmp($userRole, 'create') === 0) {
            # this is the equivalent of "admin" so, grant it
            return true;
        }

        $hasEditPermission   = strcasecmp($userRole, 'edit') <=> 0 ? false : true;
        $hasReadPermission   = strcasecmp($userRole, 'read') <=> 0 ? false : true;

        $needsEditPermission = strcasecmp($requiredRole, 'edit') <=> 0 ? false : true;
        if ($needsEditPermission && $hasEditPermission) {
            return true;
        }

        $needsReadPermission = strcasecmp($requiredRole, 'read') <=> 0 ? false : true;
        if ($needsReadPermission && $hasEditPermission) {
            return true;
        }

        if ($needsReadPermission && $hasReadPermission) {
            return true;
        }

        return false;
    }
}
