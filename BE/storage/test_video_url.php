<?php

$url = 'http://127.0.0.1:8000/storage/instructor/uploads/course_intro_video/MGVANv7IEW0etjK8GFmjQIf3GWEJ397lDeESaWfA.mp4';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HEAD Request Code: " . $httpCode . "\n";
echo "Response Headers:\n" . $response . "\n";

// Test Range Request (Range: bytes=0-1023)
$ch2 = curl_init($url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Range: bytes=0-1023']);
$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "RANGE Request Code: " . $httpCode2 . "\n";
echo "Range Response Headers:\n" . substr($response2, 0, strpos($response2, "\r\n\r\n")) . "\n";
