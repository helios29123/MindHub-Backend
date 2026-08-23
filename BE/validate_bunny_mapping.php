<?php

require 'vendor/autoload.php';

use Illuminate\Support\Str;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$filePath = storage_path('app/reports/bunny-video-mapping.md');
if (!file_exists($filePath)) {
    die("Report not found\n");
}

$lines = file($filePath);
$mode = '';
$matched = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '## Matched') === 0) {
        $mode = 'matched';
        continue;
    } elseif (strpos($line, '##') === 0) {
        $mode = 'other';
    }

    if ($mode === 'matched' && preg_match('/^\|\s*`([^`]+)`\s*\|\s*(.+?)\s*\|\s*(.+?)\s*\|\s*(.+?)\s*\|\s*(\d+)\s*\|\s*(.+?)\s*\|\s*(.+?)\s*\|$/', $line, $matches)) {
        if (strpos($matches[1], 'Bunny GUID') !== false || strpos($matches[1], '---') !== false) continue;
        $matched[] = [
            'guid' => $matches[1],
            'bunny_title' => $matches[2],
            'course' => $matches[3],
            'section' => $matches[4],
            'lesson_id' => $matches[5],
            'lesson_title' => $matches[6],
            'confidence_raw' => $matches[7]
        ];
    }
}

function normalizeTitle($title) {
    if (empty($title)) return '';
    $title = preg_replace('/\.mp4$/i', '', $title);
    $title = Str::ascii($title);
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9]+/i', ' ', $title);
    return trim(preg_replace('/\s+/', ' ', $title));
}

function extractNumber($title) {
    $norm = normalizeTitle($title);
    if (preg_match('/(?:bai|lesson|phan|chuong|part|sec|section) (\d+)/i', $norm, $matches)) {
        return (int)$matches[1];
    }
    if (preg_match('/^(\d+) /', $norm, $matches)) {
        return (int)$matches[1];
    }
    if (preg_match('/ (\d+)$/', $norm, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

$safe = [];
$review = [];
$reject = [];
$duplicates = [];
$guids = [];

foreach ($matched as $m) {
    $bunnyNum = extractNumber($m['bunny_title']);
    $lessonNum = extractNumber($m['lesson_title']);
    
    $normBunny = normalizeTitle($m['bunny_title']);
    $normLesson = normalizeTitle($m['lesson_title']);
    
    $m['bunny_num'] = $bunnyNum;
    $m['lesson_num'] = $lessonNum;
    
    // Check duplicates
    if (!isset($guids[$m['guid']])) {
        $guids[$m['guid']] = [];
    }
    $guids[$m['guid']][] = $m;
}

foreach ($guids as $guid => $matches) {
    if (count($matches) > 1) {
        $duplicates[] = $guid;
        foreach ($matches as $m) {
            $m['reason'] = "Bunny GUID assigned to multiple lessons";
            $reject[] = $m;
        }
        continue; // skip further checks for these
    }

    $m = $matches[0];
    $bunnyNum = $m['bunny_num'];
    $lessonNum = $m['lesson_num'];
    $normBunny = normalizeTitle($m['bunny_title']);
    $normLesson = normalizeTitle($m['lesson_title']);
    
    $isReject = false;
    $isReview = false;
    $reason = '';
    
    // REJECT CONDITIONS
    if ($bunnyNum !== null && $lessonNum !== null && $bunnyNum !== $lessonNum) {
        $isReject = true;
        $reason = "Lesson number conflicts: Bunny($bunnyNum) vs Lesson($lessonNum)";
    }
    
    // Partial resemblance or moderate similarity without exact match
    $hasLessonToken = strpos($normBunny, $normLesson) !== false;
    
    if (!$isReject) {
        if ($bunnyNum !== null && $lessonNum !== null && $bunnyNum === $lessonNum) {
            if ($hasLessonToken || $normBunny === $normLesson) {
                // SAFE
            } else {
                // REVIEW: Number matches but title not fully contained
                $isReview = true;
                $reason = "Lesson number matches but lesson title similarity is moderate/missing";
            }
        } elseif ($hasLessonToken) {
            // SAFE
        } else {
            // REVIEW: No number match, no token match
            $isReview = true;
            $reason = "Bunny title does not strictly contain the normalized Lesson title";
        }
    }

    if ($isReject) {
        $m['reason'] = $reason;
        $reject[] = $m;
    } elseif ($isReview) {
        $m['reason'] = $reason;
        $review[] = $m;
    } else {
        $safe[] = $m;
    }
}

// Orphans and Unmatched
$orphans = [];
$unmatchedCount = 0;
foreach ($lines as $line) {
    if (strpos($line, '- Unmatched Lessons: ') === 0) {
        $unmatchedCount = (int)trim(str_replace('- Unmatched Lessons: ', '', $line));
    }
}

$mode = '';
foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '## Orphan Bunny Videos') === 0) {
        $mode = 'orphans';
        continue;
    } elseif (strpos($line, '##') === 0) {
        $mode = 'other';
    }

    if ($mode === 'orphans' && preg_match('/^\|\s*`([^`]+)`\s*\|\s*(.+?)\s*\|$/', $line, $matches)) {
        if (strpos($matches[1], 'Bunny GUID') !== false || strpos($matches[1], '---') !== false) continue;
        $orphans[] = [
            'guid' => $matches[1],
            'bunny_title' => $matches[2]
        ];
    }
}

