<?php
namespace Main\Services;

use Exception;
use Main\Models\Carts;
use Main\Models\TransactionProducts;
use Main\Models\Transactions;
use stdClass;

class CheckoutService extends BaseService
{
    private Carts $carts;
    private TransactionProducts $transProds;
    private Transactions $transactions;
    private UuidService $uuidService;

    public function __construct(Carts $carts,
                                TransactionProducts $transactionProducts,
                                Transactions $transactions,
                                UuidService $uuidService)
    {
        $this->carts        = $carts;
        $this->transProds   = $transactionProducts;
        $this->transactions = $transactions;
        $this->uuidService  = $uuidService;
    }

    /**
     * @param string $userId
     * @return stdClass
     */
    public function checkout(string $userId): stdClass
    {
        $response = $this->createResponseObject();

        $inputs = [':user_id' => $userId];

        # get all the product ids from the user_cart
        try {
            $products = $this->carts->findCartById($userId);
        } catch(Exception $e) {
            $response->message = $e->getMessage();
            return $response;
        }

        # create a new GUID for the transaction
        try {
            $transId = $this->uuidService->generateUuid()->getUuid();

            foreach($products as $index => $value) {
                if (!is_array($value)) {
                    $transProds = [
                        'transaction_id' => $transId,
                        'product_id'     => $products['product_id'],
                        'product_price'  => $products['price'],
                    ];

                    $this->transProds->addTransactionProduct($transProds);
                    break;
                }

                $transProds = [
                    'transaction_id' => $transId,
                    'product_id'     => $value['product_id'],
                    'product_price'  => $value['price'],
                ];

                $this->transProds->addTransactionProduct($transProds);
            }

            $transValues = ['transaction_id' => $transId, 'user_id' => $userId];
            $this->transactions->addNewTransaction($transValues);

            # now delete the user's cart
            $this->carts->clearCart($userId);
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            return $response;
        }

        $response->success = true;
        $response->message = 'success';
        $response->results = ['id' => $transId];

        return $response;
    }
}