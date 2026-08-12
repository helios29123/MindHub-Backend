<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "<h1>Testing AI Search logic directly</h1>";

try {
    $query = "facebook";
    echo "Query: $query<br/>";

    // 1. Load active courses
    $courses = \App\Models\Course::where('status', 'active')->with(['instructor', 'categories'])->get();
    echo "Loaded active courses count: " . count($courses) . "<br/>";

    // 2. Load config
    $aiConfig = config('ai');
    echo "Model configured: " . ($aiConfig['model'] ?? 'N/A') . "<br/>";
    echo "Base URL: " . ($aiConfig['base_url'] ?? 'N/A') . "<br/>";
    echo "API Key loaded: " . (empty($aiConfig['api_key']) ? 'NO' : 'YES') . "<br/>";

    // 3. Make HTTP request
    echo "Making request to Gemma...<br/>";
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer " . $aiConfig['api_key']
    ])->post($aiConfig['base_url'] . "/chat/completions", [
        'model' => $aiConfig['model'],
        'messages' => [
            ['role' => 'user', 'content' => "Test prompt. Trả lời bằng tiếng Việt."]
        ],
        'stream' => false
    ]);

    echo "Response status: " . $response->status() . "<br/>";
    if ($response->failed()) {
        echo "Response error body: <pre>" . htmlspecialchars($response->body()) . "</pre>";
    } else {
        echo "Response output: <pre>" . htmlspecialchars($response->json()['choices'][0]['message']['content'] ?? 'No content') . "</pre>";
    }

} catch (\Exception $e) {
    echo "<h2>Exception caught!</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}
