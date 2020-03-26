CREATE DATABASE demo;

USE demo;

-- create a user table
DROP TABLE IF EXISTS demo.users;
CREATE TABLE demo.users
(
  user_id CHAR(36) PRIMARY KEY,
  first_name VARCHAR(40) NOT NULL,
  last_name  VARCHAR(40) NOT NULL,
  upassword  VARCHAR(128) NOT NULL,
  email VARCHAR(64) NOT NULL,
  birthday DATE NOT NULL,
  roles ENUM('read', 'edit', 'create') NOT NULL,
  active ENUM('no', 'yes') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT NOW(),
  updated_at DATETIME NULL
);

-- create a products table
DROP TABLE IF EXISTS demo.products;
CREATE TABLE demo.products
(
    product_id CHAR(36) PRIMARY KEY,
    title VARCHAR(32) NOT NULL,
    price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
);

-- create a transaction history table
