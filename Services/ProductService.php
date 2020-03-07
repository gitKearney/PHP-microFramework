<?php

namespace Main\Services;

use Exception;
use Main\Models\Products;
use stdClass;
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
    private $uuidService;

    /**
     * This contains all the business logic associated with our products.
     * The Factory class creates all the necessary classes that
     * this service class needs.
     *
     * Then, this class calls the appropriate service or model to update the
     * product or do anything else
     *
     * @param Products $productModel
     * @param UuidService $uuidService
     */
    public function __construct(Products $productModel, UuidService $uuidService)
    {
        $this->products = $productModel;
        $this->uuidService = $uuidService;
    }

    /**
     * pull the GUID from the URI
     * @param string $productId
     * @return stdClass
     */
    public function getProductInfo($productId)
    {
        $response = $this->createResponseObject();

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
     * @return stdClass
     */
    public function getAllProducts()
    {
        return $this->products->getAllProducts();
    }

    /**
     * @param string $productId
     * @return stdClass
     */
    public function deleteProductById($productId)
    {
        $response = $this->createResponseObject();

        if (! $this->uuidService->isValidGuid($productId)) {
            $response->message =  'No product found';
            return $response;
        }

        return $this->products->deleteProductById($productId);
    }

    /**
     * @param array $requestBody
     * @return stdClass
     */
    public function addNewProduct(array $requestBody)
    {
        $response = $this->createResponseObject();

        try{
            # create a new GUID and add it to the body array
            $requestBody['id'] = $this->uuidService->generateUuid()->getUuid();
        } catch (Exception $e) {
            $response->message = 'Error Creating Product';
            return $response;
        }

        $values = $this->products->setProductInfo($requestBody);
        $response = $this->products->addNewProduct($values);
        return $response;
    }

    /**
     * @param array $requestBody
     * @return stdClass
     */
    public function updateProduct(array $requestBody)
    {
        $response = new stdClass();
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
