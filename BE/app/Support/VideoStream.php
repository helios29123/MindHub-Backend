<?php
namespace App\Support;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
final class VideoStream
{
    public function stream(string $absolutePath, Request $request): StreamedResponse
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        $size = filesize($absolutePath);
        if ($size === false || $size <= 0) {
            throw new BusinessException('Không tìm thấy video.', 404);
        }
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $range = $request->headers->get('Range');
        if ($range !== null) {
            if (!preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
                return $this->rangeNotSatisfiable($size);
            }
            if ($matches[1] === '' && $matches[2] === '') {
                return $this->rangeNotSatisfiable($size);
            }
            if ($matches[1] === '') {
                $suffixLength = (int) $matches[2];
                if ($suffixLength <= 0) {
                    return $this->rangeNotSatisfiable($size);
                }
                $start = max(0, $size - $suffixLength);
            } else {
                $start = (int) $matches[1];
            }
            if ($matches[2] !== '' && $matches[1] !== '') {
                $end = min((int) $matches[2], $size - 1);
            }
            if ($start > $end || $start >= $size) {
                return $this->rangeNotSatisfiable($size);
            }
            $status = 206;
        }
        $length = $end - $start + 1;
        $headers = [
            'Content-Type' => $this->guessMimeType($absolutePath),
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Content-Disposition' => 'inline; filename="lesson-video"',
        ];
        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }
        return response()->stream(function () use ($absolutePath, $start, $length): void {
            $handle = fopen($absolutePath, 'rb');
            if ($handle === false) {
                return;
            }
            fseek($handle, $start);
            $remaining = $length;
            $chunkSize = 1024 * 1024;
            while ($remaining > 0 && !feof($handle)) {
                $readLength = min($chunkSize, $remaining);
                $buffer = fread($handle, $readLength);
                if ($buffer === false || $buffer === '') {
                    break;
                }
                echo $buffer;
                flush();
                $remaining -= strlen($buffer);
            }
            fclose($handle);
        }, $status, $headers);
    }
    private function rangeNotSatisfiable(int $size): StreamedResponse
    {
        return response()->stream(function (): void {
        }, 416, [
            'Content-Range' => 'bytes */' . $size,
            'Accept-Ranges' => 'bytes',
        ]);
    }
    private function guessMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'webm' => 'video/webm',
            'mov', 'qt' => 'video/quicktime',
            default => 'video/mp4',
        };
    }
}