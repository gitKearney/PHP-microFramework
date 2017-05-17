<?php
namespace Models;

trait fakeDbConnectionTrait
{
    function getDatabaseDns($dbType)
    {
        # this should actually throw an error: the user should set the database
        # type either in the .env file, or by passing in something in the
        # controller

        switch($dbType) {
            case 'mysql':
                return $this->getMySqlDnsString();
            case 'postgres':
                return $this->getPostgreSqlDnsString();
            case 'sqlite':
                return $this->getSqLiteDnsString();
            default:
                # log the error
                return '';
        }
    }

    function getMySqlDnsString()
    {
        # TODO: change the IP of the host should match the IP of the server
        # that the MySQL server lives on.

        # Be sure to change the port if you use a non-standard port
        return 'mysql:host=127.0.0.1;port=3306;dbname=demo;charset=utf8';
    }

    function getPostgreSqlDnsString()
    {
        return 'pgsql:dbname=example;host=localhost';
    }

    function getMongoDnsString()
    {
        # TODO: PDO no longer supports Mongo, need to figure out how to use
        # MongoDB's own driver
        return '';
    }

    function getSqLiteDnsString()
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

        # The ONLY time dbType should be '' is in dev environments
        if (strlen($dbType) == 0) {
            $this->debugLogger->setMessage('DBTYPE is empty string')->logVariable('')->write();
            return '';
        }

        $dnsString = $this->getDatabaseDns($dbType);

        if (strlen($dnsString) == 0 ) {
            $this->debugLogger->setMessage('DNS is empty string')->logVariable('')->write();
            throw new \Exception('invalid database connection');
        } else {
           $this->debugLogger->setMessage('DNS string is ')->logVariable($dnsString)->write();
        }

        try {
            $pdo = new \PDO($dnsString,
                $this->readUserNameFromEnv(),
                $this->readPasswordFromEnv()
            );

            return $pdo;
        } catch (\Exception $e) {
            $this->debugLogger->enableLogging()->setMessage('FAILED TO GET CONNECTION')->logVariable($e->getMessage())->write();
        }
    }
}
