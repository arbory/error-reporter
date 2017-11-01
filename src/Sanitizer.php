<?php

namespace Arbory\ErrorReporter;

class Sanitizer
{
    /** @var array */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
}
