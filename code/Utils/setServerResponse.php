<?php

use Laminas\Diactoros\Response;

/**
 * @param array $headers - array of strings
 * @param array|string $body - most likely an array
 * @param string $type - one of the following: "TXT, HTML, JSON"
 * @param int $status - status code
 * @param bool $isOption - if HTTP OPTION
 * @return Response
 */
function setServerResponse(array $headers, array|string $body, string $type, int $status=200, bool $isOption=false): Response
{
    $headers['Content-Length'] = strval(strlen(json_encode($body)));
    $headers['Access-Control-Allow-Origin'] = '*';

    if ($isOption) {
        $headers['Content-Length'] = "0";
        $headers['Access-Control-Allow-Headers'] = 'application/x-www-form-urlencoded,'
            .'X-Requested-With, content-type, Authorization';
        $headers['Content-Type'] = 'text/plain; charset=utf-8';
        $headers['Access-Control-Allow-Methods'] = 'OPTIONS, GET, POST, PATCH, PUT, DELETE, HEAD';
    }

    switch ($type) {
        case 'JSON':
            $response = new Response\JsonResponse($body);
            break;
        case 'HTML':
            $headers['Content-Type'] = 'text/html; charset=utf-8';
            $response = new Response\HtmlResponse($body, 200, $headers);
            break;
        default:
            $headers['Content-Type'] = 'text/plain; charset=utf-8';
            $response = new Response\TextResponse($body, 200, $headers);
    }

    return $response;
}