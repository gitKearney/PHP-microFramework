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
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE users MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

-- create a products table
DROP TABLE IF EXISTS demo.products;
CREATE TABLE demo.products
(
    product_id CHAR(36) PRIMARY KEY,
    title VARCHAR(32) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INTEGER UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE products MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

-- create a transaction history table
DROP TABLE IF EXISTS demo.transactions;
CREATE TABLE demo.transactions
(
    transaction_id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET "utf8" COLLATE utf8_general_ci;

ALTER TABLE transactions MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE demo.transaction_products
(
    transaction_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
);

DROP TABLE IF EXISTS user_cart;
CREATE TABLE user_cart (
   user_id CHAR(36) NOT NULL,
   product_id CHAR(36) NOT NULL,
   created_at DATETIME NOT NULL DEFAULT NOW(),
   updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- add foreign constraints to our tables
ALTER TABLE transaction_products MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE demo.transactions ADD CONSTRAINT FOREIGN KEY (user_id) REFERENCES demo.users(user_id);

ALTER TABLE demo.transaction_products
ADD CONSTRAINT FOREIGN KEY (product_id) REFERENCES products(product_id);

ALTER TABLE demo.transaction_products
ADD CONSTRAINT FOREIGN KEY (transaction_id) REFERENCES demo.transactions(transaction_id);

ALTER TABLE user_cart ADD CONSTRAINT FOREIGN KEY (product_id) REFERENCES products(product_id);

ALTER TABLE user_cart ADD CONSTRAINT FOREIGN KEY (product_id) REFERENCES products(product_id);
