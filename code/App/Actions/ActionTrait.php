<?php

namespace App\Actions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

trait ActionTrait
{
    public function handleRequest(ServerRequestInterface $request): void
    {
        switch ($request->getServerParams()['REQUEST_METHOD']) {
            case 'DELETE':
                $response = $this->DELETE($request);
                break;
            case 'GET':
                $response = $this->GET($request);
                break;
            case 'HEAD':
                $response = $this->HEAD($request);
                break;
            case 'OPTIONS':
                $response = $this->OPTIONS($request);
                break;
            case 'PATCH':
                $response = $this->PATCH($request);
                break;
            case 'POST':
                $response = $this->POST($request);
                break;
            case 'PUT':
                $response = $this->PUT($request);
                break;
            default:
                $body = ['success' => false, 'msg' => 'not supported'];
                $response = new JsonResponse($body);
        }

        # first display some server headers
        header(sprintf("HTTP/%s %s %s",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        # next display the other headers
        foreach ($response->getHeaders() as $index => $header) {
            header($index.': '.$header[0]);
        }

        ob_start();
        echo $response->getBody()->__toString();
        ob_end_flush();
    }
}