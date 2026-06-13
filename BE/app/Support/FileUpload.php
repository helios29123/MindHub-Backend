<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class FileUpload
{
    public function uploadLessonVideo(UploadedFile $file, int $lessonId): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid()->toString() . '.' . $extension;

        $path = $file->storeAs(
            'lessons/videos/' . $lessonId,
            $fileName,
            'public'
        );

        return asset('storage/' . $path);
    }

    public function uploadLessonAsset(UploadedFile $file, int $lessonId): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $storedFileName = Str::uuid()->toString() . ($extension !== '' ? '.' . $extension : '');
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
    }    public function deletePublicFileByUrl(?string $fileUrl): void
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
}