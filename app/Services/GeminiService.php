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
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->apiKey}";

        $prompt = <<<EOT
        Kamu adalah seorang dokter spesialis penyakit dalam (gastroenterologi) dan juga psikolog klinis.

        Analisis data berikut dari sisi pola makan dan kondisi mental pasien. Tanggapi dengan empati dan pendekatan personal seolah kamu sedang menenangkan dan memotivasi pasien yang sedang berjuang dengan GERD atau kecemasan.

        Berikut data hari ini:

        $logs

        Tampilkan jawaban dalam format JSON:
        {
        "summary": "Ringkasan harian dari kondisi lambung dan mental user, disampaikan dengan empati dan kalimat positif.",
        "suggestion": "Saran konkrit untuk memperbaiki pola makan dan kondisi psikologis, dengan bahasa suportif.",
        "concern": "Hal yang perlu diwaspadai secara fisik/mental namun tetap dikemas dengan tenang.",
        "score": 1-100
        }
        EOT;

        $response = Http::withOptions([
            'verify' => false // Jangan gunakan ini di production
        ])->post($endpoint, [
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]]
        ]);
        // Jika gagal koneksi atau status tidak 200
        if (!$response->successful()) {
            return [
                'summary' => 'Gagal terhubung dengan AI',
                'suggestion' => 'Silakan coba beberapa saat lagi',
                'concern' => 'Respons tidak valid dari Gemini',
                'score' => 0
            ];
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        $text = trim($text, "\"`\njson "); // Remove extra quotes, backticks, newlines, and spaces so it can be parsed correctly

        // Decode JSON
        $data = json_decode($text, true);
        // dd($data);

        try {
            $data = json_decode($text, true);
            return is_array($data) ? $data : [
                'summary' => 'Format data dari AI tidak sesuai',
                'suggestion' => 'Periksa ulang format JSON',
                'concern' => 'Gemini membalas dengan teks n on-JSON',
                'score' => 0
            ];
        } catch (\JsonException $e) {
            return [
                'summary' => 'Gagal mengurai jawaban AI',
                'suggestion' => 'Cek apakah balasan valid JSON',
                'concern' => $e->getMessage(),
                'score' => 0
            ];
        }
    }
}
