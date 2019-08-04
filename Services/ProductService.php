<?php

namespace Main\Services;

use Main\Models\Products;
use Main\Services\UuidService;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequest;

class ProductService extends BaseService
{
    /**
     * @var Products
     */
    private $products;

    /**
     * @var UuidService
     */
    private $uuid;

    /**
     * This contains all the business logic associated with our users.
     * The Factory\UserFactory class creates all the necessary classes that
     * this service class needs.
     *
     * Then, this class calls the appropriate service or model to update the
     * user or do anything else: like, send a password reset email, or reset
     * the password to a default value
     *
     * @param Products $productModel
     * @param UuidService $uuidService
     */
    public function __construct(Products $productModel, UuidService $uuidService)
    {
        $this->products = $productModel;
        $this->uuid = $uuidService;
    }

    /**
     * pull the GUID from the URI
     * @param string $productId
     * @return \stdClass
     */
    public function getProductInfo($productId)
    {
        $response = new \stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        # test the GUID to see if it's good
        if (! $this->uuid->isValidGuid($productId)) {
            # user sent in an invalid GUID, return no records found
            # user sent in an invalid GUID, return no records found
            logVar($productId, "Invalid GUID: ");

            $response->message =  'No product found';
            return $response;
        }

        $result = $this->products->findProductById($productId);

        if (!$result->success) {
            $response->message =  'No product found';
            return $response;
        }

        return $result;
    }

    /**
     * @desc returns all users from database
     * @return \stdClass
     */
    public function getAllProducts()
    {
        return $this->products->getAllProducts();
    }

    /**
     * @param string $productId
     * @return \stdClass
     */
    public function deleteProductById($productId)
    {
        $response = new \stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        if (! $this->uuid->isValidGuid($productId)) {
            # user sent in an invalid GUID, return no records found
            # user sent in an invalid GUID, return no records found
            logVar($productId, "Invalid GUID: ");

            $response->message =  'No product found';
            return $response;
        }

        return $this->products->deleteProductById($productId);
    }

    /**
     * @param array $requestBody
     * @return \stdClass
     */
    public function addNewProduct(array $requestBody)
    {
        $response = new \stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        try{
            # create a new GUID and add it to the body array
            $requestBody['id'] = $this->uuid->generateUuid()->getUuid();
        } catch (\Exception $e) {
            logVar($e->getCode(), 'EXCEPTION CREATING UUID: '.$e->getMessage(), 'emergency');

            $response->message = 'Error Creating User';
            return $response;
        }

        $goodData = $this->products->setProductInfo($requestBody);
        if (!$goodData) {
            $response->message = 'Bad Data';
            return $response;
        }

        $response = $this->products->addNewProduct($goodData);
        return $response;
    }

    /**
     * @param array $requestBody
     * @return \stdClass
     */
    public function updateProduct(array $requestBody)
    {
        $response = new \stdClass();
        $response->success = false;
        $response->message = '';
        $response->results = [];

        if (! $this->uuid->isValidGuid($requestBody['id'])) {
            # user sent in an invalid GUID, return no records found
            logVar($requestBody['id'], "invalid GUID: ");

            $response->message =  'No product found';
            return $response;
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

        $result = $this->products->updateProduct($requestBody);
        return $result;
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
            $uriParts = preg_split('/\/products\//', $request->getServerParams()['REQUEST_URI']);

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
