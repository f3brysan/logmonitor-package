<?php

namespace Febrysan\LogCentral;

use Illuminate\Support\ServiceProvider;
use Febrysan\LogCentral\Support\RegistersExceptionHandling;

class LogCentralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/logcentral.php',
            'logcentral'
        );

        $this->app->singleton(ReportContext::class);
        $this->app->singleton(ErrorReporter::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'logcentral'
        );

        (new RegistersExceptionHandling)->register($this->app);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/logcentral.php' => config_path('logcentral.php'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/logcentral'),
            ], 'logcentral');

            $this->publishes([
                __DIR__.'/../config/logcentral.php' => config_path('logcentral.php'),
            ], 'logcentral-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/logcentral'),
            ], 'logcentral-views');
        }
    }
}
