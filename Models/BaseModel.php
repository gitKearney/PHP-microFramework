<?php

namespace Models;

use Models\dbConnectionTrait;
use Services\DebugLogger;

abstract class BaseModel
{
    use dbConnectionTrait;

    /**
     * @var DebugLogger
     */
    protected $debugLogger;

    abstract public function update(array $values);
    abstract public function insert($query, array $values);
    abstract public function select($query, array $values);

    /**
     * @param string $logFileName default null
     * @return $this
     */
    public function setDebugLogger($logFileName = null)
    {
        if (is_null($logFileName)) {
            $this->debugLogger = new DebugLogger;
        } else {
            $this->debugLogger = new DebugLogger($logFileName);
        }

        return $this;
    }
}
