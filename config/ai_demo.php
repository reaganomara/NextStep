<?php

/*
|--------------------------------------------------------------------------
| AI Explanation Demonstration
|--------------------------------------------------------------------------
|
| An optional, AI-written explanation for the guided walkthrough's
| fictional examples. NextStep's safety rules always choose the next step
| first. The AI only explains a step that has already been chosen, and only
| for non-emergency outcomes. Emergency and crisis examples never reach it.
|
| The AI provider is Groq. The feature is OFF unless AI_DEMO_ENABLED=true
| is set. The API key is read from GROQ_API_KEY (see config/services.php)
| and is never sent to the browser.
|
*/

return [

    'enabled' => (bool) env('AI_DEMO_ENABLED', false),

    // A Groq production model available on the free plan. Groq retired
    // llama-3.1-8b-instant for free use and names this as its replacement.
    'model' => env('AI_DEMO_MODEL', 'openai/gpt-oss-20b'),

    'provider' => 'Groq',

    // The only outcomes the AI may ever explain. Emergency and crisis
    // outcomes are deliberately missing from this list.
    'allowed_outcomes' => ['self_care', 'professional'],

    // Cost and usage limits. The token cap includes the model's hidden
    // reasoning, so it is larger than the 2-3 sentences it allows.
    'max_tokens' => 1024,
    'timeout_seconds' => 15,
    'daily_limit' => (int) env('AI_DEMO_DAILY_LIMIT', 50),
    'cache_hours' => 24,

    // Lower values make replies more predictable.
    'temperature' => 0.2,

    // Longer answers are discarded and the fixed guidance is used instead.
    'max_characters' => 1000,

    // Replies containing any of these are discarded and the fixed guidance
    // is shown instead. Matching ignores case. Terms match whole words
    // (plurals included); a trailing * matches any word starting with it,
    // so "diagnos*" catches "diagnosis" and "diagnosed".
    // Erring on the side of blocking is safe: the fallback is always safe.
    'blocked_terms' => [
        // Diagnosing or naming conditions
        'diagnos*', 'condition', 'disease', 'illness', 'disorder', 'syndrome',
        'cancer', 'tumor', 'tumour', 'stroke', 'heart attack', 'aneurysm',
        'meningitis', 'migraine', 'infection', 'pneumonia', 'tuberculosis',
        'bronchitis', 'asthma', 'covid', 'flu', 'allerg*', 'panic attack',
        'panic disorder', 'hypochondria',

        // Medication and doses
        'medication', 'medicine', 'drug', 'dose', 'dosage', 'pill', 'tablet',
        'ibuprofen', 'acetaminophen', 'paracetamol', 'tylenol', 'advil',
        'aspirin', 'antibiotic', 'prescri*', 'supplement',

        // False reassurance
        'harmless', 'nothing to worry', 'nothing serious', 'not serious',
        'nothing wrong', 'definitely fine', 'perfectly fine', 'just anxiety',
        'only anxiety', 'just stress', 'guarantee', 'certainly', 'no need to worry',
    ],

    // Also blocked: links; numbers of 3 or more digits (for example made-up
    // phone numbers, since the page already shows the correct ones); and
    // telling the reader they don't need care or don't need to worry, in
    // any form (don't, doesn't, won't, does not, no need...).
    'blocked_patterns' => [
        '/https?:|www\./i',
        '/\d{3,}/',
        "/\b(?:(?:do|does|did|will|would|should)\s*(?:n't|not)|won't|no)\s+(?:need|have)\s+to\s+(?:see|visit|contact|call|go|worry|be\s+worried)\b/i",
        "/\b(?:do|does|should)\s*(?:n't|not)\s+(?:need\s+to\s+)?worry\b/i",
    ],

    'system_prompt' => <<<'PROMPT'
        You write short explanations for an educational demonstration about health anxiety. Every person you are told about is fictional.

        NextStep's safety rules have already chosen the next step. Your only job is to explain, calmly and in plain language, why that step fits the fictional person's situation.

        Rules:
        - Do not change the next step, suggest a different one, or add new options.
        - Do not diagnose, name possible illnesses or conditions, or mention medications or doses.
        - Do not promise that the symptom is harmless.
        - Write 2 or 3 short sentences of plain text, with no lists, headings, or formatting.
        PROMPT,

];
