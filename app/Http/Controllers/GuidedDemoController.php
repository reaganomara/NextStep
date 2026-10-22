<?php

namespace App\Http\Controllers;

use App\Safety\NextStepDecider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GuidedDemoController extends Controller
{
    /**
     * Walk through one predefined, fictional example and return its next step.
     *
     * Nothing sent with the request is read. Only the example ID in the URL
     * is used, and it must be one of the predefined examples.
     */
    public function run(string $scenario, NextStepDecider $decider): JsonResponse
    {
        $result = $decider->forScenario($scenario);

        abort_if($result === null, 404);

        $override = $result['override_category'] !== null;

        if ($override) {
            // Same three fields, channel and format as the safety override demo.
            Log::channel(config('safety_demo.log_channel'))->warning(json_encode([
                'timestamp' => now()->utc()->toIso8601String(),
                'scenario_id' => $scenario,
                'override_category' => $result['override_category'],
            ]));
        }

        return response()->json($result + [
            'override' => $override,
            'logged' => $override,
        ]);
    }
}
