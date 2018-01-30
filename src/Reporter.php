<?php

namespace Holystix\ErrorReporter;

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

    /**
     * @var array
     */
    protected $superGlobals = [
        'get',
        'post',
        'cookie',
        'session',
        'files'
    ];

    /**
     * Reporter constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config    = $config;
        $this->sanitizer = resolve(Sanitizer::class);
    }

    /**
     * @param Exception $exception
     */
    public function reportException(Exception $exception)
    {
        if ($this->isReportingEnabled()) {
            $this->exception = $exception;
            $globalParams    = $this->getGlobalParams();
            $exceptionParams = $this->getExceptionParams();
            $requestParams   = $this->getRequestParams();
            $envParams       = $this->getEnvParams();
            $this->send(
                array_merge($exceptionParams, $requestParams, $globalParams, $envParams)
            );
        }
    }

    /**
     * @param array $data
     */
    protected function send($data)
    {
        $apiUrl          = array_get($this->config, 'api_url');
        $data['api_key'] = array_get($this->config, 'api_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            trigger_error($error, E_USER_WARNING);
        } else {
            curl_close($ch);
        }
    }

    /**
     * @return array
     */
    protected function getExceptionParams()
    {
        return [
            'message'    => $this->exception->getMessage(),
            'file'       => $this->exception->getFile(),
            'line'       => $this->exception->getLine(),
            'level'      => $this->exception->getCode(),
            'stackTrace' => $this->shouldReportStackTrace() ? $this->sanitizer->sanitize($this->exception->getTraceAsString()) : null,
        ];
    }

    /**
     * @return array
     */
    protected function getRequestParams()
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
            'http_cookie'       => $this->sanitizer->sanitize(array_get($_SERVER, 'HTTP_COOKIE')),
            'argv'              => $this->sanitizer->sanitize(array_get($_SERVER, 'argv'))
        ];

        return $data;
    }

    /**
     * @return array
     */
    protected function getGlobalParams()
    {
        $return = [];
        foreach ($this->superGlobals as $global) {
            $key          = 'data_' . $global;
            $var          = '_' . strtoupper($global);
            $value        = array_get($GLOBALS, $var);
            $return[$key] = $this->sanitizer->sanitize($value);
        }
        return $return;
    }

    protected function getEnvParams()
    {
        return [
            'environment' => getenv('APP_ENV'),
            'branch'      => $this->getDeployedBranch(),
            'revision'    => $this->getDeployedRevision()
        ];
    }

    protected function shouldReportStackTrace()
    {
        return (bool)array_get($this->config, 'report_stack_trace', true);
    }

    protected function isReportingEnabled()
    {
        return (bool)array_get($this->config, 'enabled', false);
    }

    protected function getDeployedBranch()
    {
        $basePath       = base_path();
        $branchFilePath = $basePath . '/BRANCH';
        return file_exists($branchFilePath) && is_readable($branchFilePath) ? trim(file_get_contents($branchFilePath)) : $this->getGitBranch();
    }

    protected function getGitBranch()
    {
        $basePath = base_path();
        $gitPath  = $basePath . '/.git';
        if (file_exists($gitPath)) {
            $command = 'cd ' . $basePath . ' && git branch';
            exec($command, $output, $return_var);
            if ($return_var === 0) {
                $output = preg_grep('/^\*/', $output);
                return trim(substr(reset($output), 1));
            }
        }
        return null;
    }

    protected function getDeployedRevision()
    {
        $basePath         = base_path();
        $revisionFilePath = $basePath . '/REVISION';
        return file_exists($revisionFilePath) && is_readable($revisionFilePath) ? trim(file_get_contents($revisionFilePath)) : $this->getGitRevision();
    }

    protected function getGitRevision()
    {
        $basePath = base_path();
        $gitPath  = $basePath . '/.git';
        if (file_exists($gitPath)) {
            $command = 'cd ' . $basePath . ' && git rev-parse HEAD';
            exec($command, $output, $return_var);
            return $return_var === 0 ? trim(reset($output)) : null;
        }
        return null;
    }
}
