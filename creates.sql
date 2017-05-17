-- create a database user, so we're not using root all the time

-- grant access to remote root
-- sudo vim /etc/mysql/mysql.conf.d/mysqld.cnf
--    change bind-address from 127.0.0.1 to 0.0.0.0

-- GRANT ALL PRIVILEGES ON demo.* to 'root'@'%' IDENTIFIED BY 'password';
-- FLUSH PRIVILEGES

-- create a user table

DROP TABLE IF EXISTS users;
CREATE TABLE users
(
  user_id CHAR(36) PRIMARY KEY,
  first_name VARCHAR(40) NOT NULL,
  last_name  VARCHAR(40) NOT NULL,
  birthday DATE NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

