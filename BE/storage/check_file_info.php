<?php

$filePath = __DIR__ . '/../storage/app/public/instructor/uploads/course_intro_video/MGVANv7IEW0etjK8GFmjQIf3GWEJ397lDeESaWfA.mp4';

if (!file_exists($filePath)) {
    echo "File NOT found on path: " . $filePath . "\n";
    exit(1);
}

echo "File exists! Size: " . filesize($filePath) . " bytes\n";
$finfo = finfo_open(FILEINFO_MIME_TYPE);
echo "MIME type: " . finfo_file($finfo, $filePath) . "\n";
finfo_close($finfo);

// Check first 32 bytes for MP4 ftyp box header
$fp = fopen($filePath, 'rb');
$header = fread($fp, 32);
fclose($fp);

echo "Header hex: " . bin2hex(substr($header, 0, 16)) . "\n";
if (str_contains($header, 'ftyp') || str_contains($header, 'mp4')) {
    echo "Container: MP4 / ISOM valid container format\n";
}
