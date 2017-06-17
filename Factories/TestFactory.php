<?php

namespace Factories;


use Models\Fakes\FakeUserModel;
use Services\UserService;
use Services\UuidService;

class UserFactory
{
    /**
     * @var FakeUserModel
     */
    protected $userModel;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UuidService
     */
    protected $uuidService;

    public function __construct()
    {
        $this->userModel =  new Users;
        $this->uuidService = new UuidService;
        $this->userModel = new FakeUserModel;

        $this->userService = new UserService($this->userModel, $this->uuidService);

        return $this;
    }

    public function create()
    {
        return $this->userService;
    }
}
