<?php

namespace App\Actions;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class UserActions
{
    use ActionTrait;

    public function DELETE(ServerRequest $request): Response
    {
        return setServerResponse([], ['msg' => 'DELETE'], 'JSON');
    }

    public function GET(ServerRequest $request): Response
    {
            $query = <<<'query'
SELECT li.id, li.name, COUNT(tuesday_items.lunch_item_id) as amt
FROM lunch_items li
LEFT OUTER JOIN (
    SELECT loi.lunch_item_id
    FROM lunch_order_items loi
             INNER JOIN lunch_orders lo ON loi.lunch_order_id = lo.id
    WHERE lo.weekday = 'TUESDAY'
) AS tuesday_items ON li.id = tuesday_items.lunch_item_id
GROUP BY li.name, li.id
ORDER BY li.id;
query;

        $response = [];

        try {
            $response = select($query, []);
        } catch(Exception $e) {
            $response = ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }

        return setServerResponse([], $response, 'JSON');
    }

    public function HEAD(ServerRequest $request): Response
    {
        return setServerResponse([], ['msg' => 'HEAD'], 'JSON');
    }

    public function OPTIONS(ServerRequest $request): Response
    {
        return setServerResponse([], '', 'JSON');
    }

    public function PATCH(ServerRequest $request): Response
    {
        return setServerResponse([], ['msg' => 'POST'], 'JSON');
    }

    public function POST(ServerRequest $request): Response
    {
        return setServerResponse([], ['msg' => 'POST'], 'JSON');
    }

    public function PUT(ServerRequest $request): Response
    {
        return setServerResponse([], ['msg' => 'PUT'], 'JSON');
    }
}