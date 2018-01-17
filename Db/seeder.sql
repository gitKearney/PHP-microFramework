# PLEASE READ! You may learn something.

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
GRANT DELETE, INSERT, UPDATE ON demo.* TO 'read_user'@'%';

-- *OPTIONAL* a super user for the database only. Has essentially root permissions
-- but, only to 1 specific database. 
-- Again, this user can only log into the MySQL instance from a remote host.
-- Any attempt to log in from local host will return an error
CREATE USER 'demo_superuser'@'%' IDENTIFIED BY 'super_secret';

-- The 'DROP' permission is needed to truncate tables
GRANT ALTER, CREATE, DELETE, DROP, INDEX, INSERT, REFERENCES, SELECT, UPDATE 
ON demo.* to 'demo_superuser'@'%';

-- After adding a new user, run this command
FLUSH PRIVILEGES;


/*** COOL MySQL DEBUGGING TRICKS ***/

####################################
### TURN ON LOGGING OF EVERY SQL ###
####################################
SET GLOBAL log_output = "FILE";
SET GLOBAL general_log_file = "/tmp/mysql.logfile.log";
SET GLOBAL general_log = 'ON';

### ALLOW REMOTE ACCESS ###
###########################
# On Ubuntu 
   # open the file: /etc/mysql/mysql.conf.d/mysqld.cnf
   # in the the [mysql] section change bind-address to your server's IP address
   # sudo /etc/init.d/mysqld restart

INSERT INTO users
(
    user_id, first_name, last_name,
    upassword, 
    email, birthday, roles, 
    created_at, updated_at
)
VALUES 
(
    '12345678-1234-1234-1234-123456789abc', 'Alex', 'Hamilton',
    '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
    'alex.hamilton@example.com', '2001-01-11', 'create',
    '2017-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789abd', 'Tommy', 'Jefferson',
    '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
    'tom.jefferson@example.com', '2001-04-13', 'edit',
    '2015-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789abe', 'George', 'Washington',
    '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
    'g.w@example.com', '2001-12-14', 'read',
    '2016-12-31', NULL
),
(
    '12345678-1234-1234-1234-123456789abf', 'Aaron', 'Burr',
    '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
    'aaron.burr@example.com', '2001-02-06', 'edit',
    '2015-12-31', NULL
);

-- if no role is set, it defaults to the first role
INSERT INTO users
(
    user_id, first_name, last_name,
    upassword, 
    email, birthday, 
    created_at, updated_at
)
VALUES (
    '12345678-1234-1234-1234-123456789ac0', 'Franklin', 'Pierce',
    '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
    'frankie.p@example.com', '2001-04-08',
    '2015-12-31', NULL
);

-- test to make sure read-user can't insert record
-- INSERT INTO users
-- (
--     user_id, first_name, last_name,
--     upassword, 
--     email, birthday, 
--     created_at, updated_at
-- )
-- VALUES (
--     '12345678-1234-1234-1234-123456789ac1', 'John', 'Adams',
--     '$2y$10$vSyWZeSd5ypiFxRPo4EvF.78aZgRlEsZUt8iYThvqnW.Zi103Pt2i',
--     'j.a@example.com', '2001-04-08',
--     '2015-12-31', NULL
-- );
