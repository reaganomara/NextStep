<?php

namespace Tests\Feature;

use Tests\TestCase;

class SafetyDemoTest extends TestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('framework/testing/safety-override-test.log');
        @unlink($this->logPath);

        config(['logging.channels.safety_override.path' => $this->logPath]);
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    public function test_homepage_shows_required_safety_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertSee('A calmer way to figure out your next step.')
            ->assertSee('Not identifying warning signs does not guarantee that a symptom is harmless.')
            ->assertSee('1-800-222-1222')
            ->assertSee('This is a demonstration, not a working AI symptom checker.');
    }

    public function test_ordinary_example_returns_support_guidance_and_logs_nothing(): void
    {
        $this->postJson('/safety-demo/demo-ordinary-worry')
            ->assertOk()
            ->assertJson(['override' => false])
            ->assertJsonStructure(['support_guidance']);

        $this->assertFileDoesNotExist($this->logPath);
    }

    public function test_emergency_example_overrides_support_guidance_and_is_logged(): void
    {
        $this->postJson('/safety-demo/demo-emergency-warning')
            ->assertOk()
            ->assertJson([
                'override' => true,
                'override_category' => 'medical_emergency',
                'logged' => true,
            ])
            ->assertJsonMissingPath('support_guidance');

        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);

        $event = json_decode($lines[0], true);
        $this->assertSame(['timestamp', 'scenario_id', 'override_category'], array_keys($event));
        $this->assertSame('demo-emergency-warning', $event['scenario_id']);
        $this->assertSame('medical_emergency', $event['override_category']);
    }

    public function test_log_ignores_anything_sent_with_the_request(): void
    {
        $this->postJson('/safety-demo/demo-override-priority', ['name' => 'Jane Example', 'symptom' => 'chest pain'])
            ->assertOk();

        $contents = file_get_contents($this->logPath);
        $this->assertStringNotContainsString('Jane', $contents);
        $this->assertStringNotContainsString('chest pain', $contents);
        $this->assertStringNotContainsString('127.0.0.1', $contents);
    }

    public function test_unknown_scenario_is_rejected(): void
    {
        $this->postJson('/safety-demo/not-a-real-scenario')->assertNotFound();

        $this->assertFileDoesNotExist($this->logPath);
    }
}
