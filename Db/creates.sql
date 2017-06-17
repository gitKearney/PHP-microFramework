-- create a database user, so we're not using root all the time

-- create a user table
DROP TABLE IF EXISTS users;
CREATE TABLE users
(
  user_id CHAR(36) PRIMARY KEY,
  first_name VARCHAR(40) NOT NULL,
  last_name  VARCHAR(40) NOT NULL,
  birthday DATE NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);
