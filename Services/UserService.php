<?php

namespace Services;

use Models\Users;
use Services\UuidService;
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
    protected $uuidService;

    # GUIDs should be like this: 12345678-1234-1234-1234-123456789abc
    const GUID_REGEX = '/^[a-f\d]{8}-([a-f\d]{4}-){3}[a-f\d]{12}$/i';

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
     * @param UuidService
     */
    public function __construct(Users $users, UuidService $uuid)
    {
        $this->userModel   = $users;
        $this->uuidService = $uuid;

        # instantiate a debug logger for this service
        $this->setDebugLogger();

        return $this;
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
    public function setUserInfo(array $httpBody)
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
     * @desc pull the GUID from the URI
     * @param string $userId
     * @return array
     */
    public function findUserById($userId)
    {
        $this->debugLogger->enableLogging();


        if (! $this->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            $this->debugLogger
                ->setMessage("valid GUID: ")
                ->logVariable($userId)
                ->write();

            return [
                'result' => 'No user found',
            ];
        }

        # log the GUID to the log, the nice thing is we are able to enable
        # logging for each route to make testing easier
        $this->debugLogger
            ->setMessage("valid GUID: ")
            ->logVariable($userId)
            ->write();

        $user = $this->userModel->findUserById($userId);

        if (empty($user)) {
            return ['no users found'];
        }

        return $user;
    }

    public function deleteUserById($userId)
    {
        $this->debugLogger->enableLogging();


        if (! $this->isValidGuid($userId)) {
            # user sent in an invalid GUID, return no records found
            $this->debugLogger
                ->setMessage("valid GUID: ")
                ->logVariable($userId)
                ->write();

            return [
                'result' => 'No user found',
            ];
        }

        # log the GUID to the log, the nice thing is we are able to enable
        # logging for each route to make testing easier
        $this->debugLogger
            ->setMessage("valid GUID: ")
            ->logVariable($userId)
            ->write();

        $user = $this->userModel->deleteUserById($userId);

        if (empty($user)) {
            return ['no users found'];
        }

        return $user;
    }

    /**
     * @param ServerRequest $request
     * @return array
     */
    public function addNewUser(ServerRequest $request)
    {
        # TODO: disable logging when putting in production
        $this->debugLogger->enableLogging();

        # get the body from the HTTP request
        $requestBody = $request->getParsedBody();

        $this->debugLogger
            ->setMessage('HTTP BODY')
            ->logVariable($requestBody)
            ->write();

        # set the info from the HTTP body to values on the model
        $this->setUserInfo($requestBody);

        # now, create a GUID for our user
        $guid = $this->uuidService->generateUuid()->getUuid();
        $this->userModel->setUserId($guid);

        try {
            $result = $this->userModel->addNewUser();
        } catch (\Exception $e) {
            $m = 'ERROR! UserService::addNewUser() ';
            $this->debugLogger->setMessage($m)->logVariable($e->getMessage())->write();
            return ['error' => $e->getMessage()];
        }

        $result['status'] = 'success';
        return $result;
    }

    /**
     * @param ServerRequest $request
     * @return array
     * @throws \Exception
     */
    public function updateUser(ServerRequest $request)
    {
        # TODO: disable logging when putting in production
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

        # check to see if the ID was set on the body, if not, process this as a PATCH request
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

        try {
            return $this->userModel->update($requestBody);
        } catch (\Exception $e) {
            $m = 'ERROR! UserService::addNewUser() ';
            $this->debugLogger->setMessage($m)->logVariable($e)->write();
            return ['error' => $e->getMessage()];
        }
    }


    public function isValidGuid($userId)
    {
        if (preg_match(self::GUID_REGEX, $userId)) {
            return true;
        }

        return false;
    }
}
