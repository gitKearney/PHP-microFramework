<?php
namespace Main\Services;

use stdClass;
use Main\Models\BaseModel;

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

    /**
     * @desc Fixes the data from the query and sets normal responses to the API
     * @param BaseModel $model
     * @param stdClass $response
     */
    public function normalizeResponse(BaseModel $model, $response)
    {
        $response->success = true;
        $results = $model->getResults();

        if (count($results) === 0) {
            $response->message = 'No Results';
        }

        return $response->results = $results;
    }
}
