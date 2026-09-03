<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$libraryId = config('bunny.stream.library_id') ?: env('BUNNY_STREAM_LIBRARY_ID', '724015');
$apiKey = config('bunny.stream.api_key') ?: env('BUNNY_STREAM_API_KEY', 'a450260d-c813-421d-b6868563ec80-1f80-4ef1');

// Lấy tất cả video có video_id
$lessons = \App\Models\Lesson::whereNotNull('video_id')
    ->where('video_id', '!=', '')
    ->get();

$count = 0;
echo "Found " . $lessons->count() . " lessons to sync.\n";

$chunks = $lessons->chunk(20);

foreach ($chunks as $chunk) {
    $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $libraryId, $apiKey) {
        $reqs = [];
        foreach ($chunk as $lesson) {
            $reqs[] = $pool->as((string) $lesson->id)
                           ->withHeaders(['AccessKey' => $apiKey])
                           ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$lesson->video_id}");
        }
        return $reqs;
    });

    foreach ($responses as $lessonId => $response) {
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            $length = (int) $response->json('length');
            if ($length > 0) {
                \App\Models\Lesson::where('id', (int) $lessonId)->update(['video_duration_seconds' => $length]);
                $count++;
                echo "Updated Lesson ID {$lessonId} with length: {$length}s\n";
            }
        }
    }
}

echo "Successfully synced {$count} lessons.\n";

