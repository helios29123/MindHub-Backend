<?php

namespace App\Services\Bunny;

use App\Exceptions\BusinessException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class BunnyStreamService
{
    private string $libraryId;
    private string $apiKey;
    private string $cdnHostname;
    private string $baseUrl = 'https://video.bunnycdn.com/library';

    public function __construct()
    {
        $this->libraryId = (string) config('bunny.stream.library_id');
        $this->apiKey = (string) config('bunny.stream.api_key');
        $this->cdnHostname = (string) config('bunny.stream.cdn_hostname');

        if (empty($this->libraryId) || empty($this->apiKey) || empty($this->cdnHostname)) {
            throw new BusinessException('Cấu hình Bunny.net Stream chưa hợp lệ.', 500);
        }
    }

    /**
     * Uploads a video to Bunny Stream and returns the video GUID.
     *
     * @param UploadedFile $file The video file to upload.
     * @param string $title The title for the video.
     * @return string The Bunny Stream video GUID.
     */
    public function uploadVideo(UploadedFile $file, string $title): string
    {
        $guid = $this->createVideoEntry($title);
        $this->uploadVideoContent($guid, $file);
        return $guid;
    }

    private function createVideoEntry(string $title): string
    {
        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/{$this->libraryId}/videos", [
            'title' => $title,
        ]);

        if ($response->failed()) {
            throw new BusinessException('Không thể khởi tạo video trên Bunny Stream.', 500);
        }

        return (string) $response->json('guid');
    }

    private function uploadVideoContent(string $guid, UploadedFile $file): void
    {
        $stream = fopen($file->getRealPath(), 'rb');
        
        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
            'Content-Type' => 'application/octet-stream',
        ])
        ->withBody($stream, 'application/octet-stream')
        ->put("{$this->baseUrl}/{$this->libraryId}/videos/{$guid}");

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($response->failed()) {
            throw new BusinessException('Lỗi tải video lên Bunny Stream.', 500);
        }
    }
}
