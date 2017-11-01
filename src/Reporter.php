<?php

namespace Arbory\ErrorReporter;

use Exception;

class Reporter
{
    /** @var array */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function reportException(Exception $exception)
    {
    }
}
