<?php

namespace Febrysan\LogCentral;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorReporter
{
    public function __construct(
        private ReportContext $context
    ) {
    }

    public function report(Throwable $e): void
    {
        $this->context->setLogId(null);

        if (! config('logcentral.enabled')) {
            return;
        }

        if ($this->shouldSkip($e)) {
            return;
        }

        try {
            $request = Http::timeout((int) config('logcentral.timeout', 3))
                ->acceptJson();

            $apiKey = config('logcentral.api_key');

            if (is_string($apiKey) && $apiKey !== '') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post(
                (string) config('logcentral.endpoint'),
                $this->payload($e)
            );

            if ($response->successful()) {
                $this->context->setLogId($this->extractLogId($response->json()));
            }
        } catch (Throwable $ignored) {
            $this->context->setLogId(null);
        }
    }

    private function shouldSkip(Throwable $e): bool
    {
        if ($this->isNonServerException($e)) {
            return true;
        }

        foreach ((array) config('logcentral.dont_report', []) as $type) {
            if (is_string($type) && is_a($e, $type)) {
                return true;
            }
        }

        return false;
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

    /**
     * @return array<string, mixed>
     */
    private function payload(Throwable $e): array
    {
        $request = app()->bound('request') ? app('request') : null;
        $user = is_object($request) && method_exists($request, 'user')
            ? $request->user()
            : null;

        $message = $e->getMessage();
        $userId = is_object($user) && method_exists($user, 'getAuthIdentifier')
            ? (string) $user->getAuthIdentifier()
            : '';

        return [
            'occured_at' => now()->toIso8601String(),
            'message' => $message !== '' ? $message : '(empty)',
            'code' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $this->requestString($request, 'fullUrl', '-'),
            'method' => $this->requestString($request, 'method', 'CLI'),
            'ip_address' => $this->requestString($request, 'ip', '127.0.0.1'),
            'context' => [
                'exception' => $e::class,
                'environment' => app()->environment(),
                'app_name' => config('app.name'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
            'user_id' => $userId !== '' ? $userId : '-',
            'user_agent' => $this->requestString($request, 'userAgent', 'logcentral'),
        ];
    }

    /**
     * @param  mixed  $json
     */
    private function extractLogId($json): ?string
    {
        if (! is_array($json)) {
            return null;
        }

        $fromRoot = $this->stringifyId($json['log_id'] ?? null);

        if ($fromRoot !== null) {
            return $fromRoot;
        }

        $data = $json['data'] ?? null;

        if (! is_array($data)) {
            return null;
        }

        return $this->stringifyId($data['id'] ?? null);
    }

    private function requestString(mixed $request, string $method, string $fallback): string
    {
        if (! is_object($request) || ! method_exists($request, $method)) {
            return $fallback;
        }

        $value = $request->{$method}();

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    /**
     * @param  mixed  $value
     */
    private function stringifyId($value): ?string
    {
        if ($value === null || $value === '' || is_array($value)) {
            return null;
        }

        return (string) $value;
    }
}
