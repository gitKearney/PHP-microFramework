<?php

namespace tester;

/**
 * @author Kearney Taaffe.
 * @desc Tests the Service\UserService service
 */
class UserServiceTester
{
    public function __construct()
    {

    }

    /**
     * This will run successful suites. BTW, there's nothing wrong with PHPUnit
     * This is just a quick and dirty way to test
     */
    public function beginSuccessfulTests()
    {
        $userTestFactory = new \Factories\UserTestFactory;

        $userService = $userTestFactory->create();

        # add a new user
        $postData = [
            'first_name' => 'James',
            'last_name'  => 'Bond',
            'birthday'   => '1950-07-07',
            'id'         => '12345678-1234-1234-1234-123456789abc',
        ];

        $result = $userService->addNewUser($postData);

        if (strcasecmp($result['id'], '12345678-1234-1234-1234-123456789abc') !== 0) {
            echo 'ADDING NEW USER...FAILED', PHP_EOL;
        } else {
            echo 'ADDING NEW USER...PASSED', PHP_EOL;
        }

        $result = $userService->updateUser($postData);
        file_put_contents('/tmp/php.debug.log', 'update result: '.print_r($result, true), FILE_APPEND);
        if (strcasecmp($result['result'], 'success') != 0) {
            echo 'UPDATING USER...FAILED', PHP_EOL;
        } else {
            echo 'UPDATING USER...PASSED', PHP_EOL;
        }
    }
}

# this function attempts to find the classes from our framework to include
spl_autoload_register(function ($class_name) {
    $base_dir = __DIR__;
    $file = $base_dir.'/../' . str_replace('\\', '/', $class_name) . '.php';

    file_put_contents(
        "/tmp/php.debug.log",
        "trying to include: " . $file . PHP_EOL,
        FILE_APPEND
    );

    include $file;
});

$userServiceTester = new UserServiceTester;
$userServiceTester->beginSuccessfulTests();

