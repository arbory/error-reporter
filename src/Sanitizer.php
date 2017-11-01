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

    /**
     * Sanitizer constructor.
     * @param array $config
     */
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

    /**
     * @return array
     */
    protected function getSensitiveStringPatterns()
    {
        if (!isset($this->sensitiveStringPatterns)) {
            $this->setSensitiveStringPatterns();
        }
        return $this->sensitiveStringPatterns;
    }

    /**
     * @return string
     */
    protected function getRemovedValueNotification()
    {
        if (!$this->removeValueNotification) {
            $this->removeValueNotification = array_get($this->config, 'removed_value_notification');
        }
        return $this->removeValueNotification;
    }

    /**
     * @param array|string $value
     * @return array|string
     */
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
     * @param string $string
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

    /**
     * @param array $array
     * @return string
     */
    protected function sanitizeArray($array)
    {
        $array = $this->sanitizeArrayValues($array);
        return $this->sanitizeString(print_r($array, true));
    }

    /**
     * @param array $array
     * @return array
     */
    protected function sanitizeArrayValues($array)
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

    /**
     * @param string $key
     * @return bool
     */
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

    /**
     * @return array
     */
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
