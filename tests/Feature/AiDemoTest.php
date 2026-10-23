<?php

namespace Tests\Feature;

use App\Ai\GroqTextGenerator;
use App\Ai\NextStepExplainer;
use App\Ai\TextGenerator;
use App\Safety\NextStepDecider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/** A stand-in for the AI that records every call. It never uses the network. */
class FakeTextGenerator implements TextGenerator
{
    /** @var list<array{system: string, prompt: string}> */
    public array $calls = [];

    public function __construct(private ?string $reply = 'A calm, fictional explanation.', private bool $fail = false) {}

    public function generate(string $system, string $prompt): ?string
    {
        $this->calls[] = ['system' => $system, 'prompt' => $prompt];

        if ($this->fail) {
            throw new RuntimeException('Simulated network failure');
        }

        return $this->reply;
    }
}

class AiDemoTest extends TestCase
{
    private const MAYA = 'guided-maya-headache';

    private const JORDAN = 'guided-jordan-cough';

    private const SAM = 'guided-sam-chest-pressure';

    private const ALEX = 'guided-alex-hopeless';

    private string $logPath;

    private FakeTextGenerator $fake;

    protected function setUp(): void
    {
        parent::setUp();

        // No test may ever reach the real network.
        Http::preventStrayRequests();

        $this->logPath = storage_path('framework/testing/ai-demo-test.log');
        @unlink($this->logPath);
        config(['logging.channels.safety_override.path' => $this->logPath]);

        $this->useFake(new FakeTextGenerator);
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    private function useFake(FakeTextGenerator $fake): void
    {
        $this->fake = $fake;
        $this->app->instance(TextGenerator::class, $fake);
    }

    /** Switch the feature on with a placeholder key (never a real one). */
    private function enableAi(): void
    {
        config([
            'ai_demo.enabled' => true,
            'services.groq.key' => 'test-placeholder-key',
        ]);
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

    // --- Safe defaults ------------------------------------------------------

    public function test_ai_is_off_by_default_and_uses_a_groq_free_plan_model(): void
    {
        $this->assertFalse(config('ai_demo.enabled'));
        $this->assertSame('', (string) config('services.groq.key'));
        $this->assertSame('openai/gpt-oss-20b', config('ai_demo.model'));
        $this->assertSame('Groq', config('ai_demo.provider'));
        $this->assertSame(['self_care', 'professional'], config('ai_demo.allowed_outcomes'));
    }

    public function test_when_switched_off_the_ai_is_not_called(): void
    {
        $this->postJson('/ai-demo/'.self::MAYA)
            ->assertOk()
            ->assertJson([
                'ai_used' => false,
                'ai_status' => 'disabled',
                'explanation' => null,
                'model' => null,
            ])
            ->assertJsonPath('message', 'The AI explanation is turned off right now. The next step above was chosen by NextStep’s safety rules and still applies.');

        $this->assertCount(0, $this->fake->calls);
    }

    public function test_when_switched_on_without_a_key_the_ai_is_not_called(): void
    {
        config(['ai_demo.enabled' => true, 'services.groq.key' => '']);

        $this->postJson('/ai-demo/'.self::MAYA)
            ->assertOk()
            ->assertJson(['ai_used' => false, 'ai_status' => 'not_configured']);

        $this->assertCount(0, $this->fake->calls);
    }

    // --- The real Groq client, with faked HTTP responses -------------------

    private function groq(string $key = 'test-placeholder-key'): GroqTextGenerator
    {
        return new GroqTextGenerator(apiKey: $key, model: 'openai/gpt-oss-20b', maxTokens: 1024, timeoutSeconds: 15);
    }

    /** @return array<string, mixed> */
    private function groqReply(string $content, string $finishReason = 'stop'): array
    {
        return [
            'object' => 'chat.completion',
            'model' => 'openai/gpt-oss-20b',
            'choices' => [[
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => $content],
                'finish_reason' => $finishReason,
            ]],
        ];
    }

    public function test_groq_client_makes_no_call_without_a_key(): void
    {
        Http::fake();

        $this->assertNull($this->groq('')->generate('system', 'prompt'));
        Http::assertNothingSent();
    }

    public function test_groq_client_sends_a_short_low_effort_request_and_reads_the_answer(): void
    {
        Http::fake([GroqTextGenerator::ENDPOINT => Http::response($this->groqReply('A calm explanation.'))]);

        $this->assertSame('A calm explanation.', $this->groq()->generate('SYSTEM TEXT', 'PROMPT TEXT'));

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-placeholder-key')
                && $request['model'] === 'openai/gpt-oss-20b'
                && $request['max_completion_tokens'] === 1024
                && $request['reasoning_effort'] === 'low'
                && $request['include_reasoning'] === false
                && $request['messages'] === [
                    ['role' => 'system', 'content' => 'SYSTEM TEXT'],
                    ['role' => 'user', 'content' => 'PROMPT TEXT'],
                ];
        });
    }

    public function test_groq_answers_cut_off_by_the_token_limit_are_discarded(): void
    {
        Http::fake([GroqTextGenerator::ENDPOINT => Http::response($this->groqReply('A half-finished', 'length'))]);

        $this->assertNull($this->groq()->generate('system', 'prompt'));
    }

    public function test_groq_rate_limit_and_server_errors_fall_back_through_the_endpoint(): void
    {
        $this->enableAi();
        $this->app->forgetInstance(TextGenerator::class);
        $this->app->bind(TextGenerator::class, fn () => $this->groq());

        foreach ([429, 401, 500] as $status) {
            Http::fake([GroqTextGenerator::ENDPOINT => Http::response(['error' => ['message' => 'secret detail']], $status)]);

            $response = $this->postJson('/ai-demo/'.self::JORDAN)
                ->assertOk()
                ->assertJson(['ai_used' => false, 'ai_status' => 'error', 'explanation' => null]);

            $this->assertStringNotContainsString('secret detail', $response->getContent());
        }
    }

    public function test_full_path_with_faked_groq_response_returns_labeled_ai_text(): void
    {
        $this->enableAi();
        $this->app->forgetInstance(TextGenerator::class);
        $this->app->bind(TextGenerator::class, fn () => $this->groq());
        Http::fake([GroqTextGenerator::ENDPOINT => Http::response($this->groqReply('Maya can try a calming exercise.'))]);

        $this->postJson('/ai-demo/'.self::MAYA)
            ->assertOk()
            ->assertJson([
                'ai_used' => true,
                'ai_status' => 'ok',
                'explanation' => 'Maya can try a calming exercise.',
                'model' => 'openai/gpt-oss-20b',
                'provider' => 'Groq',
            ]);

        // Emergency and crisis examples send nothing to Groq.
        Http::fake();
        $this->postJson('/ai-demo/'.self::SAM)->assertJsonPath('ai_status', 'safety_override');
        $this->postJson('/ai-demo/'.self::ALEX)->assertJsonPath('ai_status', 'safety_override');
        Http::assertNothingSent();
    }

    // --- Safety checks run before any AI call --------------------------------

    public function test_emergency_example_never_reaches_the_ai_and_is_logged(): void
    {
        $this->enableAi();

        $this->postJson('/ai-demo/'.self::SAM)
            ->assertOk()
            ->assertJson([
                'outcome' => 'emergency',
                'override_category' => 'medical_emergency',
                'ai_used' => false,
                'ai_status' => 'safety_override',
                'explanation' => null,
                'guidance' => config('safety_demo.overrides.medical_emergency.guidance'),
                'logged' => true,
            ]);

        $this->assertCount(0, $this->fake->calls);

        $events = $this->loggedEvents();
        $this->assertCount(1, $events);
        $this->assertSame(['timestamp', 'scenario_id', 'override_category'], array_keys($events[0]));
        $this->assertSame(self::SAM, $events[0]['scenario_id']);
        $this->assertSame('medical_emergency', $events[0]['override_category']);
    }

    public function test_crisis_example_never_reaches_the_ai_and_is_logged(): void
    {
        $this->enableAi();

        $this->postJson('/ai-demo/'.self::ALEX)
            ->assertOk()
            ->assertJson([
                'outcome' => 'crisis',
                'override_category' => 'mental_health_crisis',
                'ai_used' => false,
                'ai_status' => 'safety_override',
                'guidance' => config('safety_demo.overrides.mental_health_crisis.guidance'),
                'logged' => true,
            ]);

        $this->assertCount(0, $this->fake->calls);
        $this->assertSame('mental_health_crisis', $this->loggedEvents()[0]['override_category']);
    }

    public function test_explainer_itself_refuses_emergency_and_crisis_decisions(): void
    {
        $this->enableAi();
        $explainer = $this->app->make(NextStepExplainer::class);
        $decider = new NextStepDecider;

        foreach ([self::SAM, self::ALEX] as $id) {
            $this->assertSame(
                ['status' => 'blocked', 'text' => null],
                $explainer->explain($decider->forScenario($id)),
            );
        }

        $this->assertCount(0, $this->fake->calls);
    }

    // --- Working AI path ------------------------------------------------------

    public function test_non_emergency_examples_get_an_ai_explanation_when_switched_on(): void
    {
        $this->enableAi();

        foreach ([self::MAYA => 'self_care', self::JORDAN => 'professional'] as $id => $outcome) {
            $this->postJson('/ai-demo/'.$id)
                ->assertOk()
                ->assertJson([
                    'scenario_id' => $id,
                    'outcome' => $outcome,
                    'ai_used' => true,
                    'ai_status' => 'ok',
                    'explanation' => 'A calm, fictional explanation.',
                    'model' => 'openai/gpt-oss-20b',
                    'provider' => 'Groq',
                    'message' => null,
                    'logged' => false,
                ]);
        }

        $this->assertCount(2, $this->fake->calls);
        $this->assertSame([], $this->loggedEvents());
    }

    public function test_prompt_uses_only_the_predefined_example_and_ignores_request_data(): void
    {
        $this->enableAi();

        $this->postJson('/ai-demo/'.self::MAYA, [
            'symptoms' => 'REAL-USER-SYMPTOM',
            'name' => 'REAL-USER-NAME',
        ])->assertOk();

        $call = $this->fake->calls[0];
        $this->assertSame(config('ai_demo.system_prompt'), $call['system']);
        $this->assertStringContainsString(config('guided_demo.scenarios.'.self::MAYA.'.story'), $call['prompt']);
        $this->assertStringContainsString('Try anxiety-support tools', $call['prompt']);
        $this->assertStringNotContainsString('REAL-USER', $call['prompt']);
    }

    public function test_answers_are_saved_so_repeat_visits_do_not_call_the_ai_again(): void
    {
        $this->enableAi();

        $this->postJson('/ai-demo/'.self::MAYA)->assertJsonPath('ai_status', 'ok');
        $this->postJson('/ai-demo/'.self::MAYA)
            ->assertJsonPath('ai_status', 'cached')
            ->assertJsonPath('explanation', 'A calm, fictional explanation.');
        $this->postJson('/ai-demo/'.self::MAYA)->assertJsonPath('ai_status', 'cached');

        $this->assertCount(1, $this->fake->calls);
    }

    public function test_daily_limit_stops_ai_calls(): void
    {
        $this->enableAi();
        config(['ai_demo.daily_limit' => 1]);

        $this->postJson('/ai-demo/'.self::MAYA)->assertJsonPath('ai_status', 'ok');
        $this->postJson('/ai-demo/'.self::JORDAN)
            ->assertJson(['ai_used' => false, 'ai_status' => 'daily_limit'])
            ->assertJsonPath('message', 'The AI explanation has reached its daily limit. The next step above was chosen by NextStep’s safety rules and still applies.');

        $this->assertCount(1, $this->fake->calls);
    }

    // --- Failures fall back to the fixed guidance ----------------------------

    public function test_ai_errors_fall_back_without_exposing_details(): void
    {
        $this->enableAi();
        $this->useFake(new FakeTextGenerator(fail: true));

        $response = $this->postJson('/ai-demo/'.self::MAYA)
            ->assertOk()
            ->assertJson(['ai_used' => false, 'ai_status' => 'error', 'explanation' => null]);

        $this->assertStringNotContainsString('Simulated network failure', $response->getContent());
    }

    public function test_empty_refused_or_overlong_answers_fall_back(): void
    {
        $this->enableAi();

        foreach ([null, '   ', str_repeat('a', 1001)] as $reply) {
            $this->useFake(new FakeTextGenerator(reply: $reply));

            $this->postJson('/ai-demo/'.self::JORDAN)
                ->assertOk()
                ->assertJson(['ai_used' => false, 'ai_status' => 'error', 'explanation' => null]);
        }
    }

    // --- Requests and secrets -----------------------------------------------

    public function test_unknown_example_is_rejected_without_calling_the_ai(): void
    {
        $this->enableAi();

        $this->postJson('/ai-demo/not-a-real-example')->assertNotFound();
        $this->assertCount(0, $this->fake->calls);
    }

    public function test_only_post_is_allowed(): void
    {
        $this->get('/ai-demo/'.self::MAYA)->assertStatus(405);
    }

    public function test_api_key_never_appears_in_responses_or_the_page(): void
    {
        $this->enableAi();
        config(['services.groq.key' => 'gsk_secret_test_value']);

        $this->assertStringNotContainsString('gsk_secret_test_value', $this->postJson('/ai-demo/'.self::MAYA)->getContent());
        $this->assertStringNotContainsString('gsk_secret_test_value', $this->get('/')->getContent());
    }

    // --- Page ---------------------------------------------------------------

    public function test_page_offers_a_clearly_labeled_optional_ai_explanation(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Optional: AI explanation', $html);
        $this->assertStringContainsString('AI can explain it in plain language, but it cannot change it.', $html);
        $this->assertStringContainsString('AI is never used for emergency or crisis examples.', $html);

        foreach (array_keys(config('guided_demo.scenarios')) as $id) {
            $this->assertStringContainsString('data-ai-url="/ai-demo/'.$id.'"', $html);
        }
    }
}
