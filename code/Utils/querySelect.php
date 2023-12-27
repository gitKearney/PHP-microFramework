<?php

/**
 * @return string
 */
function createDSN(): string
{
    $host = 'maria10_6';
    $port = '3306';
    $name = 'demo';
    return "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
}

/**
 * @throws Exception
 */
function getPdoConnection(): PDO
{
    $dsnString = createDSN();
    $user      = 'demo_user';
    $pass      = 'my_cool_secret';

    if (strlen($dsnString) == 0 ) {
        throw new Exception('invalid database connection');
    }

    $pdo = new PDO($dsnString, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

/**
 * @throws Exception
 */
function select($query, array $params): array
{
    $results = [];

    # error gets thrown here
    $pdo       = getPdoConnection();
    $statement = $pdo->prepare($query);
    $resultSet = $statement->execute($params);

    if ($resultSet === false) {
        throw new Exception('Error Finding Records', 500);
    }

    while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $results[] = $row;
    }

    if (count($results) == 1) {
        $results = $results[0];
    }

    return $results;
}