<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuidedDemoPageTest extends TestCase
{
    /** The HTML of just the guided walkthrough section. */
    private function guidedSection(): string
    {
        $html = $this->get('/')->assertOk()->getContent();

        $start = strpos($html, '<section id="guided-demo"');
        $end = strpos($html, '<section id="safety-demo"');

        $this->assertNotFalse($start, 'Guided section is missing.');
        $this->assertNotFalse($end, 'Existing safety demonstration is missing.');
        $this->assertLessThan($end, $start, 'Guided section should come before the existing demonstration.');

        return substr($html, $start, $end - $start);
    }

    public function test_guided_section_shows_three_steps_and_explains_it_is_a_fictional_demo(): void
    {
        $section = $this->guidedSection();

        $this->assertStringContainsString('Walk through an example', $section);
        $this->assertStringContainsString('Demonstration only', $section);
        $this->assertStringContainsString('You will not be asked anything about yourself and nothing you type is collected.', $section);
        $this->assertStringContainsString('The safety checks never use AI.', $section);

        foreach (['Step 1: Choose a fictional example', 'Step 2: What NextStep checks, in order', 'Step 3: The next step'] as $heading) {
            $this->assertStringContainsString($heading, $section);
        }
    }

    public function test_guided_section_lists_all_four_fictional_people_with_their_endpoints(): void
    {
        $section = $this->guidedSection();
        $positions = [];

        foreach (config('guided_demo.scenarios') as $id => $scenario) {
            $this->assertStringContainsString(e($scenario['story']), $section);
            $this->assertStringContainsString('data-url="/guided-demo/'.$id.'"', $section);
            $positions[] = strpos($section, 'data-url="/guided-demo/'.$id.'"');
        }

        $this->assertSame(4, substr_count($section, 'class="person"'));
        $this->assertSame(4, substr_count($section, '>Fictional<'));
        $this->assertSame($positions, array_values(array_unique($positions)));
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'People should appear in the configured order.');
    }

    public function test_result_step_always_includes_disclaimer_and_emergency_numbers(): void
    {
        $section = $this->guidedSection();

        $this->assertStringContainsString('Not identifying warning signs does not guarantee that a symptom is harmless.', $section);
        $this->assertStringContainsString('href="tel:911"', $section);
        $this->assertStringContainsString('href="tel:988"', $section);
        $this->assertStringContainsString('href="tel:18002221222"', $section);
    }

    public function test_page_has_no_way_to_enter_symptoms_or_personal_information(): void
    {
        $html = strtolower($this->get('/')->assertOk()->getContent());

        foreach (['<input', '<textarea', '<select', '<form', 'contenteditable'] as $field) {
            $this->assertStringNotContainsString($field, $html);
        }
    }

    public function test_guided_section_never_inserts_server_text_as_html(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('innerHTML', $html);
        $this->assertStringNotContainsString('insertAdjacentHTML', $html);
        $this->assertStringNotContainsString('document.write', $html);
    }

    public function test_existing_sections_are_still_present_in_order(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'aria-label="Emergency contacts"',
                'id="safety-disclaimer"',
                'id="emergency"',
                'id="guided-demo"',
                'id="safety-demo"',
                'Safety override demonstration',
                'id="awareness"',
            ], false);
    }
}
