<?php

/**
 * creates 1,000 child processes to hit an endpoint to see how many connections
 * it supports.
 * It's not nearly as good as AB, but, it should work
 *
 */

require_once 'RestBuilder.php';

use restbuilder\RestBuilder;

function sendHttpGet($childId)
{
    $get = new RestBuilder;
    $get->setUri('http://localhost/users/12345678-1234-1234-1234-123456789abc')
        ->sendRequest();

    echo "POST RESULT [$childId]:\n==========\n", print_r($get->getLastResult(), true), PHP_EOL;
}

function createChild()
{
    # this is where we start the fork in the script. The child process will start executing at this point
    return pcntl_fork();
}

function main()
{
    # we want to keep track of the number of children
    $children = 1;

    # max no. of child processes
    $max = 1000;

    $pid = null;

    while ($children <= $max) {
        # initialize the parent ID to something not false
        echo "attempting to spawn child [$children]\n";
        $pid = createChild();

        # did we fail to create a fork? If so, throw an error!
        if ($pid == -1) {
            $errorNo = pcntl_get_last_error();
            $errorSt = pcntl_strerror($errorNo);
            echo "Could not fork [$errorNo] - $errorSt", PHP_EOL;
            die();
        }

        # child processes always have a process ID of 0
        if ($pid == 0) {
            sendHttpGet($children);

            # this breaks out of the while loop: so the child process doesn't
            # try to spawn more children
            break;
        }

        # the parent ID will always be >= 1, so any code here is run by the
        # parent process (i.e. the main process)
        $children++;
    }
}

main();
