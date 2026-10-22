<?php

namespace Tests\Feature;

use App\Safety\NextStepDecider;
use InvalidArgumentException;
use Tests\TestCase;

class GuidedDemoDecisionTest extends TestCase
{
    private function signals(bool $emergency, bool $crisis, bool $professional): array
    {
        return [
            'emergency_signs' => $emergency,
            'crisis_signs' => $crisis,
            'needs_professional' => $professional,
        ];
    }

    private function outcome(bool $emergency, bool $crisis, bool $professional): string
    {
        return (new NextStepDecider)->decide($this->signals($emergency, $crisis, $professional))['outcome'];
    }

    public function test_each_single_signal_leads_to_its_own_step(): void
    {
        $this->assertSame('self_care', $this->outcome(false, false, false));
        $this->assertSame('professional', $this->outcome(false, false, true));
        $this->assertSame('crisis', $this->outcome(false, true, false));
        $this->assertSame('emergency', $this->outcome(true, false, false));
    }

    public function test_emergency_always_takes_priority(): void
    {
        foreach ([[true, true, false], [true, false, true], [true, true, true]] as [$e, $c, $p]) {
            $this->assertSame('emergency', $this->outcome($e, $c, $p));
        }
    }

    public function test_crisis_takes_priority_over_professional_care(): void
    {
        $this->assertSame('crisis', $this->outcome(false, true, true));
    }

    public function test_checks_run_in_order_and_later_checks_are_skipped(): void
    {
        $result = (new NextStepDecider)->decide($this->signals(true, true, true));

        $this->assertSame([
            ['check' => 'emergency_signs', 'result' => 'yes'],
            ['check' => 'crisis_signs', 'result' => 'skipped'],
            ['check' => 'needs_professional', 'result' => 'skipped'],
        ], $result['checks']);

        $result = (new NextStepDecider)->decide($this->signals(false, false, false));

        $this->assertSame(['no', 'no', 'no'], array_column($result['checks'], 'result'));
    }

    public function test_missing_or_non_boolean_signals_are_rejected(): void
    {
        $bad = [
            [],
            ['emergency_signs' => false, 'crisis_signs' => false],
            ['emergency_signs' => 'no', 'crisis_signs' => false, 'needs_professional' => false],
            ['emergency_signs' => 0, 'crisis_signs' => false, 'needs_professional' => false],
        ];

        foreach ($bad as $signals) {
            try {
                (new NextStepDecider)->decide($signals);
                $this->fail('Invalid signals were accepted: '.json_encode($signals));
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_each_fictional_example_gives_the_expected_step(): void
    {
        $expected = [
            'guided-maya-headache' => 'self_care',
            'guided-jordan-cough' => 'professional',
            'guided-sam-chest-pressure' => 'emergency',
            'guided-alex-hopeless' => 'crisis',
        ];

        $this->assertSame(array_keys($expected), array_keys(config('guided_demo.scenarios')));

        foreach ($expected as $id => $outcome) {
            $this->assertSame($outcome, (new NextStepDecider)->forScenario($id)['outcome'], $id);
        }
    }

    public function test_emergency_and_crisis_steps_reuse_existing_safety_wording(): void
    {
        $sam = (new NextStepDecider)->forScenario('guided-sam-chest-pressure');
        $alex = (new NextStepDecider)->forScenario('guided-alex-hopeless');

        $this->assertSame('medical_emergency', $sam['override_category']);
        $this->assertSame(config('safety_demo.overrides.medical_emergency.guidance'), $sam['guidance']);
        $this->assertStringContainsString('911', $sam['guidance']);

        $this->assertSame('mental_health_crisis', $alex['override_category']);
        $this->assertSame(config('safety_demo.overrides.mental_health_crisis.guidance'), $alex['guidance']);
        $this->assertStringContainsString('988', $alex['guidance']);
    }

    public function test_non_emergency_steps_stay_cautious_and_never_falsely_reassure(): void
    {
        $maya = (new NextStepDecider)->forScenario('guided-maya-headache');
        $jordan = (new NextStepDecider)->forScenario('guided-jordan-cough');

        $this->assertNull($maya['override_category']);
        $this->assertStringContainsString('does not guarantee that a symptom is harmless', $maya['guidance']);
        $this->assertStringContainsString('call 911', $jordan['guidance']);

        foreach ([$maya, $jordan] as $result) {
            $text = strtolower($result['guidance'].' '.implode(' ', $result['reasons']));

            foreach (['just anxiety', 'nothing to worry', 'you are fine', 'diagnos'] as $phrase) {
                $this->assertStringNotContainsString($phrase, $text);
            }
        }
    }

    public function test_every_example_is_complete_fictional_and_separate_from_existing_demos(): void
    {
        $existing = array_keys(config('safety_demo.scenarios'));

        foreach (config('guided_demo.scenarios') as $id => $scenario) {
            $this->assertStringStartsWith('guided-', $id);
            $this->assertNotContains($id, $existing);
            $this->assertStringContainsString('fictional', $scenario['story']);
            $this->assertNotEmpty($scenario['name']);
            $this->assertNotEmpty($scenario['reasons']);

            $result = (new NextStepDecider)->forScenario($id);
            $this->assertCount(3, $result['checks']);
            $this->assertNotEmpty($result['label']);
            $this->assertNotEmpty($result['guidance']);
        }
    }

    public function test_unknown_example_returns_nothing(): void
    {
        $this->assertNull((new NextStepDecider)->forScenario('not-a-real-example'));
        $this->assertNull((new NextStepDecider)->forScenario('demo-emergency-warning'));
    }
}
