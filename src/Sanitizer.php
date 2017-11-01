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

    /**
     * @var array
     */
    protected $sensitiveKeyPatterns;

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

    public function sanitize($value)
    {
        if (is_string($value)) {
            return $this->sanitizeString($value);
        } elseif (is_array($value)) {
            return $this->sanitizeArray($value);
        }
        return $value;
    }

    /**
     * @param $string
     * @return string
     */
    protected function sanitizeString($string)
    {
        return preg_replace(
            $this->getSensitiveStringPatterns(),
            $this->getRemovedValueNotification(),
            $string
        );
    }

    protected function sanitizeArray($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $value) {
            if ($this->isSensitiveArrayKey($key)) {
                $array[$key] = $this->getRemovedValueNotification();
                continue;
            }

            if (is_array($value)) {
                $array[$key] = $this->sanitizeArray($value);
            }
        }

        return $array;
    }

    protected function isSensitiveArrayKey($key)
    {
        $patterns = $this->getSensitiveKeyPatterns();

        foreach ($patterns as $pattern) {
            if (preg_match('/^' . $pattern . '$/i', $key)) {
                return true;
            }
        }
        return false;
    }

    protected function getSensitiveKeyPatterns()
    {
        if (!$this->sensitiveKeyPatterns) {
            $this->sensitiveKeyPatterns = array_merge(
                array_get($this->config, 'sensitive_key_patterns'),
                array_get($this->config, 'sensitive_string_identifiers')
            );
        }
        return $this->sensitiveKeyPatterns;
    }
}
