<?php
namespace restbuilder;

include_once 'RestBuilder.php';

use restbuilder\RestBuilder;

function sendPost()
{
    # send a POST
    $post     = new RestBuilder;
    $postData = [
        'first_name' => 'Postberth',
        'last_name'  => 'Smith',
        'birthday'   => '2000-01-23',
    ];

    $post
        ->setUri('http://localhost/users/')
        ->setHttpVerb('POST')
        ->setPostData($postData)
        ->sendRequest();

    echo "POST RESULT:\n==========\n", print_r($post->getLastResult(), true), PHP_EOL;
}

function sendGet()
{
    $get = new RestBuilder;
    $get->setUri('http://localhost/users/12345678-1234-1234-1234-123456789abc')
        ->sendRequest();

    echo "POST RESULT:\n==========\n", print_r($get->getLastResult(), true), PHP_EOL;
}

function sendPut()
{
    # send a PUT
    $put     = new RestBuilder;
    $putData = [
        'first_name' => 'Putbert',
        'last_name'  => 'Smith',
        'birthday'   => '1999-02-01',
        'id'         => '12345678-1234-1234-1234-123456789abc',
    ];

    $put
        ->setUri('http://localhost/users/')
        ->setHttpVerb('PUT')
        ->setPostData($putData)
        ->sendRequest();

    echo "PUT RESULT:\n==========\n", print_r($put->getLastResult(), true), PHP_EOL;
}


function sendPatch()
{
    # send a PATCH
    $patch     = new RestBuilder;
    $patchData = [
        'first_name' => 'Patchberth',
        'last_name'  => 'Smith',
        'birthday'   => '2000-02-20',
    ];

    $patch
        ->setUri('http://localhost/users/12345678-1234-1234-1234-123456789abc')
        ->setHttpVerb('PATCH')
        ->setPostData($patchData)
        ->sendRequest();

    echo "PATCH RESULT:\n==========\n", print_r($patch->getLastResult(), true), PHP_EOL;
}

function sendDelete()
{
    # send a DELETE
    $delete = new RestBuilder;

    $delete
        ->setUri('http://localhost/users/12345678-1234-1234-1234-123456789abc')
        ->setHttpVerb('DELETE')
        ->sendRequest();

    echo "DELETE RESULT:\n==========\n", print_r($delete->getLastResult(), true), PHP_EOL;
}

sendPost();

