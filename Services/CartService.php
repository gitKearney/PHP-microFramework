<?php


namespace Main\Services;


use Exception;
use Main\Models\Carts;
use stdClass;

class CartService extends BaseService
{
    /** @var Carts  */
    private $carts;

    public function __construct(Carts $carts)
    {
        $this->carts = $carts;
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function getUsersCart(string $userId): stdClass
    {
        $response = $this->createResponseObject();

        try {
            $cart = $this->carts->findCartById($userId);
        } catch(Exception $e) {
            $response->message = $e->getMessage();

            return $response;
        }

        $response = $this->normalizeResponse($cart);
        return $response;
    }

    /**
     * @param array $body
     * @return stdClass
     */
    public function addProductToCart(array $body): stdClass
    {
        $response = $this->createResponseObject();

        try {
            $this->carts->addToCart($body);
        } catch(Exception $e) {
            $response->message = $e->getMessage();
            return $response;
        }

        $response->success = true;
        $response->message = 'success';
        return $response;
    }

    public function deleteProductFromCart(array $body): stdClass
    {
        $response = $this->createResponseObject();

        try {
            $this->carts->deleteItem($body);
        } catch(Exception $e) {
            $response->message = $e->getMessage();
            return $response;
        }

        $response->success = true;
        $response->message = 'success';
        return $response;
    }
}