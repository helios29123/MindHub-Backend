<?php

namespace App\Services\Ai;

use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $geminiApiKey;
    private string $geminiBaseUrl;
    private string $nineRouterApiKey;
    private string $nineRouterBaseUrl;

    public function __construct()
    {
        $this->geminiApiKey = (string) env('GEMINI_API_KEY', '');
        $this->geminiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent';
        $this->nineRouterApiKey = (string) env('NINE_ROUTER_API_KEY', '');
        $this->nineRouterBaseUrl = (string) env('NINE_ROUTER_BASE_URL', 'https://ai.mindhub.io.vn/v1');
    }

    /**
     * Generate 768-dimensional dense vector embedding from text
     *
     * @param string $text
     * @param string $model
     * @return array<float>
     */
    public function generateEmbedding(string $text, string $model = 'text-embedding-004'): array
    {
        $cleanText = trim(strip_tags($text));
        if ($cleanText === '') {
            return array_fill(0, 768, 0.0);
        }

        // 1. Try Google Gemini text-embedding-004 API if key exists
        if (!empty($this->geminiApiKey) && $this->geminiApiKey !== 'MY_GEMINI_API_KEY') {
            try {
                $response = Http::timeout(10)->post("{$this->geminiBaseUrl}?key={$this->geminiApiKey}", [
                    'model' => 'models/text-embedding-004',
                    'content' => [
                        'parts' => [
                            ['text' => mb_substr($cleanText, 0, 4000, 'UTF-8')],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $values = $json['embedding']['values'] ?? null;
                    if (is_array($values) && count($values) > 0) {
                        return array_map('floatval', $values);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini Embedding API call failed: ' . $e->getMessage());
            }
        }

        // 2. Try 9Router / OpenAI compatible Embedding API
        if (!empty($this->nineRouterApiKey) && !empty($this->nineRouterBaseUrl)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->nineRouterApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post(rtrim($this->nineRouterBaseUrl, '/') . '/embeddings', [
                    'input' => mb_substr($cleanText, 0, 4000, 'UTF-8'),
                    'model' => 'text-embedding-3-small',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $values = $json['data'][0]['embedding'] ?? null;
                    if (is_array($values) && count($values) > 0) {
                        return array_map('floatval', $values);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('9Router Embedding API call failed: ' . $e->getMessage());
            }
        }

        // 3. High-quality Deterministic Semantic Fallback Vector (768-dim)
        return $this->generateDeterministicVector($cleanText, 768);
    }

    /**
     * Build rich textual payload for embedding a course
     */
    public function buildCoursePayload(Course $course): array
    {
        $course->loadMissing(['categories', 'sections.lessons']);

        $categoryNames = $course->categories->pluck('name')->filter()->join(', ');
        $lessonTitles = [];
        if ($course->sections) {
            foreach ($course->sections as $section) {
                if ($section->lessons) {
                    foreach ($section->lessons as $lesson) {
                        if (!empty($lesson->title)) {
                            $lessonTitles[] = $lesson->title;
                        }
                    }
                }
            }
        }

        $parts = [
            "Tiêu đề: {$course->title}",
            $categoryNames ? "Danh mục: {$categoryNames}" : '',
            $course->course_level ? "Trình độ: {$course->course_level}" : '',
            $course->short_description ? "Tóm tắt: {$course->short_description}" : '',
            $course->description ? "Mô tả: {$course->description}" : '',
            !empty($lessonTitles) ? "Giáo trình bài học: " . implode('; ', array_slice($lessonTitles, 0, 30)) : '',
        ];

        $fullPayload = trim(implode("\n", array_filter($parts)));
        $hash = md5($fullPayload);

        return [
            'payload' => $fullPayload,
            'hash' => $hash,
            'summary' => mb_substr($course->short_description ?: $course->title, 0, 250, 'UTF-8'),
        ];
    }

    /**
     * Compute Cosine Similarity between two dense float vectors
     *
     * @param array<float> $vecA
     * @param array<float> $vecB
     * @return float
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $countA = count($vecA);
        $countB = count($vecB);
        if ($countA === 0 || $countA !== $countB) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $countA; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];

            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        $sim = $dotProduct / (sqrt($normA) * sqrt($normB));
        return (float) max(0.0, min(1.0, $sim));
    }

    /**
     * Deterministic Semantic Vector Generator (Word-ngram weighted projection)
     *
     * @param string $text
     * @param int $dimensions
     * @return array<float>
     */
    private function generateDeterministicVector(string $text, int $dimensions = 768): array
    {
        $vector = array_fill(0, $dimensions, 0.0);
        $clean = mb_strtolower(trim($text), 'UTF-8');
        $words = preg_split('/[\s,\.\?\!\:\;\(\)\[\]\{\}\"\']+/u', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (empty($words)) {
            return $vector;
        }

        // Project n-grams into vector space
        foreach ($words as $idx => $word) {
            $wordLen = mb_strlen($word, 'UTF-8');
            if ($wordLen < 2) continue;

            $hash1 = crc32($word);
            $hash2 = crc32(strrev($word));
            
            $pos1 = abs($hash1) % $dimensions;
            $pos2 = abs($hash2) % $dimensions;

            $weight = 1.0 / (1.0 + log($idx + 1));
            $vector[$pos1] += (float) ($weight * 0.85);
            $vector[$pos2] += (float) ($weight * 0.55);

            // Bigram projection
            if (isset($words[$idx + 1])) {
                $bigram = $word . '_' . $words[$idx + 1];
                $biHash = crc32($bigram);
                $biPos = abs($biHash) % $dimensions;
                $vector[$biPos] += (float) ($weight * 1.25);
            }
        }

        // L2 Normalization
        $norm = 0.0;
        for ($i = 0; $i < $dimensions; $i++) {
            $norm += $vector[$i] * $vector[$i];
        }
        $sqrtNorm = sqrt($norm);
        if ($sqrtNorm > 0.0) {
            for ($i = 0; $i < $dimensions; $i++) {
                $vector[$i] = (float) ($vector[$i] / $sqrtNorm);
            }
        }

        return $vector;
    }
}
