<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasVideo = $this->lesson_type === 'video' && !empty($this->video_url);

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_section_id' => $this->course_section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'content' => $this->content,

            /*
             * VIDEO-SEC-01:
             * Do not expose the raw video URL/path to the frontend.
             *
             * Before:
             * video_url = /videos/... or https://media... or storage path
             *
             * After:
             * video_url = null
             * The frontend must use the protected playback flow.
             */
            'video_url' => null,
            'has_video' => $hasVideo,
            'video_access_type' => $hasVideo ? 'private_stream' : null,

            /*
             * This endpoint will be implemented in VIDEO-SEC-03.
             * Keeping it here helps the frontend know the next API to call
             * without receiving the raw private file path.
             */
            'video_access_endpoint' => $hasVideo
                ? "/api/learn/lessons/{$this->id}/video-url"
                : null,

            'video_duration_seconds' => $this->video_duration_seconds !== null ? (int) $this->video_duration_seconds : null,
            'is_preview' => (bool) $this->is_preview,
            'status' => $this->status,
            'sort_order' => (int) $this->sort_order,

            'assets' => $this->assets ? $this->assets->map(function ($asset) {
                $hasFile = !empty($asset->file_url);

                return [
                    'id' => $asset->id,
                    'title' => $asset->title,

                    /*
                     * Do not expose the raw asset URL/path.
                     * Existing protected asset APIs should be used instead.
                     */
                    'file_url' => null,
                    'has_file' => $hasFile,

                    /*
                     * Existing route:
                     * POST /api/learn/assets/{assetId}/signed-url
                     */
                    'signed_url_endpoint' => $hasFile
                        ? "/api/learn/assets/{$asset->id}/signed-url"
                        : null,

                    /*
                     * Existing route:
                     * GET /api/learn/assets/{id}/download
                     */
                    'download_endpoint' => $hasFile
                        ? "/api/learn/assets/{$asset->id}/download"
                        : null,

                    'file_name' => $asset->file_name,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size !== null ? (int) $asset->file_size : null,
                    'note' => $asset->note,
                ];
            }) : [],
        ];
    }
}