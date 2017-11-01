<?php

namespace Arbory\ErrorReporter;

use Exception;

class Reporter
{
    /** @var array */
    protected $config;

    /** @var Exception */
    protected $exception;

    protected $removedValueNotice = 'value_removed_by_leafError';

    /** @var string */
    protected $sessionCookieKey;

    public function __construct($config)
    {
        $this->config           = $config;
        $this->sessionCookieKey = config('session.cookie');
    }

    public function reportException(Exception $exception)
    {
        $this->exception = $exception;
        $exceptionParams = $this->getExceptionParamsArray();
        $requestParams   = $this->getRequestParamsArray();
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
            'http_cookie'       => $this->removeSensitiveDataFromString(
                array_get($_SERVER, 'HTTP_COOKIE'), [
                $this->sessionCookieKey,
                'XSRF-TOKEN'
            ])
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
        if (is_string($identifiers)) {
            $identifiers = [$identifiers];
        }

        $patterns = array_map(function ($identifier) {
            return '/(?<=\b' . $identifier . '=)(.+)(\b)/U';
        }, $identifiers);


        return preg_replace($patterns, $this->removedValueNotice, $string);
    }
}
