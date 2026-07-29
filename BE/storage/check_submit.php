<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\User;
use App\Services\Instructor\InstructorCourseService;

$courses = Course::where('status', 'draft')->get();
echo "Found " . $courses->count() . " draft courses.\n";

foreach ($courses as $c) {
    echo "ID: {$c->id} | Title: {$c->title} | ShortDesc: '" . ($c->short_description ?? 'NULL') . "' | CatCount: " . $c->categories->count() . " | SecCount: " . $c->sections->count() . "\n";
    $instructor = User::find($c->instructor_id);
    if ($instructor) {
        $service = app(InstructorCourseService::class);
        try {
            // Test if can be submitted
            $res = $service->submitForReview($instructor, $c->id);
            echo "  -> Submit SUCCESS! Status is now: " . $res->status . "\n";
        } catch (\Throwable $e) {
            echo "  -> Submit FAILED! Code: " . $e->getCode() . " Message: " . $e->getMessage() . "\n";
        }
    }
}
