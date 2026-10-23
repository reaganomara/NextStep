<?php

namespace App\Ai;

use Illuminate\Support\Facades\Http;

/**
 * Calls Groq's chat completions API (OpenAI-compatible) with Laravel's
 * built-in HTTP client.
 */
class GroqTextGenerator implements TextGenerator
{
    public const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private string $apiKey,
        private string $model,
        private int $maxTokens,
        private int $timeoutSeconds,
    ) {}

    public function generate(string $system, string $prompt): ?string
    {
        // Without a key there is nothing to call.
        if ($this->apiKey === '') {
            return null;
        }

        // No automatic retries: a failure simply falls back to fixed text.
        // throw() turns 4xx/5xx responses (such as 429 rate limits) into an
        // exception, which the caller records by type only.
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout($this->timeoutSeconds)
            ->post(self::ENDPOINT, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_completion_tokens' => $this->maxTokens,
                'temperature' => 0.6,
                'reasoning_effort' => 'low',
                'include_reasoning' => false,
            ])
            ->throw();

        // Anything other than a normal finish (for example an answer cut off
        // by the token limit) is treated as no answer.
        if ($response->json('choices.0.finish_reason') !== 'stop') {
            return null;
        }

        $content = $response->json('choices.0.message.content');

        return is_string($content) ? $content : null;
    }
}
