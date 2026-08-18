<?php
namespace App\Http\Resources\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasAccess = $this->resource->is_preview || ($this->additional['has_access'] ?? false);
        $hasVideo = $this->lesson_type === 'video' && (!empty($this->video_url) || !empty($this->video_id));
        return [
            'id' => $this->id,
            'course_section_id' => $this->course_section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'type' => $this->lesson_type,
            'is_preview' => (bool) $this->is_preview,
            'isPreview' => (bool) $this->is_preview,
            'sort_order' => (int) $this->sort_order,
            'video_duration_seconds' => $this->video_duration_seconds !== null
                ? (int) $this->video_duration_seconds
                : null,
            'duration' => $this->formatDuration((int) ($this->video_duration_seconds ?? 0)),
            'content' => $hasAccess ? $this->content : null,
            /*
             * Do not expose the raw video path/URL.
             * Frontend must call the signed stream endpoint.
             */
            'video_url' => $hasVideo && $this->video_provider === 'bunny' ? 'https://' . config('bunny.stream.cdn_hostname') . '/' . $this->video_id . '/playlist.m3u8' : null,
            'video_provider' => $this->video_provider,
            'video_id' => $this->video_id,
            'has_video' => $hasVideo,
            'video_access_type' => $hasVideo ? 'private_stream' : null,
            'video_access_endpoint' => '/api/learn/lessons/' . $this->id . '/video-url',
        ];
    }
    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '00:00';
        }
        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
