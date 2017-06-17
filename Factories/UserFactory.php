<?php

namespace Factories;


use Models\Users;
use Services\UserService;
use Services\UuidService;

class UserFactory
{
    /**
     * @var Users
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
        $this->userService = new UserService($this->userModel, $this->uuidService);

        return $this;
    }

    public function create()
    {
        return $this->userService;
    }
}