<?php

namespace Febrysan\LogCentral\Support;

use Febrysan\LogCentral\ErrorReporter;
use Febrysan\LogCentral\ReportContext;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RegistersExceptionHandling
{
    public function register(Application $app): void
    {
        if (! $app->bound(ExceptionHandler::class)) {
            return;
        }

        $handler = $app->make(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable(function (Throwable $e) use ($app): void {
                $app->make(ErrorReporter::class)->report($e);
            });
        }

        if (method_exists($handler, 'renderable')) {
            $handler->renderable(function (Throwable $e, $request) use ($app) {
                return $this->render($app, $e, $request);
            });
        }
    }

    private function render(Application $app, Throwable $e, $request)
    {
        if (! config('logcentral.enabled')) {
            return null;
        }

        if (config('app.debug')) {
            return null;
        }

        if (is_object($request) && method_exists($request, 'expectsJson') && $request->expectsJson()) {
            return null;
        }

        if ($this->isNonServerException($e)) {
            return null;
        }

        return response()->view(
            'logcentral::errors.500',
            ['logId' => $app->make(ReportContext::class)->logId()],
            500
        );
    }

    private function isNonServerException(Throwable $e): bool
    {
        if (interface_exists(HttpExceptionInterface::class) && $e instanceof HttpExceptionInterface) {
            return $e->getStatusCode() < 500;
        }

        $skip = [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Session\TokenMismatchException::class,
        ];

        foreach ($skip as $class) {
            if (class_exists($class) && $e instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
