<?php

namespace Arbory\ErrorReporter;

use Exception;

class Reporter
{
    /** @var array */
    protected $config;

    /** @var Exception */
    protected $exception;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function reportException(Exception $exception)
    {
        $this->exception = $exception;
        $params          = $this->getExceptionParamsArray();
    }

    protected function getExceptionParamsArray()
    {
        return [
            'message'    => $this->exception->getMessage(),
            'file'       => $this->exception->getFile(),
            'line'       => $this->exception->getLine(),
            'level'      => $this->exception->getCode(),
            'stackTrace' => $this->exception->getTrace(),
        ];
    }
}
