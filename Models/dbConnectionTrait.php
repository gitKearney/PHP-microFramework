<?php
namespace Main\Models;

trait dbConnectionTrait
{
    function getDatabaseDsn($dbType)
    {
        # this should actually throw an error: the user should set the database
        # type either in the .env file, or by passing in something in the
        # controller

        switch($dbType) {
            case 'mysql':
                return $this->getMySqlDsnString();
            case 'postgres':
                return $this->getPostgreSqlDsnString();
            case 'sqlite':
                return $this->getSqLiteDsnString();
            default:
                # log the error
                return '';
        }
    }

    /**
     *
     * @return string
     */
    function getMySqlDsnString()
    {
        $host = getenv('DATABASE_HOST');
        $port = getenv('DATABASE_PORT');
        $name = getenv('DATABASE_NAME');

        return "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
    }

    function getPostgreSqlDsnString()
    {
        return 'pgsql:dbname=example;host=localhost';
    }

    function getMongoDsnString()
    {
        # TODO: you have to use Mongo's own driver
        return '';
    }

    function getSqLiteDsnString()
    {
        return '';
    }

    /**
     * This is supposed to read from some environment variable
     * @return string
     */
    function readUserNameFromEnv()
    {
        return getenv('DATABASE_USER', true);
    }

    function readPasswordFromEnv()
    {
        return getenv('DATABASE_PASS', true);
    }

    function readDbTypeFromEnv()
    {
        return getenv('DATABASE_TYPE', true);
    }

    /**
     * @return \PDO | string
     * @throws \Exception
     */
    function getPdoConnection()
    {
        $pdo       = null; 
        $dbType    = $this->readDbTypeFromEnv();
        $dsnString = $this->getDatabaseDsn($dbType);

        if (strlen($dsnString) == 0 ) {
            logVar('DSN is empty string');
            throw new \Exception('invalid database connection');
        }

        try {
            $pdo = new \PDO($dsnString,
                $this->readUserNameFromEnv(),
                $this->readPasswordFromEnv()
            );

            # set the error level on our PDO object to not fail silently
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            logVar('FAILED TO GET CONNECTION. ');
            logVar($e->getMessage());
        }

        return $pdo;
    }
}
