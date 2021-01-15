<?php
namespace Main\Services;

use stdClass;
use Main\Models\BaseModel;

class BaseService
{
    /**
     * @return stdClass
     */
    public function createResponseObject(): stdClass
    {
        $response = new stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];
        $response->code = 200;

        return $response;
    }

    /**
     * @desc Fixes the data from the query and sets normal responses to the API
     * @param array $results
     * @return stdClass
     */
    public function normalizeResponse(array $results): stdClass
    {
        $response = $this->createResponseObject();
        $response->success = true;
        $response->results =$results;

        if (count($results) === 0) {
            $response->message = 'No Results';
        } else {
            $response->message = 'success';
        }

        return $response;
    }
}
