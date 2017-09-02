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

    function getMySqlDsnString()
    {
        return 'mysql:host=127.0.0.1;port=3306;dbname=demo;charset=utf8';
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

    function readUserNameFromEnv()
    {
        return 'root';
    }

    function readPasswordFromEnv()
    {
        return 'password';
    }

    function readDbTypeFromEnv()
    {
        return 'mysql';
    }

    /**
     * @return \PDO | string
     * @throws \Exception
     */
    function getPdoConnection()
    {
        $dbType = $this->readDbTypeFromEnv();


        # TODO: remove this is testing only
        if (strlen($dbType) == 0) {
            $this->debugLogger->setMessage('DBTYPE is empty string')->logVariable('')->write();
            return '';
        }

        $dsnString = $this->getDatabaseDsn($dbType);

        if (strlen($dsnString) == 0 ) {
            $this->debugLogger->setMessage('DSN is empty string')->logVariable('')->write();
            throw new \Exception('invalid database connection');
        }

        try {
            $pdo = new \PDO($dsnString,
                $this->readUserNameFromEnv(),
                $this->readPasswordFromEnv()
            );

            # set the error level on our PDO object to not fail silently
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (\Exception $e) {
            $this->debugLogger->setMessage('FAILED TO GET CONNECTION')->logVariable($e->getMessage())->write();
        }
    }
}
