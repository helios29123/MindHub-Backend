<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CourseVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/bunny_videos.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("File $jsonPath not found.");
            return;
        }

        $videosData = json_decode(File::get($jsonPath), true);

        $mapping = [
            1 => 'Laravel Rest API',
            2 => 'MySQL Database Design',
            3 => 'React E-Learning',
            4 => 'MVP Web Product',
            5 => 'Postman API Testing',
            6 => 'Web Analytics A/B Testing',
            7 => 'Deploy VPS AAPanel',
            8 => 'Career Webdev',
            9 => 'Web Project Management',
            10 => 'Backend Interview',
        ];

        foreach ($mapping as $courseId => $videoGroup) {
            $course = Course::find($courseId);
            if (!$course) {
                $this->command->warn("Course ID $courseId not found.");
                continue;
            }

            if (!isset($videosData[$videoGroup])) {
                $this->command->warn("Video group '$videoGroup' not found in JSON.");
                continue;
            }

            $videos = $videosData[$videoGroup];

            // Clear existing sections and lessons if any to avoid duplicates
            // We only do this for the specific course if we re-run the seeder
            Lesson::where('course_id', $course->id)->delete();
            CourseSection::where('course_id', $course->id)->delete();

            // Create a section for the course
            $section = CourseSection::create([
                'course_id' => $course->id,
                'title' => 'Danh sách bài học',
                'description' => 'Các video bài giảng của khóa học.',
                'sort_order' => 1,
                'status' => CourseSection::STATUS_PUBLISHED,
            ]);

            // Create lessons
            $sortOrder = 1;
            foreach ($videos as $video) {
                Lesson::create([
                    'course_id' => $course->id,
                    'course_section_id' => $section->id,
                    'title' => $video['title'],
                    'lesson_type' => Lesson::TYPE_VIDEO,
                    'content' => 'Nội dung video bài giảng: ' . $video['title'],
                    'video_url' => null, // As requested by user
                    'video_id' => $video['video_id'],
                    'video_duration_seconds' => 0,
                    'is_preview' => $sortOrder <= 2, // Make first 2 videos previewable
                    'status' => Lesson::STATUS_PUBLISHED,
                    'sort_order' => $sortOrder,
                ]);
                $sortOrder++;
            }
            
            $this->command->info("Seeded course ID $courseId with " . count($videos) . " videos from group '$videoGroup'.");
        }
    }
}
