<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\File;

class SyncBunnyVideos extends Command
{
    protected $signature = 'bunny:sync-videos {--dry-run : Print report without updating DB}';
    protected $description = 'Sync all Bunny.net videos to DB lessons based on matching titles';

    public function handle()
    {
        $libraryId = config('bunny.stream.library_id') ?: env('BUNNY_STREAM_LIBRARY_ID');
        $apiKey = config('bunny.stream.api_key') ?: env('BUNNY_STREAM_API_KEY');

        if (!$libraryId || !$apiKey) {
            $this->error('Missing Bunny API credentials in .env');
            return 1;
        }

        $this->info("Fetching videos from Bunny API...");

        // 1. FETCH ALL BUNNY VIDEOS
        $bunnyVideos = [];
        $page = 1;
        $itemsPerPage = 200; // Recommended API pagination
        
        do {
            $response = Http::withHeaders([
                'AccessKey' => $apiKey
            ])->get("https://video.bunnycdn.com/library/{$libraryId}/videos", [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'orderBy' => 'date'
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch videos from Bunny API: " . $response->status());
                return 1;
            }

            $data = $response->json();
            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                $bunnyVideos[] = [
                    'guid' => $item['guid'],
                    'title' => $item['title'],
                    'dateUploaded' => $item['dateUploaded'] ?? '',
                    'status' => $item['status'] ?? 0,
                    'encodeProgress' => $item['encodeProgress'] ?? 0
                ];
            }
            $page++;
        } while (count($items) === $itemsPerPage);

        $this->info("Total Bunny videos fetched: " . count($bunnyVideos));

        // 2. FETCH DB LESSONS
        // We only care about video lessons
        $lessons = Lesson::with(['course', 'section'])->where('lesson_type', 'video')->get();
        $this->info("Total DB video lessons fetched: " . $lessons->count());

        // 3. NORMALIZATION & MATCHING
        $matched = [];
        $ambiguous = [];
        $unmatched = [];
        $orphans = [];
        $alreadyMapped = [];
        $conflicts = [];

        // Pre-normalize DB lessons
        $lessonCache = [];
        foreach ($lessons as $lesson) {
            $courseTitle = $lesson->course ? $lesson->course->title : '';
            $sectionTitle = $lesson->section ? $lesson->section->title : '';
            
            $normLesson = $this->normalizeTitle($lesson->title);
            $normCourse = $this->normalizeTitle($courseTitle);
            
            $lessonCache[] = [
                'lesson' => $lesson,
                'norm_title' => $normLesson,
                'norm_course' => $normCourse,
                'course_title' => $courseTitle,
                'section_title' => $sectionTitle,
                'num' => $this->extractNumber($lesson->title)
            ];
        }

        // Keep track of which lessons have been matched to avoid one lesson mapping to multiple videos
        $lessonMatchedBy = [];

        foreach ($bunnyVideos as $video) {
            $normBunny = $this->normalizeTitle($video['title']);
            $bunnyNum = $this->extractNumber($video['title']);
            
            $candidates = [];

            foreach ($lessonCache as $lc) {
                // Scoring system or exact bounds checking
                $isMatch = false;
                $reason = '';
                $confidence = 0;

                // 1. Exact normalized title match
                if ($normBunny === $lc['norm_title']) {
                    $isMatch = true;
                    $reason = 'Exact match';
                    $confidence = 100;
                } 
                // 2. Strong bounding match with number consistency
                else {
                    $hasCourse = empty($lc['norm_course']) ? false : preg_match("/(^| )" . preg_quote($lc['norm_course'], '/') . "( |$)/", $normBunny);
                    $hasLesson = empty($lc['norm_title']) ? false : preg_match("/(^| )" . preg_quote($lc['norm_title'], '/') . "( |$)/", $normBunny);
                    
                    if ($hasLesson) {
                        // If bunny contains the lesson title
                        // check if numbers match to avoid "Lesson 1" matching "Lesson 10"
                        if ($bunnyNum !== null && $lc['num'] !== null) {
                            if ($bunnyNum === $lc['num']) {
                                $isMatch = true;
                                $reason = 'Token + Number match';
                                $confidence = 90;
                            }
                        } else {
                            $isMatch = true;
                            $reason = 'Token match';
                            $confidence = 80;
                        }
                    }
                }

                if ($isMatch) {
                    $candidates[] = [
                        'lc' => $lc,
                        'reason' => $reason,
                        'confidence' => $confidence
                    ];
                }
            }

            if (count($candidates) === 1) {
                $c = $candidates[0];
                $lesson = $c['lc']['lesson'];
                
                // Check current state
                if ($lesson->video_id === $video['guid']) {
                    $alreadyMapped[] = [
                        'video' => $video,
                        'lesson' => $lesson,
                        'course_title' => $c['lc']['course_title']
                    ];
                } elseif (!empty($lesson->video_id) && $lesson->video_provider === 'bunny') {
                    $conflicts[] = [
                        'video' => $video,
                        'lesson' => $lesson,
                        'course_title' => $c['lc']['course_title'],
                        'current_video_id' => $lesson->video_id
                    ];
                } else {
                    $matched[] = [
                        'video' => $video,
                        'lesson' => $lesson,
                        'course_title' => $c['lc']['course_title'],
                        'section_title' => $c['lc']['section_title'],
                        'reason' => $c['reason'],
                        'confidence' => $c['confidence']
                    ];
                }
                
                $lessonMatchedBy[$lesson->id][] = $video;
            } elseif (count($candidates) > 1) {
                $ambiguous[] = [
                    'type' => 'bunny_matches_multiple_lessons',
                    'video' => $video,
                    'candidates' => $candidates
                ];
            } else {
                $orphans[] = $video;
            }
        }

        // Post-process matches to find lessons that matched multiple different bunny videos
        // (Wait, we already filtered Candidates > 1, but what if multiple bunny videos match the SAME lesson?)
        $finalMatched = [];
        $ambiguousLessons = [];
        foreach ($matched as $m) {
            $lessonId = $m['lesson']->id;
            if (count($lessonMatchedBy[$lessonId]) > 1) {
                $ambiguousLessons[$lessonId] = [
                    'lesson' => $m['lesson'],
                    'videos' => $lessonMatchedBy[$lessonId]
                ];
            } else {
                $finalMatched[] = $m;
            }
        }
        
        foreach ($ambiguousLessons as $al) {
            $ambiguous[] = [
                'type' => 'lesson_matches_multiple_bunny',
                'lesson' => $al['lesson'],
                'videos' => $al['videos']
            ];
        }

        // Unmatched lessons
        $matchedIds = collect($finalMatched)->pluck('lesson.id')->merge(collect($alreadyMapped)->pluck('lesson.id'))->merge(collect($conflicts)->pluck('lesson.id'))->unique()->toArray();
        $ambiguousIds = array_keys($ambiguousLessons);
        
        foreach ($lessonCache as $lc) {
            $id = $lc['lesson']->id;
            if (!in_array($id, $matchedIds) && !in_array($id, $ambiguousIds)) {
                $unmatched[] = $lc;
            }
        }

        // 4. GENERATE REPORT
        $reportPath = storage_path('app/reports/bunny-video-mapping.md');
        if (!File::exists(dirname($reportPath))) {
            File::makeDirectory(dirname($reportPath), 0755, true);
        }
        
        $report = "# Bunny Video Mapping Report\n\n";
        $report .= "## Summary\n\n";
        $report .= "- Bunny videos: " . count($bunnyVideos) . "\n";
        $report .= "- Video lessons (DB): " . $lessons->count() . "\n";
        $report .= "- Matched: " . count($finalMatched) . "\n";
        $report .= "- Already mapped: " . count($alreadyMapped) . "\n";
        $report .= "- Conflicts: " . count($conflicts) . "\n";
        $report .= "- Ambiguous: " . count($ambiguous) . "\n";
        $report .= "- Unmatched Lessons: " . count($unmatched) . "\n";
        $report .= "- Orphan Bunny videos: " . count($orphans) . "\n\n";

        if (count($finalMatched) > 0) {
            $report .= "## Matched\n\n";
            $report .= "| Bunny GUID | Bunny Title | Course | Section | Lesson ID | Lesson | Confidence |\n";
            $report .= "|------------|-------------|--------|---------|-----------|--------|------------|\n";
            foreach ($finalMatched as $m) {
                $report .= "| `{$m['video']['guid']}` | {$m['video']['title']} | {$m['course_title']} | {$m['section_title']} | {$m['lesson']->id} | {$m['lesson']->title} | {$m['confidence']}% ({$m['reason']}) |\n";
            }
            $report .= "\n";
        }
        
        if (count($alreadyMapped) > 0) {
            $report .= "## Already Mapped (Skipped)\n\n";
            $report .= "| Bunny GUID | Bunny Title | Lesson ID | Lesson |\n";
            $report .= "|------------|-------------|-----------|--------|\n";
            foreach ($alreadyMapped as $m) {
                $report .= "| `{$m['video']['guid']}` | {$m['video']['title']} | {$m['lesson']->id} | {$m['lesson']->title} |\n";
            }
            $report .= "\n";
        }

        if (count($conflicts) > 0) {
            $report .= "## Conflicts (Manual resolution needed)\n\n";
            $report .= "| Bunny GUID | Bunny Title | Lesson ID | Current DB video_id |\n";
            $report .= "|------------|-------------|-----------|---------------------|\n";
            foreach ($conflicts as $c) {
                $report .= "| `{$c['video']['guid']}` | {$c['video']['title']} | {$c['lesson']->id} | `{$c['current_video_id']}` |\n";
            }
            $report .= "\n";
        }

        if (count($ambiguous) > 0) {
            $report .= "## Ambiguous\n\n";
            foreach ($ambiguous as $a) {
                if ($a['type'] === 'bunny_matches_multiple_lessons') {
                    $report .= "**Bunny Video:** {$a['video']['title']} (`{$a['video']['guid']}`) matches multiple lessons:\n";
                    foreach ($a['candidates'] as $c) {
                        $report .= "- Lesson {$c['lc']['lesson']->id} | {$c['lc']['course_title']} | {$c['lc']['lesson']->title}\n";
                    }
                } else {
                    $report .= "**Lesson:** {$a['lesson']->id} | {$a['lesson']->title} matches multiple Bunny videos:\n";
                    foreach ($a['videos'] as $v) {
                        $report .= "- Bunny: {$v['title']} (`{$v['guid']}`)\n";
                    }
                }
                $report .= "\n";
            }
        }

        if (count($unmatched) > 0) {
            $report .= "## Unmatched Video Lessons\n\n";
            $report .= "| Lesson ID | Course | Lesson Title |\n";
            $report .= "|-----------|--------|--------------|\n";
            foreach ($unmatched as $u) {
                $report .= "| {$u['lesson']->id} | {$u['course_title']} | {$u['lesson']->title} |\n";
            }
            $report .= "\n";
        }

        if (count($orphans) > 0) {
            $report .= "## Orphan Bunny Videos\n\n";
            $report .= "| Bunny GUID | Bunny Title |\n";
            $report .= "|------------|-------------|\n";
            foreach ($orphans as $o) {
                $report .= "| `{$o['guid']}` | {$o['title']} |\n";
            }
            $report .= "\n";
        }

        File::put($reportPath, $report);
        $this->info("Report generated: {$reportPath}");

        // Summary in console
        $this->info("\n=== SUMMARY ===");
        $this->info("Bunny videos: " . count($bunnyVideos));
        $this->info("Video lessons: " . $lessons->count());
        $this->info("Matched (ready to update): " . count($finalMatched));
        $this->info("Already mapped: " . count($alreadyMapped));
        $this->info("Conflicts: " . count($conflicts));
        $this->info("Ambiguous: " . count($ambiguous));
        $this->info("Unmatched lessons: " . count($unmatched));
        $this->info("Orphan Bunny videos: " . count($orphans));
        $this->info("===============\n");

        if ($this->option('dry-run')) {
            $this->info("Dry run completed. No database changes were made.");
            return 0;
        }

        if (count($finalMatched) === 0) {
            $this->info("No new matches found to update.");
            return 0;
        }

        if (!$this->confirm('Do you want to proceed with updating the database? (Only MATCHED will be updated)')) {
            $this->info('Update cancelled.');
            return 0;
        }

        // 5. UPDATE DATABASE
        DB::beginTransaction();
        try {
            foreach ($finalMatched as $m) {
                $lesson = $m['lesson'];
                $video = $m['video'];

                $lesson->video_provider = 'bunny';
                $lesson->video_id = $video['guid'];
                // Do NOT modify video_url if it exists, user requested to keep it for fallback
                $lesson->save();
            }
            DB::commit();
            $this->info("Database updated successfully.");
            
            // Post-migration validation
            $this->info("\n=== POST-MIGRATION VALIDATION ===");
            $this->runValidationQueries();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to update database: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Convert Vietnamese characters to ASCII, lowercase, remove symbols
     */
    private function normalizeTitle($title)
    {
        if (empty($title)) return '';

        // Remove .mp4 extension
        $title = preg_replace('/\.mp4$/i', '', $title);

        // Convert to ASCII (handles Vietnamese diacritics using Laravel Str)
        $title = Str::ascii($title);

        // Lowercase
        $title = strtolower($title);

        // Replace non-alphanumeric with space
        $title = preg_replace('/[^a-z0-9]+/i', ' ', $title);

        // Trim and collapse spaces
        $title = trim(preg_replace('/\s+/', ' ', $title));

        return $title;
    }

    /**
     * Extract lesson number (e.g., "1" from "Bài 1", "Lesson 01")
     */
    private function extractNumber($title)
    {
        // Matches "bai 1", "bai 01", "lesson 1", "lesson 01", or even leading number "01-abc"
        // But we must normalize first to strip accents if checking "bài"
        $norm = $this->normalizeTitle($title);
        
        // Match numbers following "bai", "lesson", "phan", "chuong"
        if (preg_match('/(?:bai|lesson|phan|chuong|part|sec|section) (\d+)/i', $norm, $matches)) {
            return (int)$matches[1];
        }

        // If it starts with a number (e.g. "01 introduction")
        if (preg_match('/^(\d+) /', $norm, $matches)) {
            return (int)$matches[1];
        }

        // If it ends with a number (e.g. "introduction 1")
        if (preg_match('/ (\d+)$/', $norm, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    private function runValidationQueries()
    {
        $stats = DB::select("
            SELECT
                COUNT(*) AS total_lessons,
                SUM(lesson_type = 'video') AS video_lessons,
                SUM(lesson_type = 'video' AND video_id IS NOT NULL) AS mapped_video_lessons,
                SUM(lesson_type = 'video' AND video_provider = 'bunny') AS bunny_lessons
            FROM lessons
        ")[0];

        $this->info("total_lessons: {$stats->total_lessons}");
        $this->info("video_lessons: {$stats->video_lessons}");
        $this->info("mapped_video_lessons: {$stats->mapped_video_lessons}");
        $this->info("bunny_lessons: {$stats->bunny_lessons}");

        $duplicates = DB::select("
            SELECT video_id, COUNT(*) as count
            FROM lessons
            WHERE video_id IS NOT NULL
            GROUP BY video_id
            HAVING COUNT(*) > 1
        ");

        if (count($duplicates) > 0) {
            $this->error("WARNING: Duplicate video_id mappings found!");
            foreach ($duplicates as $d) {
                $this->error("video_id: {$d->video_id} mapped {$d->count} times");
            }
        } else {
            $this->info("No duplicate video_id assignments found.");
        }
    }
}
