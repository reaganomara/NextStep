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
 * Every reply, new or saved, must pass the safety filter before it is shown.
 */
class NextStepExplainer
{
    public function __construct(private TextGenerator $generator) {}

    /**
     * @param  array<string, mixed>  $decision  Output of NextStepDecider::forScenario().
     * @return array{status: string, text: ?string}
     *
     * status is "ok" or "cached" when text is AI-written, and otherwise one
     * of "blocked", "disabled", "not_configured", "daily_limit", "filtered"
     * or "error".
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
            if ($this->blockedBy($saved) !== null) {
                Cache::forget($cacheKey);

                return ['status' => 'filtered', 'text' => null];
            }

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

        $blocked = $this->blockedBy($text);
        if ($blocked !== null) {
            // Record only which rule matched, never the reply itself.
            Log::warning('AI demo reply filtered: '.$blocked);

            return ['status' => 'filtered', 'text' => null];
        }

        Cache::put($cacheKey, $text, now()->addHours(config('ai_demo.cache_hours')));

        return ['status' => 'ok', 'text' => $text];
    }

    /**
     * Return the blocked term or pattern the text matches, or null if the
     * text is allowed.
     */
    public function blockedBy(string $text): ?string
    {
        // Treat curly apostrophes and special hyphens like plain ones.
        $text = str_replace(["\u{2019}", "\u{2018}", "\u{2010}", "\u{2011}", "\u{2013}"], ["'", "'", '-', '-', '-'], $text);

        foreach (config('ai_demo.blocked_terms') as $term) {
            // "word*" matches any word starting with "word". Other terms
            // match as whole words, with or without a plural ending.
            $regex = str_ends_with($term, '*')
                ? '/\b'.preg_quote(rtrim($term, '*'), '/').'/iu'
                : '/\b'.preg_quote($term, '/').'(s|es)?\b/iu';

            if (preg_match($regex, $text) === 1) {
                return $term;
            }
        }

        foreach (config('ai_demo.blocked_patterns') as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return $pattern;
            }
        }

        return null;
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
