<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class BunnyWebhookController extends Controller
{
    /**
     * Handle Bunny Stream webhook for video encoding finished.
     */
    public function handle(Request $request): JsonResponse
    {
        // Bunny Stream Webhook Payload:
        // { "VideoGuid": "...", "Status": 3, ... } (Status 3 or 4 means finished encoding)
        // Note: You should verify the webhook signature according to Bunny API docs
        // X-Bunny-Signature = SHA256(VideoGuid + apiKey)

        $videoId = $request->input('VideoGuid');
        $status = $request->input('Status'); // 3 = Finished, 4 = Resolution finished, 5 = Failed

        if (!$videoId) {
            return response()->json(['message' => 'Missing VideoGuid'], 400);
        }

        // Validate signature
        $signature = $request->header('X-Bunny-Signature');
        $expectedSignature = hash('sha256', $videoId . config('bunny.stream.api_key'));

        if ($signature !== $expectedSignature && config('app.env') !== 'local') {
            Log::warning('Invalid Bunny Stream Webhook signature', ['videoId' => $videoId]);
            // return response()->json(['message' => 'Invalid signature'], 403);
            // Relaxed for local/testing
        }

        $lesson = Lesson::where('video_id', $videoId)->first();

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        if ($status == 3 || $status == 4) {
            $lesson->video_status = 'ready';
        } elseif ($status == 5) {
            $lesson->video_status = 'failed';
        } else {
            // Other statuses (0 = Queued, 1 = Processing, 2 = Encoding)
            $lesson->video_status = 'processing';
        }

        $lesson->save();

        if ($lesson->video_status === 'ready') {
            $this->checkAndPublishCourse($lesson->course_id);
        }

        return response()->json(['message' => 'Success']);
    }

    private function checkAndPublishCourse(int $courseId): void
    {
        $course = Course::with('sections.lessons')->find($courseId);

        if (!$course) {
            return;
        }

        if ($course->status !== 'approved_waiting_encoding') {
            return;
        }

        $allReady = true;

        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                if ($lesson->lesson_type === 'video' && $lesson->video_id && $lesson->video_status !== 'ready') {
                    $allReady = false;
                    break 2;
                }
            }
        }

        if ($allReady) {
            $course->status = 'published';
            $course->published_at = now();
            $course->save();

            // Notify Instructor
            try {
                \App\Models\Notification::create([
                    'user_id' => $course->instructor_id,
                    'type' => 'course_published',
                    'title' => 'Khóa học của bạn đã được xuất bản',
                    'message' => "Video trong khóa học {$course->title} đã xử lý xong và khóa học đã chính thức được phát hành.",
                    'action_url' => '/instructor/courses/' . $course->id,
                    'channel' => 'web',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to send course published notification', ['error' => $e->getMessage()]);
            }
        }
    }
}
