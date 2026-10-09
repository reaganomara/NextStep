<?php

/*
|--------------------------------------------------------------------------
| Safety Override Demonstration
|--------------------------------------------------------------------------
|
| Fictional, predefined scenarios used by the safety override demonstration
| on the homepage. Nothing here is typed by a visitor and no AI is involved.
| A scenario with an "override" category replaces its ordinary
| anxiety-support guidance with the emergency guidance for that category.
|
*/

return [

    'log_channel' => 'safety_override',

    'overrides' => [

        'medical_emergency' => [
            'label' => 'Medical emergency',
            'guidance' => 'These could be signs of a medical emergency. Call 911 or go to the nearest emergency room now. Do not wait to see if it passes.',
        ],

        'mental_health_crisis' => [
            'label' => 'Mental health crisis',
            'guidance' => 'You deserve support right now. Call or text 988 to reach the Suicide & Crisis Lifeline. If you are in immediate danger, call 911.',
        ],

    ],

    'scenarios' => [

        'demo-ordinary-worry' => [
            'title' => 'Ordinary anxiety-support example',
            'description' => 'A fictional person notices a mild tension headache after a long day of studying and starts to worry about what it could mean.',
            'support_guidance' => 'It makes sense that this feels worrying. Try a slow breathing exercise: breathe in for 4 seconds and out for 6, for a few minutes. Consider setting the symptom searching aside for now, and if the headache continues or you stay concerned, a regular appointment with a healthcare professional is a reasonable next step.',
            'override' => null,
        ],

        'demo-emergency-warning' => [
            'title' => 'Emergency-warning example',
            'description' => 'A fictional person describes sudden chest pressure that spreads to one arm, along with trouble breathing.',
            'support_guidance' => 'Try a slow breathing exercise and set the symptom searching aside for now.',
            'override' => 'medical_emergency',
        ],

        'demo-override-priority' => [
            'title' => 'Override example: a calming request with a warning sign',
            'description' => 'A fictional person asks only for a relaxation tip to calm down, but also mentions having thoughts of ending their life.',
            'support_guidance' => 'Here is a relaxation tip: tense and release each muscle group, starting at your feet and moving upward.',
            'override' => 'mental_health_crisis',
        ],

    ],

];
