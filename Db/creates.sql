CREATE DATABASE demo;

USE demo;

-- create a database user, so we're not using root all the time
CREATE USER 'someuser'@'localhost' IDENTIFIED BY 'password';
GRANT ALTER, INSERT, SELECT, CREATE, DELETE, UPDATE, DROP ON demo.* to 'someuser'@'localhost';
FLUSH PRIVILEGES ;

-- create a user table
DROP TABLE IF EXISTS users;
CREATE TABLE users
(
  user_id CHAR(36) PRIMARY KEY,
  first_name VARCHAR(40) NOT NULL,
  last_name  VARCHAR(40) NOT NULL,
  upassword  VARCHAR(128) NOT NULL,
  email VARCHAR(64) NOT NULL,
  birthday DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT NOW(),
  updated_at DATETIME NULL
);
