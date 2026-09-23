<?php

namespace Febrysan\LogCentral\Tests;

use Febrysan\LogCentral\LogCentralServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LogCentralServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.debug', false);
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('logcentral.enabled', true);
        $app['config']->set('logcentral.endpoint', 'http://logcentral.test/api/error-log-transactions');
        $app['config']->set('logcentral.api_key', 'test-token');
        $app['config']->set('logcentral.timeout', 3);
        $app['config']->set('logcentral.dont_report', []);
    }

    protected function defineRoutes($router): void
    {
        $router->get('/__logcentral-smoke', function () {
            throw new \RuntimeException('logcentral smoke test');
        });
    }
}
