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
        //
    }
}
