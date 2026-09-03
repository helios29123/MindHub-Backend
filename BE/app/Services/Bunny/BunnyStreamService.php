<?php

namespace App\Services\Bunny;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class BunnyStreamService
{
    private string $libraryId;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->libraryId = (string) config('bunny.stream.library_id');
        $this->apiKey = (string) config('bunny.stream.api_key');
        $this->baseUrl = "https://video.bunnycdn.com/library/{$this->libraryId}";
    }

    private function getHeaders(): array
    {
        return [
            'AccessKey' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get or create a collection by name.
     */
    public function getOrCreateCollection(string $name): string
    {
        // Fetch collections (might need pagination in a huge library, but let's assume we can find it)
        $response = Http::withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/collections", [
                'search' => $name,
                'itemsPerPage' => 100,
            ]);

        if ($response->successful()) {
            $collections = $response->json('items', []);
            foreach ($collections as $collection) {
                if ($collection['name'] === $name) {
                    return $collection['guid'];
                }
            }
        }

        // Create if not found
        $createResponse = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/collections", [
                'name' => $name,
            ]);

        if ($createResponse->successful()) {
            return $createResponse->json('guid');
        }

        Log::error('Failed to create Bunny Stream collection', ['response' => $createResponse->body()]);
        throw new BusinessException('Không thể tạo bộ sưu tập trên máy chủ video.', 500);
    }

    /**
     * Update collection name
     */
    public function updateCollection(string $collectionId, string $newName): void
    {
        if (!$collectionId) return;

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/collections/{$collectionId}", [
                'name' => $newName,
            ]);

        if (!$response->successful()) {
            Log::warning("Failed to update Bunny Stream collection {$collectionId}", ['response' => $response->body()]);
        }
    }

    /**
     * Delete collection
     */
    public function deleteCollection(string $collectionId): void
    {
        if (!$collectionId) return;

        $response = Http::withHeaders($this->getHeaders())
            ->delete("{$this->baseUrl}/collections/{$collectionId}");

        if (!$response->successful()) {
            Log::warning("Failed to delete Bunny Stream collection {$collectionId}", ['response' => $response->body()]);
        }
    }

    /**
     * Create a video object and return its ID
     */
    public function createVideo(string $title, string $collectionId): string
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/videos", [
                'title' => $title,
                'collectionId' => $collectionId,
            ]);

        if ($response->successful()) {
            return $response->json('guid');
        }

        Log::error('Failed to create Bunny Stream video object', ['response' => $response->body()]);
        throw new BusinessException('Không thể khởi tạo video trên máy chủ.', 500);
    }

    /**
     * Upload a video file to an existing video object
     */
    public function uploadVideo(string $videoId, string $filePath): void
    {
        $fileStream = fopen($filePath, 'r');
        if (!$fileStream) {
            throw new BusinessException('Không thể đọc file video để upload.', 500);
        }

        $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->withBody($fileStream, 'application/octet-stream')
            ->put("{$this->baseUrl}/videos/{$videoId}");

        if (is_resource($fileStream)) {
            fclose($fileStream);
        }

        if (!$response->successful()) {
            Log::error('Failed to upload video to Bunny Stream', ['response' => $response->body()]);
            throw new BusinessException('Quá trình upload video thất bại.', 500);
        }
    }

    /**
     * Delete a video
     */
    public function deleteVideo(string $videoId): void
    {
        if (!$videoId) return;

        $response = Http::withHeaders($this->getHeaders())
            ->delete("{$this->baseUrl}/videos/{$videoId}");

        if (!$response->successful()) {
            Log::warning("Failed to delete Bunny Stream video {$videoId}", ['response' => $response->body()]);
        }
    }
}
