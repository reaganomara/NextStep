<?php

namespace App\Http\Controllers;

use App\Ai\NextStepExplainer;
use App\Safety\NextStepDecider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AiDemoController extends Controller
{
    /**
     * Return an optional AI-written explanation for one predefined, fictional
     * example from the guided walkthrough.
     *
     * The safety rules run first. Emergency and crisis examples get the
     * fixed 911 or 988 guidance and never reach the AI. Nothing sent with the
     * request is read; only the example ID in the URL is used.
     */
    public function run(string $scenario, NextStepDecider $decider, NextStepExplainer $explainer): JsonResponse
    {
        $decision = $decider->forScenario($scenario);

        abort_if($decision === null, 404);

        $base = [
            'scenario_id' => $scenario,
            'outcome' => $decision['outcome'],
            'override_category' => $decision['override_category'],
        ];

        if ($decision['override_category'] !== null) {
            // Same three fields, channel and format as the other demos.
            Log::channel(config('safety_demo.log_channel'))->warning(json_encode([
                'timestamp' => now()->utc()->toIso8601String(),
                'scenario_id' => $scenario,
                'override_category' => $decision['override_category'],
            ]));

            return response()->json($base + [
                'ai_used' => false,
                'ai_status' => 'safety_override',
                'explanation' => null,
                'guidance' => $decision['guidance'],
                'message' => 'AI is never used for emergency or crisis examples. Follow the guidance above.',
                'logged' => true,
            ]);
        }

        $result = $explainer->explain($decision);
        $aiUsed = in_array($result['status'], ['ok', 'cached'], true);

        return response()->json($base + [
            'ai_used' => $aiUsed,
            'ai_status' => $result['status'],
            'explanation' => $result['text'],
            'model' => $aiUsed ? config('ai_demo.model') : null,
            'provider' => $aiUsed ? config('ai_demo.provider') : null,
            'message' => $aiUsed ? null : $this->fallbackMessage($result['status']),
            'logged' => false,
        ]);
    }

    private function fallbackMessage(string $status): string
    {
        $reason = match ($status) {
            'disabled' => 'The AI explanation is turned off right now.',
            'daily_limit' => 'The AI explanation has reached its daily limit.',
            default => 'The AI explanation is not available right now.',
        };

        return $reason.' The next step above was chosen by NextStep’s safety rules and still applies.';
    }
}
