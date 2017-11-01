<?php

namespace Arbory\ErrorReporter;

use Exception;

class Reporter
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @var Sanitizer
     */
    protected $sanitizer;

    protected $superGlobals = [
        'GET',
        'POST',
        'COOKIE',
        'SESSION',
        'FILES'
    ];

    public function __construct($config)
    {
        $this->config    = $config;
        $this->sanitizer = resolve(Sanitizer::class);
    }

    public function reportException(Exception $exception)
    {
        $this->exception = $exception;
        $exceptionParams = $this->getExceptionParamsArray();
        $requestParams   = $this->getRequestParamsArray();
        $globalParams    = $this->getGlobalParamsArray();
        dd($requestParams);
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

    protected function getRequestParamsArray()
    {
        $data = [
            'user_ip'           => array_get($_SERVER, 'REMOTE_ADDR'),
            'user_forwarded_ip' => array_get($_SERVER, 'HTTP_X_FORWARDED_FOR'),
            'http_host'         => array_get($_SERVER, 'HTTP_HOST'),
            'request_uri'       => array_get($_SERVER, 'REQUEST_URI'),
            'query_string'      => array_get($_SERVER, 'QUERY_STRING'),
            'request_method'    => array_get($_SERVER, 'REQUEST_METHOD'),
            'http_referer'      => array_get($_SERVER, 'HTTP_REFERER'),
            'user_agent'        => array_get($_SERVER, 'HTTP_REFERER'),
            'http_content_type' => array_get($_SERVER, 'CONTENT_TYPE'),
            'http_cookie'       => $this->sanitizer->sanitizeString(array_get($_SERVER, 'HTTP_COOKIE'))
        ];

        return $data;
    }

    /**
     * @param string       $string
     * @param string|array $identifiers
     * @return string
     */
    protected function removeSensitiveDataFromString($string, $identifiers)
    {
    }

    protected function getGlobalParamsArray()
    {
        $return = [];
        foreach ($this->superGlobals as $global) {
            $key          = 'data_' . $global;
            $var          = '_' . strtoupper($global);
            $value        = array_get($GLOBALS, $var);
            $return[$key] = $this->removeSensitiveValues($value);
        }
        return $return;
    }

    protected function removeSensitiveValues($value)
    {
        // at first remove all array values with sensitive keys
        if (is_array($value)) {
            $value = self::removeSensitiveArrayValues($value);
        }
        return $value;
    }

    protected function removeSensitiveArrayValues($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (self::isSensitiveArrayKey($key)) {
                    $array[$key] = $this->removedValueNotice;
                    continue;
                }

                if (is_array($value)) {
                    $array[$key] = self::removeSensitiveArrayValues($value);
                }
            }
        }

        return $array;
    }

    protected function isSensitiveArrayKey($key)
    {
        $patterns = $this->listSensitiveKeyPatterns();

        foreach ($patterns as $pattern) {
            if (preg_match('/^' . $pattern . '$/i', $key)) {
                return true;
            }
        }
        return false;
    }

    protected function listSensitiveKeyPatterns()
    {
        $patterns = [
            '(\S*)password(\S*).*'
        ];

        if (!empty($this->sessionCookieName)) {
            $patterns[] = $this->sessionCookieName;
        }

        return $patterns;
    }
}
