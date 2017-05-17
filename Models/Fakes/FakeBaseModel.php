<?php

namespace Models\Fakes;

use Models\Fakes\dbConnectionTrait;
use Services\DebugLogger;

abstract class FakeBaseModel
{
    use fakeDbConnectionTrait;

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
