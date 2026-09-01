<?php

$libraryId = config('bunny.stream.library_id');
$apiKey = config('bunny.stream.api_key');

// Chỉ lấy những video chưa có duration
$lessons = \App\Models\Lesson::whereNotNull('video_id')
    ->where(function ($q) {
        $q->whereNull('video_duration_seconds')
          ->orWhere('video_duration_seconds', 0);
    })
    ->get();

$count = 0;
echo "Found " . $lessons->count() . " lessons to sync.\n";

// Dùng Http Pool để chạy song song (batch 20) cho nhanh
$chunks = $lessons->chunk(20);

foreach ($chunks as $chunk) {
    $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $libraryId, $apiKey) {
        $reqs = [];
        foreach ($chunk as $lesson) {
            $reqs[$lesson->id] = $pool->withHeaders(['AccessKey' => $apiKey])
                                      ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$lesson->video_id}");
        }
        return $reqs;
    });

    foreach ($responses as $lessonId => $response) {
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            $length = $response->json('length');
            if ($length !== null && $length > 0) {
                \App\Models\Lesson::where('id', $lessonId)->update(['video_duration_seconds' => $length]);
                $count++;
                echo "Updated Lesson ID {$lessonId} with length: {$length}s\n";
            } else {
                echo "Lesson ID {$lessonId} length is null or 0\n";
            }
        } else {
            echo "Failed to fetch for Lesson ID {$lessonId}\n";
        }
    }
}

echo "Successfully synced {$count} lessons.\n";