$out = "# Bunny Mapping Validation v2\n\n";
$out .= "## Summary\n\n";
$out .= "- SAFE: " . count($safe) . "\n";
$out .= "- REVIEW: " . count($review) . "\n";
$out .= "- REJECT: " . count($reject) . "\n";
$out .= "- Duplicate GUIDs: " . count($duplicates) . "\n";
$out .= "- Orphan Bunny Videos: " . count($orphans) . "\n";
$out .= "- Lessons Without Bunny Video: " . $unmatchedCount . "\n\n";

if (count($safe) > 0) {
    $out .= "## SAFE MATCHES\n\n";
    $out .= "| Bunny GUID | Bunny Title | Lesson ID | Lesson Title |\n";
    $out .= "|------------|-------------|-----------|--------------|\n";
    foreach ($safe as $v) {
        $out .= "| `{$v['guid']}` | {$v['bunny_title']} | {$v['lesson_id']} | {$v['lesson_title']} |\n";
    }
    $out .= "\n";
}

if (count($review) > 0) {
    $out .= "## REVIEW MATCHES\n\n";
    $out .= "| Bunny GUID | Bunny Title | Course | Section | Lesson ID | Lesson Title | Extracted Num | Reason |\n";
    $out .= "|------------|-------------|--------|---------|-----------|--------------|---------------|--------|\n";
    foreach ($review as $r) {
        $extNum = $r['bunny_num'] !== null ? $r['bunny_num'] : 'None';
        $extLesson = $r['lesson_num'] !== null ? $r['lesson_num'] : 'None';
        $out .= "| `{$r['guid']}` | {$r['bunny_title']} | {$r['course']} | {$r['section']} | {$r['lesson_id']} | {$r['lesson_title']} | B:{$extNum}/L:{$extLesson} | {$r['reason']} |\n";
    }
    $out .= "\n";
}

if (count($reject) > 0) {
    $out .= "## REJECTED MATCHES\n\n";
    $out .= "| Bunny GUID | Bunny Title | Course | Section | Lesson ID | Lesson Title | Extracted Num | Reason |\n";
    $out .= "|------------|-------------|--------|---------|-----------|--------------|---------------|--------|\n";
    foreach ($reject as $r) {
        $extNum = $r['bunny_num'] !== null ? $r['bunny_num'] : 'None';
        $extLesson = $r['lesson_num'] !== null ? $r['lesson_num'] : 'None';
        $out .= "| `{$r['guid']}` | {$r['bunny_title']} | {$r['course']} | {$r['section']} | {$r['lesson_id']} | {$r['lesson_title']} | B:{$extNum}/L:{$extLesson} | {$r['reason']} |\n";
    }
    $out .= "\n";
}

$out .= "## Orphan Bunny Videos\n\n";
if (count($orphans) > 0) {
    $out .= "| Bunny GUID | Bunny Title |\n";
    $out .= "|------------|-------------|\n";
    foreach ($orphans as $o) {
        $out .= "| `{$o['guid']}` | {$o['bunny_title']} |\n";
    }
} else {
    $out .= "None\n";
}
$out .= "\n";

file_put_contents(storage_path('app/reports/bunny-video-validation-v2.md'), $out);
echo "Validation report generated at storage/app/reports/bunny-video-validation-v2.md\n";
