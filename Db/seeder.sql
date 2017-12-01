INSERT INTO users 
(
    user_id, first_name, last_name, 
    upassword, 
    email ,birthday
)
VALUES
(
    '12345678-1234-1234-1234-123456789abc', 'Andrew', 'Jackson',
    '$2y$10$N09HTpUkN90nwO8QDhZTUuyOcwHuVaKneHDDv7C/z7d9raMiWs1vy',
    'andrew.jackson@example.com', '2010-10-31'
),
(
    '12345678-1235-1234-1234-123456789abc', 'Alexander', 'Hamilton',
    '$2y$10$N09HTpUkN90nwO8QDhZTUuyOcwHuVaKneHDDv7C/z7d9raMiWs1vy',
    'alexH@example.com', '2000-02-29'
),
(
    '12345678-1236-1234-1234-123456789abc', 'Ben', 'Franklin',
    '$2y$10$N09HTpUkN90nwO8QDhZTUuyOcwHuVaKneHDDv7C/z7d9raMiWs1vy',
    'bf@example.com', '2000-10-01'
),
(
    '12345678-1237-1234-1234-123456789abc', 'Richard', 'Woosley',
    '$2y$10$N09HTpUkN90nwO8QDhZTUuyOcwHuVaKneHDDv7C/z7d9raMiWs1vy',
    'woosley@example.com', '2012-06-04'
);
