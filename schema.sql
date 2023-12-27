-- MARIADB SCHEMA

-- THE PEOPLE TABLE
DROP TABLE IF EXISTS people;
CREATE TABLE people
(
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(32) NOT NULL,
    last_name VARCHAR(32) NOT NULL
);

INSERT INTO people
VALUES
    (2, 'Kenny', 'Peppercorn'),
    (4, 'Fred', 'Fennel'),
    (6, 'Peter', 'Pimento'),
    (8, 'Katy', 'Curry');

-- THE FOOD TABLE
DROP TABLE IF EXISTS lunch_items;
CREATE TABLE lunch_items
(
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) NOT NULL
);

INSERT INTO lunch_items
VALUES
    (10, 'apples'),
    (20, 'strawberries'),
    (30, 'potato chips'),
    (40, 'cheese puffs'),
    (50, 'carrot sticks'),
    (60, 'ham and cheese sandwich'),
    (70, 'veggie wrap'),
    (80, 'chocolate chip cookies'),
    (90, 'cake');

-- WEEKLY ORDERS
DROP TABLE IF EXISTS lunch_orders;
CREATE TABLE lunch_orders
(
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    weekday VARCHAR(12) NOT NULL,
    people_id TINYINT UNSIGNED NOT NULL
);

INSERT INTO lunch_orders
VALUES
    -- MR PEPPERCORNS ORDERS
    (11, 'MONDAY', 2),
    (12, 'TUESDAY', 2),
    (13, 'WEDNESDAY', 2),
    (14, 'THURSDAY', 2),
    (15, 'FRIDAY', 2),

    -- MR FENNEL'S ORDERS
    (21, 'MONDAY', 4),
    (22, 'TUESDAY', 4),
    (23, 'WEDNESDAY', 4),
    (24, 'THURSDAY', 4),
    (25, 'FRIDAY', 4),

    -- MR PIMENTO
    (31, 'MONDAY', 6),
    (33, 'WEDNESDAY', 6),
    (35, 'FRIDAY', 6),

    -- MS CURRY
    (41, 'MONDAY', 8),
    (42, 'TUESDAY', 8),
    (43, 'WEDNESDAY', 8),
    (44, 'THURSDAY', 8);


DROP TABLE IF EXISTS lunch_order_items;
CREATE TABLE lunch_order_items
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lunch_order_id TINYINT UNSIGNED NOT NULL,
    lunch_item_id TINYINT UNSIGNED NOT NULL
);

INSERT INTO lunch_order_items
VALUES
    -- MONDAY ORDERS
    (NULL, 11, 10), -- MR PEPPERCORN'S
    (NULL, 11, 70),
    (NULL, 11, 90),

    (NULL, 21, 10), -- MR FENNEL'S
    (NULL, 21, 30),
    (NULL, 21, 60),
    (NULL, 21, 90),

    (NULL, 31, 10), -- MR PIMENTO'S
    (NULL, 31, 40),
    (NULL, 31, 60),
    (NULL, 31, 90),

    (NULL, 41, 10), -- MS CURRY
    (NULL, 41, 20),
    (NULL, 41, 30),

    -- TUESDAY ORDERS
    (NULL, 12, 10), -- MR PEPPERCORN'S
    (NULL, 12, 20),
    (NULL, 12, 30),
    (NULL, 12, 90),

    (NULL, 22, 10),  -- MR FENNEL'S
    (NULL, 22, 30),
    (NULL, 22, 60),
    (NULL, 22, 90),

    (NULL, 42, 10), -- MS CURRY
    (NULL, 42, 20),
    (NULL, 42, 30),

    -- WEDNESDAY ORDERS
    (NULL, 13, 10),  -- MR PEPPERCORN'S
    (NULL, 13, 40),
    (NULL, 13, 60),

    (NULL, 23, 10), -- MR FENNEL'S
    (NULL, 23, 30),
    (NULL, 23, 60),
    (NULL, 23, 90),

    (NULL, 33, 20), -- MR PIMENTO'S
    (NULL, 33, 40),
    (NULL, 33, 60),
    (NULL, 33, 80),

    (NULL, 43, 20), -- MS CURRY
    (NULL, 43, 40),
    (NULL, 43, 60),

    -- THURSDAY ORDERS
    (NULL, 14, 50), -- MR PEPPERCORN'S
    (NULL, 14, 60),

    (NULL, 24, 10), -- MR FENNEL'S
    (NULL, 24, 30),
    (NULL, 24, 60),
    (NULL, 24, 90),

    (NULL, 44, 20), -- MS CURRY
    (NULL, 44, 40),
    (NULL, 44, 60),

    -- FRIDAY ORDERS
    (NULL, 15, 20), -- MR PEPPERCORN'S
    (NULL, 15, 50),
    (NULL, 15, 60),

    (NULL, 25, 10), -- MR FENNEL'S
    (NULL, 25, 30),
    (NULL, 25, 60),
    (NULL, 25, 90),

    (NULL, 35, 40), -- MR PIMENTO'S
    (NULL, 35, 70),
    (NULL, 35, 90);
