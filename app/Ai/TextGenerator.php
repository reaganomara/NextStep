<?php

namespace App\Ai;

/**
 * Something that turns a fixed instruction and prompt into a short text.
 *
 * The real implementation calls Groq. Tests swap in a fake so they never
 * make network calls or cost money.
 */
interface TextGenerator
{
    /**
     * Return the generated text, or null if no usable answer came back.
     * May throw on network or API errors; callers must handle that.
     */
    public function generate(string $system, string $prompt): ?string;
}
