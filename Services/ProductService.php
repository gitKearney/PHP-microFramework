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
     * @desc pull the GUID from the URI
     * @param string $userId
     * @return array
     */
    public function getProductInfo($productId)
    {
        # test the GUID to see if it's good
        if (! $this->uuid->isValidGuid($productId)) {
            # user sent in an invalid GUID, return no records found
            return [
                'result' => 'No products found',
            ];
        }

        try {
            $result = $this->product->findProductById($userId);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        if (!$result) {
            return [
                'result' => 'No products found',
            ];
        }

        return $result;
    }

    /**
     * @desc returns all users from database
     * @return array
     */
    public function getAllProducts()
    {
        $this->products->getAllProducts();

        return $this->products->getResults();
    }

    /**
     * @param string $productId
     * @return array
     */
    public function deleteProductById($productId)
    {
        if (! $this->uuid->isValidGuid($productId)) {
            # user sent in an invalid GUID, return no records found
            return [
                'result' => 'No user found',
            ];
        }

        return $this->products->deleteProductById($productId);
    }

    /**
     * @param array $requestBody
     * @return array
     */
    public function addNewProduct(array $requestBody)
    {
        # create a new GUID and add it to the body array
        $requestBody['id'] = $this->uuid->generateUuid()->getUuid();

        try{
            # set data from the HTTP body to values their matching values on the model
            # if any data fails the checks, throw an error and catch it here
            $this->products->setProductInfo($requestBody);
        } catch (\Exception $e) {
            return ['error_code' => $e->getCode(), 'error_msg' => $e->getMessage()];
        }


        try{
            # set data from the HTTP body to values their matching values on the model
            # if any data fails the checks, throw an error and catch it here
            return $this->products->addNewProduct();
        } catch (\Exception $e) {
            return ['error_code' => $e->getCode(), 'error_msg' => $e->getMessage()];
        }
    }

    /**
     * @param array $requestBody
     * @return array
     * @throws \Exception
     */
    public function updateProduct(array $requestBody)
    {

        if (! $this->uuid->isValidGuid($requestBody['id'])) {
            # user sent in an invalid GUID, return no records found
            logVar("invalid GUID: " . $requestBody['id']);

            return [
                'error_code' => 404, 'error_msg' => 'No product found',
            ];
        }

        # update the updated_at timestamp value
        $requestBody['updated_at'] = date('Y-m-d H:i:s');

        try {
            # TODO: verify that the info passed in is good
            return $this->products->updateProduct($requestBody);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * This takes a ServerRequest and extracts all the relevant data from it
     * It should primarly be used on PUT and PATCH requests
     * @param ServerRequest $request
     * @return array
     */
    public function parseServerRequest(ServerRequest $request)
    {
        # get the body from the HTTP request
        $requestBody = json_decode($request->getBody()->__toString());

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
