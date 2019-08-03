<?php

/**
 * Save your configuration settings in a file called "credentials.php".
 * The file "credentials.php" SHOULD NOT be committed to your repo.
 *
 * This is much better than using a .env file because it requires no overhead
 * of opening a file, reading it line by line, putting the value into 3
 * separate places (evn, $_ENV, and $_SERVER) then closing the file.
 */

# TODO: create a new file in this directory called "credentials.php"
# TODO: copy the code here to the "credentials.php" file
$config = new stdClass();

// You can add as many database connection details by creating a new stdClass
// as a property to $config

// This demonstrates how to use use 1 database for reading and a different
// database for writing.
$config->read_database = new stdClass();
$config->read_database->type = 'mysql';
$config->read_database->name = 'demo';
$config->read_database->port = '3306';
$config->read_database->host = '192.168.1.10';
$config->read_database->user = 'read_user';
$config->read_database->pass = 'secret';

$config->write_database = new stdClass();
$config->write_database->type = 'mysql';
$config->write_database->name = 'demo';
$config->write_database->port = '3306';
$config->write_database->host = '192.168.1.11';
$config->write_database->user = 'write_user';
$config->write_database->pass = 'secret';

// The model has two properties: readConnectionId and writeConnectionId, which
// tell the model what database to use for reading and writing.

// If you will be reading/writing to same database set readConnectionId &
// writeConnectionId to the same value, and create a new config key, like so
$config->product_database = new stdClass();
$config->product_database->type = 'mysql';
$config->product_database->name = 'products';
$config->product_database->port = '3306';
$config->product_database->host = '192.168.1.12';
$config->product_database->user = 'userdb_user';
$config->product_database->pass = 'userdb_pass';

$config->memory_database = new stdClass();
$config->memory_database->type = 'memory';
$config->memory_database->name = 'demo_memory';
$config->memory_database->port = '3306';
$config->memory_database->host = '127.0.0.1';
$config->memory_database->user = 'memory_user';
$config->memory_database->pass = 'secret';

$config->app_settings = new stdClass();
$config->app_settings->log_level    = 'debug';
$config->app_settings->log_location = '/tmp/debug.log';
$config->app_settings->log_rotate   = true;

// JWT settings
$config->jwt = new stdClass();
$config->jwt->issuer   = 'http://example.com';
$config->jwt->audience = 'http://example.com';
$config->jwt->max_hours = '1';
$config->jwt->max_minutes = '30';
$config->jwt->key = 'ice cream!';

# Debug settings
$config->debug = new \stdClass();
$config->debug->authUsers =  false;
