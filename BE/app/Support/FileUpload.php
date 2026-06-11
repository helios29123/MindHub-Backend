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
}