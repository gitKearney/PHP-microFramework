<?php
namespace Main\Services;

use stdClass;

class BaseService
{
    /**
     * @return stdClass
     */
    public function createResponseObject()
    {
        $response = new stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        return $response;
    }
}
