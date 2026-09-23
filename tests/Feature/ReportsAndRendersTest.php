<?php

namespace Febrysan\LogCentral\Tests\Feature;

use Febrysan\LogCentral\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ReportsAndRendersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'log_id' => '01featurelogid',
                'data' => ['id' => '01featurelogid'],
            ], 201),
        ]);
    }

    public function test_html_500_shows_safe_page_and_log_id_when_debug_off(): void
    {
        $response = $this->get('/__logcentral-smoke');

        $response->assertStatus(500);
        $response->assertSee('Terjadi Kesalahan', false);
        $response->assertSee('LOG ID: 01featurelogid', false);
        $response->assertDontSee('logcentral smoke test', false);
        Http::assertSentCount(1);
    }

    public function test_json_request_is_not_replaced_with_custom_view(): void
    {
        $response = $this->getJson('/__logcentral-smoke');

        $response->assertStatus(500);
        $response->assertDontSee('Terjadi Kesalahan', false);
        $this->assertStringNotContainsString('LOG ID', $response->getContent());
    }

    public function test_debug_on_does_not_use_package_500_view(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/__logcentral-smoke');

        $response->assertSee('logcentral smoke test', false);
    }

    public function test_disabled_package_does_not_report_or_override_500(): void
    {
        config(['logcentral.enabled' => false]);
        Http::fake();

        $response = $this->get('/__logcentral-smoke');

        $response->assertDontSee('Terjadi Kesalahan', false);
        Http::assertNothingSent();
    }
}
