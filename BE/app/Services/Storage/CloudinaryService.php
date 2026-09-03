<?php

namespace App\Services\Storage;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryService
{
    private ?Cloudinary $cloudinary = null;

    public function __construct()
    {
    }

    public function isConfigured(): bool
    {
        return !empty(config('cloudinary.cloud_name')) &&
               !empty(config('cloudinary.api_key')) &&
               !empty(config('cloudinary.api_secret'));
    }

    private function getCloudinary(): Cloudinary
    {
        if ($this->cloudinary !== null) {
            return $this->cloudinary;
        }

        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');

        if (
            empty($cloudName) ||
            empty($apiKey) ||
            empty($apiSecret)
        ) {
            throw new RuntimeException(
                'Cloudinary configuration is incomplete.'
            );
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => (bool) config('cloudinary.secure', true),
            ],
        ]);

        return $this->cloudinary;
    }

    /**
     * Upload an image to Cloudinary.
     *
     * @return array{
     *     url: string,
     *     public_id: string,
     *     width: int|null,
     *     height: int|null,
     *     format: string|null,
     *     bytes: int|null
     * }
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder
    ): array {
        $result = $this->uploadApi()->upload(
            $file->getRealPath(),
            [
                'resource_type' => 'image',
                'folder' => trim($folder, '/'),
                'use_filename' => false,
                'unique_filename' => true,
                'overwrite' => false,
            ]
        );

        return [
            'url' => (string) $result['secure_url'],
            'public_id' => (string) $result['public_id'],
            'width' => isset($result['width'])
                ? (int) $result['width']
                : null,
            'height' => isset($result['height'])
                ? (int) $result['height']
                : null,
            'format' => $result['format'] ?? null,
            'bytes' => isset($result['bytes'])
                ? (int) $result['bytes']
                : null,
        ];
    }

    /**
     * Delete an image from Cloudinary.
     */
    public function deleteImage(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        $this->uploadApi()->destroy(
            $publicId,
            [
                'resource_type' => 'image',
                'invalidate' => true,
            ]
        );
    }

    private function uploadApi(): UploadApi
    {
        return $this->getCloudinary()->uploadApi();
    }
}
