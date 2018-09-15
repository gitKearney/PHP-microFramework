<?php
namespace Main\Models;

trait dbConnectionTrait
{
    /**
     * returns a dns string to connect to a database for reading only
     * @param \stdClass $readConfigs
     * @return string
     */
    function getReadDatabaseDsn($readConfigs)
    {
        $dbType    = $readConfigs->type;

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
     * @param \stdClass $writeConfigs
     * @return string
     */
    function getWriteDatabaseDsn($writeConfigs)
    {
        $dbType    = $writeConfigs->type;

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

    /**
     * @param \stdClass $config
     * @return string
     */
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

        $readId  = $this->readConnectionId;
        $writeId = $this->writeConnectionId;

        switch ($mode) {
            case 'read':
                $dsnString = $this->getReadDatabaseDsn($config->$readId);
                $user      = $config->$readId->user;
                $pass      = $config->$readId->pass;

                break;
            case 'write':
                $dsnString = $this->getWriteDatabaseDsn($config->$writeId);
                $user      = $config->$writeId->user;
                $pass      = $config->$writeId->pass;

                break;
            default:
                throw new \Exception("invalid mode passed in: must be"
                    ." 'read', or 'write'");
        }

        if (strlen($dsnString) == 0 ) {
            throw new \Exception('invalid database connection');
        }

        try {
            logVar($dsnString, "DSN String: ");

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
