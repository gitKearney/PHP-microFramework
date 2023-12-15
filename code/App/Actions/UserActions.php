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
        return setServerResponse([], ['msg' => 'GET'], 'JSON');
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