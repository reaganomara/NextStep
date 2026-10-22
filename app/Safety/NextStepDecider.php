<?php

namespace App\Safety;

use InvalidArgumentException;

/**
 * Decides one safe next step from a fictional example's fixed answers.
 *
 * Checks run in a fixed order (emergency, crisis, professional) and the
 * first "yes" wins, so an emergency always takes priority. If nothing is
 * flagged, the next step is anxiety-support tools.
 */
class NextStepDecider
{
    /** Each check, in priority order, and the outcome it leads to. */
    private const ORDER = [
        'emergency_signs' => 'emergency',
        'crisis_signs' => 'crisis',
        'needs_professional' => 'professional',
    ];

    /**
     * Decide the outcome for a set of yes/no signals.
     *
     * Every signal must be present and be true or false. Anything else is
     * treated as a configuration mistake rather than guessed at.
     *
     * @param  array<string, bool>  $signals
     * @return array{outcome: string, checks: list<array{check: string, result: string}>}
     */
    public function decide(array $signals): array
    {
        foreach (array_keys(self::ORDER) as $check) {
            if (! array_key_exists($check, $signals) || ! is_bool($signals[$check])) {
                throw new InvalidArgumentException("Signal \"$check\" must be true or false.");
            }
        }

        $outcome = 'self_care';
        $checks = [];

        foreach (self::ORDER as $check => $result) {
            if ($outcome !== 'self_care') {
                // A higher-priority step was already chosen.
                $checks[] = ['check' => $check, 'result' => 'skipped'];

                continue;
            }

            $checks[] = ['check' => $check, 'result' => $signals[$check] ? 'yes' : 'no'];

            if ($signals[$check]) {
                $outcome = $result;
            }
        }

        return ['outcome' => $outcome, 'checks' => $checks];
    }

    /**
     * Build the full next step for one predefined example, or null if the
     * example does not exist.
     *
     * @return array<string, mixed>|null
     */
    public function forScenario(string $id): ?array
    {
        $scenario = config('guided_demo.scenarios')[$id] ?? null;

        if ($scenario === null) {
            return null;
        }

        $decision = $this->decide($scenario['signals']);
        $outcome = config('guided_demo.outcomes')[$decision['outcome']];
        $category = $outcome['override_category'];

        // Emergency and crisis wording comes from the safety override demo,
        // so there is only one copy of the 911 and 988 guidance.
        $guidance = $category === null
            ? $outcome['guidance']
            : config('safety_demo.overrides')[$category]['guidance'];

        return [
            'scenario_id' => $id,
            'name' => $scenario['name'],
            'outcome' => $decision['outcome'],
            'label' => $outcome['label'],
            'guidance' => $guidance,
            'override_category' => $category,
            'checks' => array_map(fn (array $c) => [
                'question' => config('guided_demo.checks')[$c['check']],
                'result' => $c['result'],
            ], $decision['checks']),
            'reasons' => $scenario['reasons'],
        ];
    }
}
