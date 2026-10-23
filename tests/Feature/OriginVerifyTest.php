<?php

namespace Tests\Feature;

use App\Ai\TextGenerator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OriginVerifyTest extends TestCase
{
    private const SECRET = 'test-origin-secret-0123456789abcdef0123456789abcdef';

    private const OLD_SECRET = 'test-old-origin-secret-fedcba9876543210fedcba98765432';

    /** The 8 existing demonstrations: 4 guided examples and 4 safety demos. */
    private const DEMOS = [
        '/guided-demo/guided-maya-headache',
        '/guided-demo/guided-jordan-cough',
        '/guided-demo/guided-sam-chest-pressure',
        '/guided-demo/guided-alex-hopeless',
        '/safety-demo/demo-ordinary-worry',
        '/safety-demo/demo-emergency-warning',
        '/safety-demo/demo-override-priority',
        '/safety-demo/demo-impending-doom',
    ];

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        // Keep override log entries from these tests out of the real log.
        $this->logPath = storage_path('framework/testing/origin-verify-test.log');
        config(['logging.channels.safety_override.path' => $this->logPath]);
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    private function requireSecret(string $secret = self::SECRET, string $previous = ''): void
    {
        config(['origin.secret' => $secret, 'origin.previous_secret' => $previous]);
    }

    /** @return array<string, string> */
    private function fromCloudFront(string $secret = self::SECRET): array
    {
        return ['X-Origin-Verify' => $secret];
    }

    // --- Off by default -----------------------------------------------------

    public function test_check_is_off_by_default_so_nothing_changes_until_configured(): void
    {
        $this->assertSame('', config('origin.secret'));
        $this->assertSame('', config('origin.previous_secret'));
        $this->assertSame('X-Origin-Verify', config('origin.header'));

        $this->get('/')->assertOk();
        foreach (self::DEMOS as $url) {
            $this->postJson($url)->assertOk();
        }
    }

    // --- Requests without the right header are refused -----------------------

    public function test_requests_without_the_header_are_refused(): void
    {
        $this->requireSecret();

        $this->get('/')->assertForbidden();
        $this->postJson('/guided-demo/guided-maya-headache')->assertForbidden();
        $this->get('/does-not-exist')->assertForbidden();
    }

    public function test_wrong_empty_or_partial_values_are_refused(): void
    {
        $this->requireSecret();

        foreach (['wrong', '', ' ', substr(self::SECRET, 0, 20), self::SECRET.'x', strtoupper(self::SECRET)] as $value) {
            $this->get('/', ['X-Origin-Verify' => $value])->assertForbidden();
        }
    }

    public function test_refused_requests_reveal_nothing_and_get_no_cookies(): void
    {
        $this->requireSecret();

        $response = $this->get('/', ['X-Origin-Verify' => 'wrong']);

        $response->assertForbidden();
        $this->assertSame('Forbidden', $response->getContent());
        $this->assertStringNotContainsString(self::SECRET, $response->getContent());
        $this->assertSame([], $response->headers->getCookies());
    }

    // --- Requests through CloudFront work as before --------------------------

    public function test_homepage_and_all_eight_demos_work_through_cloudfront(): void
    {
        $this->requireSecret();

        $this->get('/', $this->fromCloudFront())
            ->assertOk()
            ->assertSee('Walk through an example')
            ->assertSee('Safety override demonstration');

        foreach (self::DEMOS as $url) {
            $this->postJson($url, [], $this->fromCloudFront())->assertOk();
            $this->postJson($url)->assertForbidden();
        }
    }

    public function test_safety_overrides_still_return_emergency_guidance_through_cloudfront(): void
    {
        $this->requireSecret();

        $this->postJson('/guided-demo/guided-sam-chest-pressure', [], $this->fromCloudFront())
            ->assertJson(['outcome' => 'emergency', 'override_category' => 'medical_emergency', 'logged' => true]);

        $this->postJson('/guided-demo/guided-alex-hopeless', [], $this->fromCloudFront())
            ->assertJson(['outcome' => 'crisis', 'override_category' => 'mental_health_crisis', 'logged' => true]);

        $this->postJson('/safety-demo/demo-impending-doom', [], $this->fromCloudFront())
            ->assertJson(['override' => true, 'override_category' => 'medical_emergency']);
    }

    public function test_page_never_contains_the_secret(): void
    {
        $this->requireSecret();

        $this->assertStringNotContainsString(self::SECRET, $this->get('/', $this->fromCloudFront())->getContent());
    }

    // --- AI endpoint --------------------------------------------------------

    public function test_ai_endpoint_is_refused_without_the_header_and_ai_is_never_called(): void
    {
        $this->requireSecret();
        config(['ai_demo.enabled' => true, 'services.groq.key' => 'test-placeholder-key']);

        $fake = new class implements TextGenerator
        {
            public int $calls = 0;

            public function generate(string $system, string $prompt): ?string
            {
                $this->calls++;

                return 'A calm, fictional explanation.';
            }
        };
        $this->app->instance(TextGenerator::class, $fake);

        $this->postJson('/ai-demo/guided-maya-headache')->assertForbidden();
        $this->assertSame(0, $fake->calls);

        $this->postJson('/ai-demo/guided-maya-headache', [], $this->fromCloudFront())
            ->assertOk()
            ->assertJsonPath('ai_status', 'ok');
        $this->assertSame(1, $fake->calls);
    }

    public function test_ai_stays_disabled_by_default_through_cloudfront(): void
    {
        $this->requireSecret();

        $this->postJson('/ai-demo/guided-maya-headache', [], $this->fromCloudFront())
            ->assertOk()
            ->assertJson(['ai_used' => false, 'ai_status' => 'disabled']);
    }

    // --- Health check and secret changes -------------------------------------

    public function test_health_check_works_without_the_header(): void
    {
        $this->requireSecret();

        $this->get('/up')->assertOk();
    }

    public function test_previous_secret_keeps_working_during_a_change(): void
    {
        $this->requireSecret(self::SECRET, self::OLD_SECRET);

        $this->get('/', $this->fromCloudFront(self::SECRET))->assertOk();
        $this->get('/', $this->fromCloudFront(self::OLD_SECRET))->assertOk();
        $this->get('/', $this->fromCloudFront('wrong'))->assertForbidden();
    }

    public function test_only_a_previous_secret_set_still_enforces_the_check(): void
    {
        $this->requireSecret('', self::OLD_SECRET);

        $this->get('/')->assertForbidden();
        $this->get('/', $this->fromCloudFront(self::OLD_SECRET))->assertOk();
    }
}
