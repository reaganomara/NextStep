<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SafetyDemoController extends Controller
{
    /**
     * Run one predefined, fictional scenario from the safety override demonstration.
     */
    public function run(string $scenario): JsonResponse
    {
        $definition = config('safety_demo.scenarios')[$scenario] ?? null;

        abort_if($definition === null, 404);

        $category = $definition['override'];

        if ($category === null) {
            return response()->json([
                'scenario_id' => $scenario,
                'override' => false,
                'support_guidance' => $definition['support_guidance'],
            ]);
        }

        // Only these three fields are ever recorded. No request data is logged.
        $event = [
            'timestamp' => now()->utc()->toIso8601String(),
            'scenario_id' => $scenario,
            'override_category' => $category,
        ];

        Log::channel(config('safety_demo.log_channel'))->warning(json_encode($event));

        $override = config('safety_demo.overrides')[$category];

        return response()->json([
            'scenario_id' => $scenario,
            'override' => true,
            'override_category' => $category,
            'override_label' => $override['label'],
            'emergency_guidance' => $override['guidance'],
            'withheld_support_guidance' => $definition['support_guidance'],
            'logged' => true,
        ]);
    }
}
