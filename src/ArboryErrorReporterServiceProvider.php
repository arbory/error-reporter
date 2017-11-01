<?php

namespace Arbory\ErrorReporter;

use Illuminate\Support\ServiceProvider;

class ErrorReporterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        require __DIR__ . '/Exceptions/Handler.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/error-reporter.php', 'error-reporter');
        $this->app->singleton(Reporter::class, function ($app) {

            $config   = config('error-reporter');
            $reporter = new Reporter($config);

            return $reporter;
        });
    }
}
