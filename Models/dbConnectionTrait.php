<?php
namespace Main\Models;

trait dbConnectionTrait
{
    /**
     * returns a dns string to connect to a database for reading only
     * @param stdClass $readConfigs
     * @return string
     */
    function getReadDatabaseDsn($readConfigs)
    {
        $dbType    = $readConfigs->type;

        # this should actually throw an error: the user should set the database
        # type either in the .env file, or by passing in something in the
        # controller

        switch($dbType) {
            case 'mysql':
                $host = $readConfigs->host;
                $port = $readConfigs->port;
                $name = $readConfigs->name;
                return "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
            case 'postgres':
                return 'pgsql:dbname=example;host=localhost';
            case 'sqlite':
                return '';
            default:
                return '';
        }
    }

    /**
     * returns a dns string to connect to a database for writing only
     * @return string
     */
    function getWriteDatabaseDsn($writeConfigs)
    {
        $config    = getAppConfigSettings();
        $dbType    = $writeConfigs->type;

        # this should actually throw an error: the user should set the database
        # type either in the .env file, or by passing in something in the
        # controller

        switch($dbType) {
            case 'mysql':
                $host = $writeConfigs->host;
                $port = $writeConfigs->port;
                $name = $writeConfigs->name;
                return "mysql:host=$host;port=$port;dbname=$name;charset=utf8";
            case 'postgres':
                return 'pgsql:dbname=example;host=localhost';
            case 'sqlite':
                return '';
            default:
                return '';
        }
    }


    function getMongoDsnString($config)
    {
        # TODO: you have to use Mongo's own driver
        return '';
    }


    /**
     * @param string $mode - 'read' or 'write'
     * @return \PDO | string
     * @throws \Exception
     */
    function getPdoConnection($mode)
    {
        $pdo       = null;
        $config    = getAppConfigSettings();

        switch ($mode) {
            case 'read':
                $dsnString = $this->getReadDatabaseDsn($config->read_database);
                $user      = $config->read_database->user;
                $pass      = $config->read_database->pass;
                break;
            case 'write':
                $dsnString = $this->getWriteDatabaseDsn($config->write_database);
                $user      = $config->write_database->user;
                $pass      = $config->write_database->pass;
                break;
            default:
                throw new \Exception("invalid mode passed in: must be"
                    ." 'read', or 'write'");
        }

        if (strlen($dsnString) == 0 ) {
            logVar('DSN is empty string');
            throw new \Exception('invalid database connection');
        }

        try {
            $pdo = new \PDO($dsnString, $user, $pass);

            # set the error level on our PDO object to not fail silently
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            logVar('FAILED TO GET CONNECTION. ');
            logVar($e->getMessage());
        }

        return $pdo;
    }
}
