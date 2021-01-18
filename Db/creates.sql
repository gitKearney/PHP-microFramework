CREATE DATABASE demo;

USE demo;

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
    roles ENUM('read', 'edit', 'create') NOT NULL,
    active ENUM('no', 'yes') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE users MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

-- create a products table
DROP TABLE IF EXISTS products;
CREATE TABLE products
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
DROP TABLE IF EXISTS transactions;
CREATE TABLE transactions
(
    transaction_id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET "utf8" COLLATE utf8_general_ci;

ALTER TABLE transactions MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE transactions ADD CONSTRAINT trans_user_id FOREIGN KEY (user_id) REFERENCES users(user_id);

-- create a transaction product table
DROP TABLE IF EXISTS transaction_products;
CREATE TABLE transaction_products
(
    transaction_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE transaction_products MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE transaction_products
ADD CONSTRAINT `fk_product_id` FOREIGN KEY (product_id) REFERENCES products (product_id)
ON DELETE CASCADE;

-- create a cart for each user
DROP TABLE IF EXISTS user_cart;
CREATE TABLE user_cart (
   user_id CHAR(36) NOT NULL,
   product_id CHAR(36) NOT NULL,
   created_at DATETIME NOT NULL DEFAULT NOW(),
   updated_at DATETIME NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE transaction_products MODIFY updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE user_cart ADD CONSTRAINT `cart_product_id` FOREIGN KEY (product_id) REFERENCES products(product_id);
