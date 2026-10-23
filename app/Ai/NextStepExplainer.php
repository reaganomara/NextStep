<?php

namespace App\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks the AI to explain a next step that NextStep's safety rules have
 * already chosen for a predefined, fictional example.
 *
 * Every check that could stop the AI call runs before it, in this order:
 * allowed outcome, switched on, key present, saved answer, daily limit.
 */
class NextStepExplainer
{
    public function __construct(private TextGenerator $generator) {}

    /**
     * @param  array<string, mixed>  $decision  Output of NextStepDecider::forScenario().
     * @return array{status: string, text: ?string}
     *
     * status is "ok" or "cached" when text is AI-written, and otherwise one
     * of "blocked", "disabled", "not_configured", "daily_limit" or "error".
     */
    public function explain(array $decision): array
    {
        // Second line of defence: the controller already stops emergency
        // and crisis examples, but this class refuses them too.
        if ($decision['override_category'] !== null
            || ! in_array($decision['outcome'], config('ai_demo.allowed_outcomes'), true)) {
            return ['status' => 'blocked', 'text' => null];
        }

        if (! config('ai_demo.enabled')) {
            return ['status' => 'disabled', 'text' => null];
        }

        if ((string) config('services.groq.key') === '') {
            return ['status' => 'not_configured', 'text' => null];
        }

        // The examples never change, so one saved answer serves everyone.
        $cacheKey = 'ai-demo:explanation:'.$decision['scenario_id'].':'.config('ai_demo.model');
        $saved = Cache::get($cacheKey);
        if (is_string($saved)) {
            return ['status' => 'cached', 'text' => $saved];
        }

        if (! $this->withinDailyLimit()) {
            return ['status' => 'daily_limit', 'text' => null];
        }

        try {
            $text = $this->generator->generate(config('ai_demo.system_prompt'), $this->prompt($decision));
        } catch (Throwable $e) {
            // Record only the kind of error, never the prompt or the answer.
            Log::warning('AI demo call failed: '.$e::class);

            return ['status' => 'error', 'text' => null];
        }

        $text = trim((string) $text);

        if ($text === '' || mb_strlen($text) > config('ai_demo.max_characters')) {
            return ['status' => 'error', 'text' => null];
        }

        Cache::put($cacheKey, $text, now()->addHours(config('ai_demo.cache_hours')));

        return ['status' => 'ok', 'text' => $text];
    }

    /** Count this call toward today's limit and say whether it is allowed. */
    private function withinDailyLimit(): bool
    {
        $key = 'ai-demo:calls:'.now()->utc()->toDateString();

        Cache::add($key, 0, now()->addDay());

        return Cache::increment($key) <= config('ai_demo.daily_limit');
    }

    /**
     * Built only from the predefined example in config/guided_demo.php and
     * the decision. Nothing from the request is included.
     *
     * @param  array<string, mixed>  $decision
     */
    private function prompt(array $decision): string
    {
        $story = config('guided_demo.scenarios')[$decision['scenario_id']]['story'];

        return implode("\n\n", [
            'Fictional example: '.$story,
            'Next step chosen by the safety rules: '.$decision['label'],
            'Guidance already shown to the reader: '.$decision['guidance'],
            "Reasons the safety rules gave:\n- ".implode("\n- ", $decision['reasons']),
            'Explain in 2 or 3 calm sentences why this next step fits '.$decision['name'].'.',
        ]);
    }
}
