<?php
$files = [
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/TopInstructorsReportTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/TopCoursesReportTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/RevenueReportTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/QuizAttemptResultTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/Instructor/InstructorProfileTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/InactiveLearnersReportTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/DashboardReportTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/CourseLearnerTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/CourseDashboardTest.php',
    'f:/Phatnt/laragon/www/MindHub-Backend/BE/tests/Feature/AdminUserManagementTest.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File does not exist: $file\n";
        continue;
    }
    $content = file_get_contents($file);
    
    // Replace import
    $newContent = str_replace(
        'Illuminate\Foundation\Testing\RefreshDatabase',
        'Illuminate\Foundation\Testing\DatabaseTransactions',
        $content
    );
    
    // Replace use statement inside class
    $newContent = str_replace(
        'use RefreshDatabase;',
        'use DatabaseTransactions;',
        $newContent
    );
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated file: " . basename($file) . "\n";
    } else {
        echo "No changes for: " . basename($file) . "\n";
    }
}
