<?php

/**
 * Save your configuration settings here. This file SHOULD NOT be committed to
 * your repo.
 *
 * This is much better than using a .env file because it requires no overhead
 * of opening a file, reading it line by line, putting the value into 3
 * seperate places (evn, $_ENV, and $_SERVER) then closing the file.
 */
$config = new \stdClass();

// If you only have 1 database connection, then use this one and delete the
// others
$config->read_database = new stdClass();
$config->read_database->type = 'mysql';
$config->read_database->name = 'demo';
$config->read_database->port = '3306';
$config->read_database->host = '127.0.0.1';
$config->read_database->user = 'root';
$config->read_database->pass = 'password';

$config->write_database = new stdClass();
$config->write_database->type = 'mysql';
$config->write_database->name = 'demo';
$config->write_database->port = '3306';
$config->write_database->host = '127.0.0.1';
$config->write_database->user = 'root';
$config->write_database->pass = 'password';

$config->memory_database = new stdClass();
$config->memory_database->type = 'memory';
$config->memory_database->name = 'demo_memory';
$config->memory_database->port = '3306';
$config->memory_database->host = '127.0.0.1';
$config->memory_database->user = 'memory_user';
$config->memory_database->pass = 'secret';

$config->redis = new stdClass();
$config->redis->type = 'redis';
$config->redis->name = 'demo_redis';
$config->redis->port = '3000';
$config->redis->host = '127.0.0.1';
$config->redis->user = 'redis_user';
$config->redis->pass = 'secret';

$config->app_settings = new stdClass();
$config->app_settings->log_level    = 'debug';
$config->app_settings->log_location = '/tmp/debug.log';
$config->app_settings->log_rotate   = true;

# JWT settings
$config->jwt = new stdClass();

$config->jwt->issuer   = 'http://example.com';
$config->jwt->audience = 'http://example.com';
$config->jwt->max_hours = '1';
$config->jwt->max_minutes = '30';

