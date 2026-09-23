# febrysan/logcentral

Laravel package that reports exceptions to a central error-log API and serves a custom HTTP 500 page.

Supports Laravel 8–13 on PHP 8.1+.

## Install

```bash
composer require febrysan/logcentral
```

Until the package is on Packagist, add a path repository in the host app `composer.json` and require `@dev`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../logmonitor-package"
        }
    ]
}
```

Laravel package discovery registers `Febrysan\LogCentral\LogCentralServiceProvider`. No edit to `config/app.php` is required.

## Configuration

Copy env keys into the host app `.env`:

```env
LOGCENTRAL_ENABLED=true
LOGCENTRAL_ENDPOINT=http://localhost:8088/api/error-log-transactions
LOGCENTRAL_API_KEY=
LOGCENTRAL_TIMEOUT=3
```

Publish config and the 500 view (optional):

```bash
php artisan vendor:publish --tag=logcentral
```

Separate tags:

- `--tag=logcentral-config` — `config/logcentral.php`
- `--tag=logcentral-views` — `resources/views/vendor/logcentral`

The HTML 500 page reports the exception synchronously so the API can return a LOG ID for that response. There is no queue.

`LOGCENTRAL_API_KEY` is sent as `Authorization: Bearer …` and is required by LogMonitor. The body follows the LogMonitor contract: `occured_at`, `message`, `code`, `file`, `line`, `trace`, `url`, `method`, `ip_address`, `context`, `user_id`, `user_agent`. Empty `user_id` is sent as `-`. The 500 page shows `log_id` from the 201 response (`log_id` or `data.id`).
