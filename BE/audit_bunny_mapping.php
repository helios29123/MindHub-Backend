<?php

require 'vendor/autoload.php';

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Fetching Bunny Videos...\n";

$libraryId = config('bunny.stream.library_id') ?: env('BUNNY_STREAM_LIBRARY_ID');
$apiKey = config('bunny.stream.api_key') ?: env('BUNNY_STREAM_API_KEY');

$bunnyVideos = [];
$page = 1;
$itemsPerPage = 200;
do {
    $response = Http::withHeaders(['AccessKey' => $apiKey])
        ->get("https://video.bunnycdn.com/library/{$libraryId}/videos", [
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'orderBy' => 'date'
        ]);

    if (!$response->successful()) {
        die("Failed to fetch videos from Bunny API: " . $response->status() . "\n");
    }

    $items = $response->json()['items'] ?? [];
    foreach ($items as $item) {
        $bunnyVideos[$item['guid']] = $item['title'];
    }
    $page++;
} while (count($items) === $itemsPerPage);

echo "Total Bunny videos fetched: " . count($bunnyVideos) . "\n";

echo "Fetching DB Lessons...\n";

// Fetch lessons
$lessons = DB::table('lessons')
    ->leftJoin('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
    ->leftJoin('courses', 'course_sections.course_id', '=', 'courses.id')
    ->select(
        'lessons.id as lesson_id',
        'lessons.title as lesson_title',
        'lessons.lesson_type',
        'lessons.video_provider',
        'lessons.video_id',
        'lessons.video_url',
        'course_sections.id as section_id',
        'course_sections.title as section_title',
        'courses.id as course_id',
        'courses.title as course_title'
    )
    ->get();

$mappedLessons = [];
$unmappedLessons = [];
$nonVideoMapped = [];
$duplicateGuids = [];
$invalidGuids = [];
$obsoleteUrls = [];
$titleInconsistencies = [];
$fkAnomalies = [];
$guidCounts = [];
$anomalies = [];

$stats = [
    'courses' => DB::table('courses')->count(),
    'course_sections' => DB::table('course_sections')->count(),
    'lessons' => $lessons->count(),
    'video_lessons' => 0,
    'bunny_mapped' => 0,
    'bunny_videos' => count($bunnyVideos)
];

foreach ($lessons as $lesson) {
    if ($lesson->lesson_type === 'video') {
        $stats['video_lessons']++;
    }

    if ($lesson->video_provider === 'bunny' || !empty($lesson->video_id)) {
        if ($lesson->lesson_type !== 'video') {
            $nonVideoMapped[] = $lesson;
            $anomalies[] = [
                'lesson' => $lesson,
                'reason' => "Non-video lesson has Bunny mapping"
            ];
            continue;
        }

        if ($lesson->video_provider !== 'bunny') {
            $anomalies[] = [
                'lesson' => $lesson,
                'reason' => "video_provider is '{$lesson->video_provider}', expected 'bunny'"
            ];
        }
        if (empty($lesson->video_id)) {
            $anomalies[] = [
                'lesson' => $lesson,
                'reason' => "video_id is null/empty but provider is bunny"
            ];
        }

        if ($lesson->video_provider === 'bunny' && !empty($lesson->video_id)) {
            $stats['bunny_mapped']++;
            $mappedLessons[] = $lesson;

            if (!isset($guidCounts[$lesson->video_id])) {
                $guidCounts[$lesson->video_id] = 0;
            }
            $guidCounts[$lesson->video_id]++;

            if (!isset($bunnyVideos[$lesson->video_id])) {
                $invalidGuids[] = $lesson;
                $anomalies[] = [
                    'lesson' => $lesson,
                    'reason' => "video_id '{$lesson->video_id}' not found in Bunny API"
                ];
            } else {
                $bunnyTitle = $bunnyVideos[$lesson->video_id];
                // basic normalization check for consistency
                $normalizeForComp = function($t) { return strtolower(preg_replace('/[^a-z0-9]/i', '', $t)); };
                $nb = $normalizeForComp($bunnyTitle);
                $nl = $normalizeForComp($lesson->lesson_title);
                
                // Very loose check to see if there's SOME resemblance
                if (strpos($nb, $nl) === false && strpos($nl, $nb) === false && levenshtein($nb, $nl) > 30) {
                    $titleInconsistencies[] = [
                        'lesson' => $lesson,
                        'bunny_title' => $bunnyTitle
                    ];
                }
            }

            if (empty($lesson->course_id) || empty($lesson->section_id)) {
                $fkAnomalies[] = $lesson;
                $anomalies[] = [
                    'lesson' => $lesson,
                    'reason' => "Missing course_id or section_id (Broken FK)"
                ];
            }

            if (!empty($lesson->video_url)) {
                $obsoleteUrls[] = $lesson;
                $anomalies[] = [
                    'lesson' => $lesson,
                    'reason' => "Obsolete video_url exists: {$lesson->video_url}"
                ];
            }
        }
    } else {
        if ($lesson->lesson_type === 'video') {
            $unmappedLessons[] = $lesson;
        }
    }
}

foreach ($guidCounts as $guid => $count) {
    if ($count > 1) {
        $duplicateGuids[] = $guid;
    }
}

$orphanBunnyVideos = [];
$mappedGuids = array_keys($guidCounts);
foreach ($bunnyVideos as $guid => $title) {
    if (!in_array($guid, $mappedGuids)) {
        $orphanBunnyVideos[] = [
            'guid' => $guid,
            'title' => $title
        ];
    }
}

// Generate Markdown
$out = "# Bunny Final Audit Report\n\n";

$out .= "## SUMMARY\n\n";
$out .= "- Courses: {$stats['courses']}\n";
$out .= "- Sections: {$stats['course_sections']}\n";
$out .= "- Lessons: {$stats['lessons']}\n";
$out .= "- Video lessons: {$stats['video_lessons']}\n";
$out .= "- Bunny mapped lessons: {$stats['bunny_mapped']}\n";
$out .= "- Bunny videos currently available: {$stats['bunny_videos']}\n\n";

$out .= "## MAPPED VIDEO INTEGRITY\n\n";
$out .= "Checks performed: \n";
$out .= "- Every `video_id` is a valid Bunny GUID\n";
$out .= "- No Bunny GUID is assigned to more than one lesson\n";
$out .= "- Every Bunny lesson has `video_provider = 'bunny'`\n";
$out .= "- Every Bunny lesson has a non-null `video_id`\n";
$out .= "- No non-video lesson has Bunny video mapping\n\n";

$out .= "## DUPLICATE GUID CHECK\n\n";
if (count($duplicateGuids) > 0) {
    foreach ($duplicateGuids as $d) {
        $out .= "- **{$d}** is mapped {$guidCounts[$d]} times!\n";
    }
} else {
    $out .= "PASS: No duplicate GUIDs found.\n";
}
$out .= "\n";

$out .= "## INVALID GUID CHECK\n\n";
if (count($invalidGuids) > 0) {
    foreach ($invalidGuids as $i) {
        $out .= "- Lesson ID {$i->lesson_id}: GUID {$i->video_id} not found in Bunny\n";
    }
} else {
    $out .= "PASS: All mapped GUIDs exist in Bunny API.\n";
}
$out .= "\n";

$out .= "## COURSE/SECTION/LESSON INTEGRITY\n\n";
if (count($fkAnomalies) > 0) {
    foreach ($fkAnomalies as $fk) {
        $out .= "- Lesson ID {$fk->lesson_id} has broken FK.\n";
    }
} else {
    $out .= "PASS: All mapped lessons belong to valid courses and sections.\n";
}
$out .= "\n";

$out .= "## OBSOLETE video_url CHECK\n\n";
if (count($obsoleteUrls) > 0) {
    $out .= "Found " . count($obsoleteUrls) . " mapped lessons that still have an old `video_url`.\n";
} else {
    $out .= "PASS: No obsolete `video_url` values found on mapped lessons.\n";
}
$out .= "\n";

$out .= "## BUNNY TITLE CONSISTENCY\n\n";
if (count($titleInconsistencies) > 0) {
    $out .= "Found " . count($titleInconsistencies) . " mapped lessons where Bunny title severely diverges from Lesson title:\n\n";
    $out .= "| Lesson ID | Lesson Title | Bunny Title |\n";
    $out .= "|-----------|--------------|-------------|\n";
    foreach (array_slice($titleInconsistencies, 0, 10) as $t) {
        $out .= "| {$t['lesson']->lesson_id} | {$t['lesson']->lesson_title} | {$t['bunny_title']} |\n";
    }
    if (count($titleInconsistencies) > 10) $out .= "| ... | ... | ... |\n";
} else {
    $out .= "PASS: Titles appear consistent.\n";
}
$out .= "\n";

$out .= "## ANOMALIES LOG\n\n";
if (count($anomalies) > 0) {
    $out .= "| Lesson ID | Course | Section | Lesson Title | Bunny GUID | Reason |\n";
    $out .= "|-----------|--------|---------|--------------|------------|--------|\n";
    foreach ($anomalies as $a) {
        $l = $a['lesson'];
        $c = $l->course_title ?: 'N/A';
        $s = $l->section_title ?: 'N/A';
        $out .= "| {$l->lesson_id} | {$c} | {$s} | {$l->lesson_title} | {$l->video_id} | {$a['reason']} |\n";
    }
} else {
    $out .= "No anomalies detected in constraints.\n";
}
$out .= "\n";

$out .= "## ORPHAN BUNNY VIDEOS\n\n";
$out .= "Total: " . count($orphanBunnyVideos) . "\n\n";

$out .= "## UNMAPPED LESSONS\n\n";
$out .= "Total video lessons without Bunny mapping: " . count($unmappedLessons) . "\n\n";

$out .= "## FINAL VERDICT\n\n";

$errors = count($duplicateGuids) + count($invalidGuids) + count($fkAnomalies) + count($nonVideoMapped);
$warnings = count($obsoleteUrls) + count($titleInconsistencies);
foreach ($anomalies as $a) {
    if (strpos($a['reason'], 'Obsolete') === false) {
        $errors++;
    }
}

if ($errors > 0) {
    $out .= "**FAIL**\n\nCritical integrity errors found.";
} elseif ($warnings > 0) {
    $out .= "**PASS WITH WARNINGS**\n\nMapping is structurally valid, but check warnings (like obsolete URLs).";
} else {
    $out .= "**PASS**\n\nAll checks passed perfectly.";
}

file_put_contents(storage_path('app/reports/bunny-final-audit.md'), $out);
echo "Final audit report generated at storage/app/reports/bunny-final-audit.md\n";
