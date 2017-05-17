<?php
namespace Services;

use Services\DebugLogger;

class BaseService
{
    /**
     * @var DebugLogger
     */
    protected $debugLogger;

    /**
     * @param string $logFileName default null
     * @return $this
     */
    public function setDebugLogger($logFileName = '/tmp/php.debug.log')
    {
        if (is_null($logFileName)) {
            # handle the case where someone passes in null
            $this->debugLogger = new DebugLogger;
        } else {
            $this->debugLogger = new DebugLogger($logFileName);
        }

        return $this;
    }
}
