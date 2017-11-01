<?php

namespace Arbory\ErrorReporter;

class Sanitizer
{
    /**
     * @var array
     * */
    protected $config;

    /**
     * @var string
     */
    protected $removeValueNotification;

    /**
     * @var array
     */
    protected $sensitiveStringPatterns;

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function setSensitiveStringPatterns()
    {
        $identifiers = array_get($this->config, 'sensitive_string_identifiers');

        $this->sensitiveStringPatterns = array_map(function ($identifier) {
            return '/(?<=\b' . $identifier . '=)(.+)(\b)/U';
        }, $identifiers);
    }

    protected function getSensitiveStringPatterns()
    {
        if (!isset($this->sensitiveStringPatterns)) {
            $this->setSensitiveStringPatterns();
        }
        return $this->sensitiveStringPatterns;
    }

    protected function getRemovedValueNotification()
    {
        if (!$this->removeValueNotification) {
            $this->removeValueNotification = array_get($this->config, 'removed_value_notification');
        }
        return $this->removeValueNotification;
    }

    public function sanitizeString($string)
    {
        return preg_replace(
            $this->getSensitiveStringPatterns(),
            $this->getRemovedValueNotification(),
            $string
        );
    }
}
