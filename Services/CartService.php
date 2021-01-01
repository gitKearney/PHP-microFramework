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
}