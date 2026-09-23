<?php

namespace Febrysan\LogCentral\Tests\Unit;

use Febrysan\LogCentral\ErrorReporter;
use Febrysan\LogCentral\ReportContext;
use Febrysan\LogCentral\Tests\TestCase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorReporterTest extends TestCase
{
    public function test_posts_contract_payload_and_stores_log_id(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'log_id' => '01logidfromroot',
                'data' => ['id' => '01logidfromroot'],
            ], 201),
        ]);

        $this->app->make(ErrorReporter::class)->report(new RuntimeException('boom'));

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'http://logcentral.test/api/error-log-transactions'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && isset($data['occured_at'], $data['code'], $data['ip_address'], $data['context'], $data['user_agent'])
                && $data['message'] === 'boom'
                && $data['code'] === RuntimeException::class
                && $data['user_id'] === '-'
                && is_array($data['context']);
        });

        $this->assertSame('01logidfromroot', $this->app->make(ReportContext::class)->logId());
    }

    public function test_user_resolver_exception_does_not_block_report(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'log_id' => '01afteruserfail',
                'data' => ['id' => '01afteruserfail'],
            ], 201),
        ]);

        $request = new class extends \Illuminate\Http\Request {
            public function user($guard = null)
            {
                throw new RuntimeException('session down');
            }
        };
        $request->initialize([], [], [], [], [], [
            'HTTP_HOST' => 'example.test',
            'REQUEST_URI' => '/x',
            'REQUEST_METHOD' => 'GET',
        ]);
        $this->app->instance('request', $request);

        $this->app->make(ErrorReporter::class)->report(new RuntimeException('boom'));

        Http::assertSentCount(1);
        $this->assertSame('01afteruserfail', $this->app->make(ReportContext::class)->logId());
    }

    public function test_dont_report_skips_post(): void
    {
        config(['logcentral.dont_report' => [RuntimeException::class]]);
        Http::fake();

        $this->app->make(ErrorReporter::class)->report(new RuntimeException('skip me'));

        Http::assertNothingSent();
        $this->assertNull($this->app->make(ReportContext::class)->logId());
    }

    public function test_client_http_exception_skips_post(): void
    {
        Http::fake();

        $this->app->make(ErrorReporter::class)->report(new NotFoundHttpException());

        Http::assertNothingSent();
    }

    public function test_http_failure_is_fail_safe(): void
    {
        Http::fake(function () {
            throw new ConnectionException('down');
        });

        $this->app->make(ErrorReporter::class)->report(new RuntimeException('boom'));

        $this->assertNull($this->app->make(ReportContext::class)->logId());
    }

    public function test_unsuccessful_response_does_not_set_log_id(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'invalid'], 422),
        ]);

        $this->app->make(ErrorReporter::class)->report(new RuntimeException('boom'));

        $this->assertNull($this->app->make(ReportContext::class)->logId());
    }
}
