# error-reporter

1. Require package

```
composer require cube-agency/error-reporter
```

2. Update .env file with relevant API url and key

```
ERROR_REPORTER_API_URL=xxx
ERROR_REPORTER_API_KEY=yyy
```

3. Register service provider in `config/app.php` (for Laravel 5.4 and older )

```
'providers' => [


   CubeAgency\ErrorReporter\ErrorReporterServiceProvider::class
```

4. Bind exception handler in `bootstrap/app.php`
```
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    CubeAgency\ErrorReporter\Exceptions\Handler::class
);

```


5. Override package [config default values](src/config/error-reporter.php) by creating a config file in `config/error-reporter.php`
