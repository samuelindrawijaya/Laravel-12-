<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function analyzeDailyLogs(string $logs): array
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$this->apiKey}";

        $response = Http::post($endpoint, [
            'contents' => [[
                'parts' => [[
                    'text' => "Berikan ringkasan dan saran dari makanan berikut:\n\n$logs\n\nBalas dalam format JSON:\n{\n\"summary\": \"...\",\n\"suggestion\": \"...\",\n\"concern\": \"...\",\n\"score\": 87\n}"
                ]]
            ]]
        ]);

        $text = $response->json('candidates.0.content.parts.0.text') ?? '{}';

        return json_decode($text, true);
    }
}
