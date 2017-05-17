<?php

namespace Controllers;

use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\ServerRequest;

trait RequestTrait
{
    /**
     * @return ServerRequest
     */
    function processRequest()
    {
        return ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
    }
}
