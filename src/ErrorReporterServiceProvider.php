<?php

namespace Holystix\ErrorReporter;

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

        $this->app->singleton(Sanitizer::class, function ($app) {
            $sanitizer = new Sanitizer(
                config('error-reporter.sanitizer')
            );
            return $sanitizer;
        });

        $this->app->singleton(Reporter::class, function ($app) {
            $reporter = new Reporter(
                config('error-reporter.reporter')
            );
            return $reporter;
        });
    }
}
