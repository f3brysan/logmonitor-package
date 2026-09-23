# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-23

First stable release. Reports Laravel exceptions to a LogMonitor API and replaces the HTML 500 page with a safe view plus LOG ID.

### Added

- Composer package `febrysan/logcentral` (`Febrysan\LogCentral`) with Laravel auto-discovery
- Sync `POST /api/error-log-transactions` using Bearer `LOGCENTRAL_API_KEY` (no queue)
- HTML 500 view when `APP_DEBUG=false`: “Terjadi Kesalahan” and `LOG ID` from `log_id` or `data.id`
- Skip JSON responses, 4xx/auth/validation, and `dont_report` types
- Fail-safe reporter: API or `$request->user()` failures do not break the error page
- Config/env: `LOGCENTRAL_ENABLED`, `LOGCENTRAL_ENDPOINT`, `LOGCENTRAL_API_KEY`, `LOGCENTRAL_TIMEOUT`
- Testbench tests and GitHub Actions matrix for Laravel 8–13 (PHP 8.1+)

### Notes

- LogMonitor contract fields: `occured_at`, `message`, `code`, `file`, `line`, `trace`, `url`, `method`, `ip_address`, `context`, `user_id`, `user_agent`
- Empty `user_id` is sent as `-`
- `guzzlehttp/guzzle` `^7.2 || ^8.0`

[1.0.0]: https://github.com/f3brysan/logmonitor-package/releases/tag/v1.0.0
