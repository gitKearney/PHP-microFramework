<?php
namespace restbuilder;

include_once 'RestBuilder.php';

use restbuilder\RestBuilder;

/**
 * creates a new user
 */
function sendPost()
{
    # send a POST
    $post     = new RestBuilder;
    $postData = [
        'first_name' => 'Bob',
        'last_name'  => 'Smith',
        'birthday'   => '1990-01-02',
    ];

    $post
        ->setUri('http://localhost/users/')
        ->setHttpVerb('POST')
        ->setPostData($postData)
        ->sendRequest();

    echo "POST RESULT:\n==========\n", print_r($post->getLastResult(), true), PHP_EOL;
}

/**
 * @param string $guid
 */
function sendGet($guid)
{
    $get = new RestBuilder;
    $get->setUri('http://localhost/users/'.$guid)
        ->sendRequest();

    echo "POST RESULT:\n==========\n", print_r($get->getLastResult(), true), PHP_EOL;
}

/**
 * @param string $guid
 */
function sendPut($guid)
{
    # send a PUT
    $put     = new RestBuilder;
    $putData = [
        'first_name' => 'Bob',
        'last_name'  => 'Smith',
        'birthday'   => '1990-01-13',
        'id'         => $guid,
    ];

    $put
        ->setUri('http://localhost/users/')
        ->setHttpVerb('PUT')
        ->setPostData($putData)
        ->sendRequest();

    echo "PUT RESULT:\n==========\n", print_r($put->getLastResult(), true), PHP_EOL;
}

/**
 * @param string $guid
 */
function sendPatch($guid)
{
    # send a PATCH
    $patch     = new RestBuilder;
    $patchData = [
        'first_name' => 'Bob',
        'last_name'  => 'Smith',
        'birthday'   => '1990-01-23',
    ];

    $patch
        ->setUri('http://localhost/users/'.$guid)
        ->setHttpVerb('PATCH')
        ->setPostData($patchData)
        ->sendRequest();

    echo "PATCH RESULT:\n==========\n", print_r($patch->getLastResult(), true), PHP_EOL;
}

/**
 * @param string $guid
 */
function sendDelete($guid)
{
    # send a DELETE
    $delete = new RestBuilder;

    $delete
        ->setUri('http://localhost/users/'.$guid)
        ->setHttpVerb('DELETE')
        ->sendRequest();

    echo "DELETE RESULT:\n==========\n", print_r($delete->getLastResult(), true), PHP_EOL;
}

function main($argv)
{
    $command = '';

    # get the args passed in. If the user didn't pass in anything, return an
    # error telling them they need to pass in arguments
    if (count($argv) == 1):
        printUsage();
        return;
    endif;

    $command = strtolower($argv[1]);

    # only 2 arguments were passed in, if the user didn't use the post
    # command, then return an error
    if (count($argv) == 2):
        # the user better have used the 'POST' command
        if (strcasecmp($command, 'post') != 0):
            echo 'You MUST pass in a GUID when using the ', $command,
            ' option', PHP_EOL;
            printUsage();
            return;
        endif;
    else:
        $guid = $argv[2];
    endif;

    switch($command):
        case 'post':
            echo 'adding new user', PHP_EOL;
            sendPost();
            break;
        case 'put':
            echo 'Updating (PUT) user with GUID: ', $guid, PHP_EOL;
            sendPut($guid);
            break;
        case 'patch':
            echo 'Updating (PATCH) user with GUID: ', $guid, PHP_EOL;
            sendPatch($guid);
            break;
        case 'get':
            echo 'Getting user with GUID: ', $guid, PHP_EOL;
            sendGet($guid);
            break;
        case 'delete':
            echo 'Deleting user with GUID: ', $guid, PHP_EOL;
            sendDelete($guid);
            break;
        default:
            echo 'Invalid argument', PHP_EOL;
            printUsage();
            break;
    endswitch;
}

function printUsage()
{
    echo 'USAGE: ', PHP_EOL,
    '    php UserEndPoints.php [POST*|PUT|PATCH|GET|DELETE] [GUID]',
    PHP_EOL, PHP_EOL,
    '* If POST command is used, the GUID does not need to set', PHP_EOL;
}

# call our main function passing in the command line arguments
main($argv);

