<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuidedDemoEndpointTest extends TestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('framework/testing/guided-demo-test.log');
        @unlink($this->logPath);

        config(['logging.channels.safety_override.path' => $this->logPath]);
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    /** @return list<array<string, string>> */
    private function loggedEvents(): array
    {
        if (! file_exists($this->logPath)) {
            return [];
        }

        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return array_map(fn (string $line) => json_decode($line, true), $lines);
    }

    public function test_maya_gets_anxiety_support_tools_and_nothing_is_logged(): void
    {
        $this->postJson('/guided-demo/guided-maya-headache')
            ->assertOk()
            ->assertJson([
                'scenario_id' => 'guided-maya-headache',
                'outcome' => 'self_care',
                'label' => 'Try anxiety-support tools',
                'override' => false,
                'override_category' => null,
                'logged' => false,
            ])
            ->assertJsonPath('checks.0.result', 'no')
            ->assertJsonPath('checks.1.result', 'no')
            ->assertJsonPath('checks.2.result', 'no');

        $this->assertSame([], $this->loggedEvents());
    }

    public function test_jordan_gets_professional_care_and_nothing_is_logged(): void
    {
        $this->postJson('/guided-demo/guided-jordan-cough')
            ->assertOk()
            ->assertJson([
                'outcome' => 'professional',
                'label' => 'Contact a healthcare professional soon',
                'override' => false,
                'logged' => false,
            ])
            ->assertJsonPath('checks.2.result', 'yes');

        $this->assertSame([], $this->loggedEvents());
    }

    public function test_sam_gets_911_and_the_override_is_logged_with_three_fields(): void
    {
        $this->postJson('/guided-demo/guided-sam-chest-pressure')
            ->assertOk()
            ->assertJson([
                'outcome' => 'emergency',
                'label' => 'Call 911 now',
                'guidance' => config('safety_demo.overrides.medical_emergency.guidance'),
                'override' => true,
                'override_category' => 'medical_emergency',
                'logged' => true,
            ])
            ->assertJsonPath('checks.0.result', 'yes')
            ->assertJsonPath('checks.1.result', 'skipped')
            ->assertJsonPath('checks.2.result', 'skipped');

        $events = $this->loggedEvents();
        $this->assertCount(1, $events);
        $this->assertSame(['timestamp', 'scenario_id', 'override_category'], array_keys($events[0]));
        $this->assertSame('guided-sam-chest-pressure', $events[0]['scenario_id']);
        $this->assertSame('medical_emergency', $events[0]['override_category']);
    }

    public function test_alex_gets_988_and_the_override_is_logged_with_three_fields(): void
    {
        $this->postJson('/guided-demo/guided-alex-hopeless')
            ->assertOk()
            ->assertJson([
                'outcome' => 'crisis',
                'label' => 'Call or text 988 now',
                'guidance' => config('safety_demo.overrides.mental_health_crisis.guidance'),
                'override' => true,
                'override_category' => 'mental_health_crisis',
                'logged' => true,
            ])
            ->assertJsonPath('checks.1.result', 'yes')
            ->assertJsonPath('checks.2.result', 'skipped');

        $events = $this->loggedEvents();
        $this->assertCount(1, $events);
        $this->assertSame(['timestamp', 'scenario_id', 'override_category'], array_keys($events[0]));
        $this->assertSame('guided-alex-hopeless', $events[0]['scenario_id']);
        $this->assertSame('mental_health_crisis', $events[0]['override_category']);
    }

    public function test_anything_sent_with_the_request_is_ignored(): void
    {
        $extra = [
            'name' => 'Jane Example',
            'symptom' => 'chest pain',
            'signals' => ['emergency_signs' => false, 'crisis_signs' => false, 'needs_professional' => false],
        ];

        // Sent answers cannot change Sam's emergency result.
        $response = $this->postJson('/guided-demo/guided-sam-chest-pressure', $extra)
            ->assertOk()
            ->assertJson(['outcome' => 'emergency']);

        $this->assertStringNotContainsString('Jane', $response->getContent());
        $this->assertStringNotContainsString('chest pain', $response->getContent());

        $contents = file_get_contents($this->logPath);
        $this->assertStringNotContainsString('Jane', $contents);
        $this->assertStringNotContainsString('chest pain', $contents);
        $this->assertStringNotContainsString('127.0.0.1', $contents);
    }

    public function test_unknown_and_existing_demo_ids_are_rejected(): void
    {
        $this->postJson('/guided-demo/not-a-real-example')->assertNotFound();
        $this->postJson('/guided-demo/demo-emergency-warning')->assertNotFound();

        $this->assertSame([], $this->loggedEvents());
    }

    public function test_only_post_requests_are_allowed(): void
    {
        $this->getJson('/guided-demo/guided-maya-headache')->assertMethodNotAllowed();
    }

    public function test_guided_demo_has_its_own_rate_limit_and_never_blocks_the_safety_demo(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/guided-demo/guided-maya-headache')->assertOk();
        }

        $this->postJson('/guided-demo/guided-maya-headache')->assertTooManyRequests();

        // The existing safety override demonstration still works.
        $this->postJson('/safety-demo/demo-ordinary-worry')->assertOk();
    }
}
