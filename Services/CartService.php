<?php


namespace Main\Services;


use Main\Models\Carts;

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
     * @return array
     */
    public function getUsersCart(string $userId)
    {
        return $this->carts->findCartById($userId);
    }
}