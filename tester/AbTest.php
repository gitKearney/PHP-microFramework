<?php

class Logger
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $loggingMode;

    /**
     * @var string
     */
    private $message;

    /**
     * logger constructor.
     */
    public function __construct()
    {
        $this->filePath = '/tmp/test.log';
        $this->loggingMode = false;
        $this->message = '';
    }

    /**
     * @return $this
     */
    public function enableLogging()
    {
        $this->loggingMode = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableLogging()
    {
        $this->loggingMode = false;
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message .= $message;
        return $this;
    }

    /**
     * @return $this
     */
    public function write()
    {
        # add a new newline character
        $this->message .= PHP_EOL;

        file_put_contents($this->filePath, $this->message, FILE_APPEND);
        return $this;
    }
}


abstract class AbClass
{
    /**
     * @var logger
     */
    protected $logger;

    /**
     * @return void
     */
    abstract public function printMe();

    /**
     * @return $this
     */
    public function createLogger()
    {
        $this->logger = new Logger;

        return $this;
    }
}

class ConClass extends AbClass
{
    public function __construct()
    {
        ;
    }

    public function printMe()
    {
        $this->logger->enableLogging()->setMessage('me!')->write();
    }
}

$me = new ConClass;

$me->createLogger()->printMe();
