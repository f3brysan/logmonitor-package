<?php

namespace Febrysan\LogCentral;

class ReportContext
{
    private const ATTRIBUTE = 'logcentral.log_id';

    private ?string $fallbackLogId = null;

    public function setLogId(?string $logId): void
    {
        $this->fallbackLogId = $logId;

        $request = $this->currentRequest();

        if ($request !== null) {
            $request->attributes->set(self::ATTRIBUTE, $logId);
        }
    }

    public function logId(): ?string
    {
        $request = $this->currentRequest();

        if ($request !== null) {
            $value = $request->attributes->get(self::ATTRIBUTE);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return $this->fallbackLogId !== null && $this->fallbackLogId !== ''
            ? $this->fallbackLogId
            : null;
    }

    private function currentRequest(): ?object
    {
        if (! function_exists('app') || ! app()->bound('request')) {
            return null;
        }

        $request = app('request');

        return is_object($request) && isset($request->attributes)
            ? $request
            : null;
    }
}
