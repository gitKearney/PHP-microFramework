<?php

namespace Services;

/**
 * use this for local debugging.
 * It's not an alternative to Monolog, just something to be used for
 * local debugging
 *
 * The way it works is
 * $debugLogger = new \Services\DebugLogger;
 * $debugLogger->enableLogging();
 * $debugLogger->setMessage("some message")->logVariable($someVar)->write();
 *
 * The wonderful thing about this is, you can disable logging, and leave the
 * log statements in, without effecting code
 *
 * TODO: change to a trait
 */
class DebugLogger
{
    /**
     * @var string
     */
    private $logFileName;

    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var message
     */
    private $message;

    /**
     * @var string
     */
    private $stringVar;

    /**
     * @param string $logFileName
     */
    public function __construct($logFileName = '/tmp/php.debug.log')
    {
        $this->logFileName = $logFileName;
        $this->message = "";
        $this->stringVar = "";
    }

    /**
     * @return $this
     */
    public function enableLogging()
    {
        $this->logLevel = 1;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableLogging()
    {
        $this->logLevel = 0;
        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    private function logBoolean($value)
    {
        if ($value) {
            return "true";
        }

        return "false";
    }

    /**
     * @param $value
     * @return string
     */
    private function logArray($value)
    {
        return print_r($value, true);
    }

    /**
     * @param float $value
     * @return string
     */
    private function logFloat($value)
    {
        return strval($value);
    }

    /**
     * translates a null variable so it shows up as "NULL"
     * @return string
     */
    private function logNull()
    {
        return "NULL";
    }

    /**
     * @param mixed $var
     * @return $this
     */
    public function logVariable($var)
    {
        if (is_null($var)) {
            $this->stringVar = $this->logNull();
            return $this;
        }

        if (is_array($var)) {
            $this->stringVar = $this->logArray($var);
            return $this;
        }

        if (is_bool($var)) {
            $this->stringVar = $this->logBoolean($var);
            return $this;
        }

        if (is_int($var) || is_float($var)) {
            $this->stringVar = $this->logFloat($var);
            return $this;
        }

        if (is_object($var)) {
            $this->stringVar = $this->logArray($var);
            return $this;
        }

        # otherwise, assume it's a string
        $this->stringVar = $var;

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message='')
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return void
     */
    public function write()
    {
        if (! $this->logLevel) {
            return;
        }

        $outstring = $this->message." ".$this->stringVar.PHP_EOL;
        file_put_contents($this->logFileName, $outstring, FILE_APPEND);
    }
}
