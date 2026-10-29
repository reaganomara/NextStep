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
            ->assertSee('AI-powered explanations are now live for fictional examples')
            ->assertSee('A calmer way to figure out your next step.')
            ->assertSee('Not identifying warning signs does not guarantee that a symptom is harmless.')
            ->assertSee('1-800-222-1222')
            ->assertSee('This is a demonstration, not a working AI symptom checker.');
    }

    public function test_homepage_hides_withheld_guidance_and_explains_why(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('In the real NextStep app, withheld anxiety guidance would never be shown to the user during an emergency.')
            ->assertSee('Show what was withheld');

        foreach (['demo-emergency-warning', 'demo-override-priority', 'demo-impending-doom'] as $id) {
            $response->assertDontSee(config("safety_demo.scenarios.$id.support_guidance"));
        }
    }

    public function test_homepage_shows_emergency_contacts_prominently(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('aria-label="Emergency contacts"', false)
            ->assertSee('href="tel:911"', false)
            ->assertSee('href="tel:988"', false)
            ->assertSee('href="tel:18002221222"', false)
            ->assertSee('These phone numbers are for the United States.');
    }

    public function test_homepage_shows_sourced_awareness_dates_and_walk_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Mental Health Awareness Month', 'https://mhanational.org/mental-health-month/'], false)
            ->assertSeeInOrder(['September 10', 'World Suicide Prevention Day', 'https://www.who.int/campaigns/world-suicide-prevention-day'], false)
            ->assertSeeInOrder(['October 10', 'World Mental Health Day', 'https://www.who.int/campaigns/world-mental-health-day'], false)
            ->assertSee('https://www.namiwalks.org/', false)
            ->assertSee('https://afsp.org/out-of-the-darkness-walks/', false)
            ->assertSee('NextStep is not affiliated with, endorsed by, or partnered with these organizations');
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

    public function test_homepage_lists_all_four_scenarios(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'demo-ordinary-worry',
                'demo-emergency-warning',
                'demo-override-priority',
                'Sense of Impending Doom',
                'Is this just anxiety?',
                'demo-impending-doom',
            ]);
    }

    public function test_impending_doom_example_prioritizes_emergency_evaluation_and_is_logged(): void
    {
        $scenario = config('safety_demo.scenarios.demo-impending-doom');

        $response = $this->postJson('/safety-demo/demo-impending-doom')
            ->assertOk()
            ->assertJson([
                'scenario_id' => 'demo-impending-doom',
                'override' => true,
                'override_category' => 'medical_emergency',
                'override_label' => 'Medical emergency',
                'emergency_guidance' => $scenario['emergency_guidance'],
                'withheld_support_guidance' => $scenario['support_guidance'],
                'logged' => true,
            ])
            ->assertJsonMissingPath('support_guidance');

        $guidance = $response->json('emergency_guidance');
        $this->assertStringContainsString('Call 911', $guidance);
        $this->assertStringContainsString('panic attack', $guidance);
        $this->assertStringContainsString('serious medical emergencies', $guidance);
        $this->assertStringContainsString('emergency evaluation', $guidance);

        // Relaxation or CBT guidance must never appear in the emergency response itself.
        $this->assertStringNotContainsString($scenario['support_guidance'], $guidance);
        $this->assertStringNotContainsStringIgnoringCase('breathing:', $guidance);

        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);

        $event = json_decode($lines[0], true);
        $this->assertSame(['timestamp', 'scenario_id', 'override_category'], array_keys($event));
        $this->assertSame('demo-impending-doom', $event['scenario_id']);
        $this->assertSame('medical_emergency', $event['override_category']);
    }

    public function test_impending_doom_guidance_does_not_diagnose_panic_or_falsely_reassure(): void
    {
        $guidance = strtolower(config('safety_demo.scenarios.demo-impending-doom.emergency_guidance'));

        foreach ([
            'just anxiety',
            'only anxiety',
            'nothing to worry',
            'you are having a panic attack',
            "you're having a panic attack",
            'this is a panic attack',
            'you are fine',
            'harmless',
        ] as $phrase) {
            $this->assertStringNotContainsString($phrase, $guidance);
        }
    }

    public function test_existing_emergency_example_still_uses_default_category_guidance(): void
    {
        $this->postJson('/safety-demo/demo-emergency-warning')
            ->assertOk()
            ->assertJson(['emergency_guidance' => config('safety_demo.overrides.medical_emergency.guidance')]);
    }

    public function test_unknown_scenario_is_rejected(): void
    {
        $this->postJson('/safety-demo/not-a-real-scenario')->assertNotFound();

        $this->assertFileDoesNotExist($this->logPath);
    }
}
