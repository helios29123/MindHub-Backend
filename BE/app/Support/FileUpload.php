<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class FileUpload
{
    public function uploadLessonVideo(UploadedFile $file, int $lessonId): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $fileName = Str::uuid()->toString() . ($extension !== '' ? '.' . $extension : '');

        $disk = $this->videoDisk();

        return $file->storeAs(
            'videos/lessons/' . $lessonId,
            $fileName,
            $disk
        );
    }

    public function uploadLessonAsset(UploadedFile $file, int $lessonId): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $storedFileName = Str::uuid()->toString() . ($extension !== '' ? '.' . $extension : '');

        /*
         * GD1 note:
         * Lesson assets are kept on the public disk for now to avoid breaking
         * existing asset download/display flow.
         *
         * Video protection is handled by uploadLessonVideo(), which stores
         * videos on the private_media disk and returns only an internal path.
         */
        $path = $file->storeAs(
            'lessons/assets/' . $lessonId,
            $storedFileName,
            'public'
        );

        return [
            'file_url' => asset('storage/' . $path),
            'file_name' => $originalName,
            'file_type' => $extension !== '' ? $extension : (string) $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    public function deletePublicFileByUrl(?string $fileUrl): void
    {
        if (!$fileUrl) {
            return;
        }

        $storageUrl = asset('storage/');

        if (!str_starts_with($fileUrl, $storageUrl)) {
            return;
        }

        $relativePath = ltrim(str_replace($storageUrl, '', $fileUrl), '/');

        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }

    public function deletePrivateMediaFile(?string $path): void
    {
        if (!$path || $this->looksLikeUrl($path)) {
            return;
        }

        Storage::disk($this->videoDisk())->delete(ltrim($path, '/'));
    }

    private function videoDisk(): string
    {
        return (string) config('filesystems.video_disk', 'private_media');
    }

    private function looksLikeUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            || str_starts_with($value, '/storage/')
            || str_starts_with($value, 'storage/')
            || str_starts_with($value, '/videos/');
    }
}
