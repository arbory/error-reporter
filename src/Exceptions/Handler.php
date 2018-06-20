<?php

namespace CubeAgency\ErrorReporter\Exceptions;

use Exception;
use CubeAgency\ErrorReporter\Reporter;
use Illuminate\Auth\AuthenticationException;
use App\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldReport($exception)) {
            /** @var Reporter $reporter */
            $reporter = resolve(Reporter::class);
            $reporter->reportException($exception);
        }

        parent::report($exception);
    }
}
