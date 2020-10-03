

-- You should *ALWAYS* create a read user and write user. They should never
-- have drop permission, this is reserved for the root user only!

-- If you want to drop, write the scripts manually and run them on the server.
-- If you don't know SQL, you shouldn't be running anything that modifies your
-- schema

-- Create a read only user. This user will log in from an external server.
-- If you attempt to connect to the MySQL instance from the server it's running on,
-- you will not be able to connect. This create prevents localhost from logging in
CREATE USER 'read_user'@'%' IDENTIFIED BY 'secret';

-- Grant the read only user SELECT permission. Nothing else
GRANT SELECT ON demo.* TO 'read_user'@'%';

-- Create a write only user. This user will log in from an external server.
-- If you attempt to connect to the MySQL instance from the server it's running on,
-- you will not be able to connect. This create prevents localhost from logging in
CREATE USER 'write_user'@'%' IDENTIFIED BY 'secret';

-- Grant the write only user DELETE, UPDATE, INSERT permission. Nothing else
-- SELECT permission is needed to run update queries because the WHERE clause
-- performs a select
GRANT SELECT, DELETE, INSERT, UPDATE ON demo.* TO 'write_user'@'%';

-- *OPTIONAL* create a super user for 1 database only. This user can only log
-- into the MySQL instance from a remote host. Any attempt to log in from
-- localhost will return an error
CREATE USER 'demo_superuser'@'%' IDENTIFIED BY 'super_secret';

-- The 'DROP' permission is needed to truncate tables
GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, REFERENCES, SELECT, UPDATE
ON demo.* to 'demo_superuser'@'%';

-- After adding a new user, run this command
FLUSH PRIVILEGES;

-- Edit the /etc/mysql/mysql.conf.d/mysqld.cnf file
-- change the line bind-address=127.0.0.1 to bind-address=0.0.0.0
-- then restart MySQL server: sudo service mysql restart

-- now you can log into the MySQL server using the following command
-- mysql --user=demo_superuser --password=super_secret --database=demo --port=3306 --host=10.0.2.15

/*** COOL MySQL DEBUGGING TRICKS ***/

####################################
### TURN ON LOGGING OF EVERY SQL ###
####################################
SET GLOBAL log_output = "FILE";
SET GLOBAL general_log_file = "/tmp/mysql.logfile.log";
SET GLOBAL general_log = 'ON';

###########################
## SAMPLE USERS          ##
###########################

-- the password for the following users is "#!ABC123"
INSERT INTO users
(
    user_id, first_name, last_name,
    upassword,
    email, birthday, roles, active,
    created_at, updated_at
)
VALUES
(
    '12345678-1234-1234-1234-123456789000', 'Johnny', 'Adams',
    '$argon2id$v=19$m=1024,t=2,p=2$QTk3aDVpb1VHVVZYQU11WA$ccZGZdUdHKue5ovOdiOkn9TYEJ3i3lghGEx4kSz3Syk',
    'alex.hamilton@example.com', '2001-01-11', 'create', 'yes',
    '2017-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789001', 'Tommy', 'Jefferson',
    '$argon2id$v=19$m=1024,t=2,p=2$QTk3aDVpb1VHVVZYQU11WA$ccZGZdUdHKue5ovOdiOkn9TYEJ3i3lghGEx4kSz3Syk',
    'tom.jefferson@example.com', '2001-04-13', 'edit', 'yes',
    '2015-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789002', 'G Dog', 'Washington',
    '$argon2id$v=19$m=1024,t=2,p=2$QTk3aDVpb1VHVVZYQU11WA$ccZGZdUdHKue5ovOdiOkn9TYEJ3i3lghGEx4kSz3Syk',
    'g.w@example.com', '2001-12-14', 'read', 'yes',
    '2016-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789003', 'Aaron', 'Burr',
    '$argon2id$v=19$m=1024,t=2,p=2$QTk3aDVpb1VHVVZYQU11WA$ccZGZdUdHKue5ovOdiOkn9TYEJ3i3lghGEx4kSz3Syk',
    'aaron.burr@example.com', '2001-02-06', 'edit', 'yes',
    '2015-12-31', NULL
);

-- if no role is set, it defaults to the first role
INSERT INTO users
(
    user_id, first_name, last_name,
    upassword,
    email, created_at, active,
    birthday, roles
)
VALUES (
    '12345678-1234-1234-1234-123456789abc', 'Frank', 'Pierce',
    '$argon2id$v=19$m=1024,t=2,p=2$QTk3aDVpb1VHVVZYQU11WA$ccZGZdUdHKue5ovOdiOkn9TYEJ3i3lghGEx4kSz3Syk',
    'frankie.p@example.com', '2001-04-08', 'yes',
    '2015-12-31', 'edit'
);

-- default products
INSERT INTO products
(product_id, title, price, quantity, created_at, updated_at)
VALUES
('12345678-1234-1234-1234-123456789000', 'Red Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789001', 'Orange Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789002', 'Yellow Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789003', 'Green Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789004', 'Blue Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789005', 'Indigo Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789006', 'Violet Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789007', 'White Socks', 5.99, 25, NOW(), NULL),
('12345678-1234-1234-1234-123456789008', 'Black Socks', 5.99, 25, NOW(), NULL);
