<?php

/*
|--------------------------------------------------------------------------
| Guided Next-Step Demonstration
|--------------------------------------------------------------------------
|
| Fictional, predefined examples for the planned "Walk through an example"
| guide. Visitors only choose an example; they never type or answer
| anything about themselves. Each example's answers ("signals") are fixed
| here, and App\Safety\NextStepDecider turns them into one next step.
|
| Emergency and crisis wording is not repeated here. It comes from
| config/safety_demo.php so the 911 and 988 messages live in one place.
|
*/

return [

    // The questions NextStep checks, always in this order. The first "yes"
    // decides the next step, so emergencies are always checked first.
    'checks' => [
        'emergency_signs' => 'Are there emergency warning signs?',
        'crisis_signs' => 'Are there crisis or safety concerns?',
        'needs_professional' => 'Does this need a healthcare professional soon?',
    ],

    'outcomes' => [

        'emergency' => [
            'label' => 'Call 911 now',
            'override_category' => 'medical_emergency',
        ],

        'crisis' => [
            'label' => 'Call or text 988 now',
            'override_category' => 'mental_health_crisis',
        ],

        'professional' => [
            'label' => 'Contact a healthcare professional soon',
            'override_category' => null,
            'guidance' => 'This is worth checking with a healthcare professional. Contact your doctor or a clinic in the next few days to make an appointment. If new symptoms appear, such as trouble breathing or chest pain, call 911.',
        ],

        'self_care' => [
            'label' => 'Try anxiety-support tools',
            'override_category' => null,
            'guidance' => 'No warning signs were identified in this example. Try a slow breathing exercise: breathe in for 4 seconds and out for 6, for a few minutes. Consider setting symptom searching aside for now. If the symptom continues, gets worse, or you stay worried, contact a healthcare professional. Not identifying warning signs does not guarantee that a symptom is harmless.',
        ],

    ],

    'scenarios' => [

        'guided-maya-headache' => [
            'name' => 'Maya',
            'story' => 'Maya, a fictional student, has had a mild headache since the afternoon after a long day of studying. She read about a rare illness online and keeps checking her symptoms.',
            'signals' => [
                'emergency_signs' => false,
                'crisis_signs' => false,
                'needs_professional' => false,
            ],
            'reasons' => [
                'No emergency warning signs are described.',
                'No crisis or safety concerns are described.',
                'The headache is mild and recent, and repeated symptom checking can make anxiety worse.',
            ],
        ],

        'guided-jordan-cough' => [
            'name' => 'Jordan',
            'story' => 'Jordan, a fictional person, has had a cough for three weeks. It is not getting worse, and there is no trouble breathing, chest pain, or coughing up blood.',
            'signals' => [
                'emergency_signs' => false,
                'crisis_signs' => false,
                'needs_professional' => true,
            ],
            'reasons' => [
                'No emergency warning signs are described.',
                'No crisis or safety concerns are described.',
                'A cough that has lasted three weeks is worth checking with a healthcare professional.',
            ],
        ],

        'guided-sam-chest-pressure' => [
            'name' => 'Sam',
            'story' => 'Sam, a fictional person, suddenly feels pressure in the chest while climbing stairs, along with sweating and shortness of breath.',
            'signals' => [
                'emergency_signs' => true,
                'crisis_signs' => false,
                'needs_professional' => false,
            ],
            'reasons' => [
                'Sudden chest pressure with sweating and shortness of breath are emergency warning signs.',
                'Emergency warning signs are always checked first and take priority over everything else.',
            ],
        ],

        'guided-alex-hopeless' => [
            'name' => 'Alex',
            'story' => 'Alex, a fictional person, has been feeling hopeless for several days and has had thoughts of not wanting to be alive.',
            'signals' => [
                'emergency_signs' => false,
                'crisis_signs' => true,
                'needs_professional' => false,
            ],
            'reasons' => [
                'Thoughts of not wanting to be alive are a crisis and safety concern.',
                'Crisis support takes priority over ordinary anxiety-support tools.',
            ],
        ],

    ],

];
