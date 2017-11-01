<?php

namespace Arbory\ErrorReporter;

class Sanitizer
{
    /** @var array */
    protected $config;
    protected $removedValueNotice = 'value_removed_by_leafError';

    /**
     * @var array
     */
    protected $sensitiveStringPatterns;

    public function __construct($config)
    {
        $this->config = $config;
        $this->setSensitiveStringPatterns();
    }

    protected function setSensitiveStringPatterns()
    {
        $identifiers = array_get($this->config, 'sensitive_string_identifiers');

        $this->sensitiveStringPatterns = array_map(function ($identifier) {
            return '/(?<=\b' . $identifier . '=)(.+)(\b)/U';
        }, $identifiers);
    }

    public function sanitizeString($string)
    {
        return preg_replace($this->sensitiveStringPatterns, $this->removedValueNotice, $string);
    }
}
