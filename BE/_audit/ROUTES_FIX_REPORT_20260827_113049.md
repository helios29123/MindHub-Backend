# ROUTES FIX REPORT

**Generated:** 2026-08-27T11:30:50
**Result:** PASS
**Backup:** `D:\laragon\www\datn\MindHub-Backend\BE\_audit\backup_routes_fix_20260827_113049`

## 1. Changes
- Đã xóa Category restore route (1 match).
- Đã xóa Course view route (1 match).

## 2. Errors
- Không có.

## 3. Source verification
- admin.php còn restore route: PASS
- course.php còn view route: PASS

## 4. Route list verification
- Category restore route còn tồn tại: NO
- Course view route còn tồn tại: NO
- php artisan route:list exit code: 0

## 5. PHP lint
### `routes/api/admin.php` — PASS
```text
No syntax errors detected in D:\laragon\www\datn\MindHub-Backend\BE\routes\api\admin.php
```
### `routes/api/course.php` — PASS
```text
No syntax errors detected in D:\laragon\www\datn\MindHub-Backend\BE\routes\api\course.php
```

## 6. Matching routes after fix
```text
No stale routes found.
```

## 7. git status
```text
 M app/Http/Controllers/AdminCategoryController.php
 M app/Http/Controllers/CoursePublicController.php
 M app/Http/Controllers/InteractionController.php
 M app/Http/Controllers/ReportController.php
 M app/Repositories/Admin/AdminCategoryRepository.php
 M app/Repositories/Admin/AdminCourseRepository.php
 M app/Repositories/Catalog/BannerRepository.php
 M app/Repositories/Catalog/CatalogCourseRepository.php
 M app/Repositories/Catalog/CategoryRepository.php
 M app/Repositories/Catalog/FeaturedInstructorRepository.php
 M app/Repositories/Instructor/InstructorCourseRepository.php
 M app/Repositories/Instructor/InstructorLearnerRepository.php
 M app/Repositories/Instructor/InstructorLessonRepository.php
 M app/Repositories/Instructor/InstructorRevenueRepository.php
 M app/Repositories/Interaction/InstructorQuestionRepository.php
 M app/Repositories/Moderation/CourseModerationRepository.php
 M app/Repositories/Report/InstructorDashboardAlertRepository.php
 M app/Repositories/Report/InstructorDashboardRepository.php
 M app/Repositories/Report/InstructorEnrollmentChartRepository.php
 M app/Repositories/Wishlist/WishlistRepository.php
 M app/Services/Admin/AdminCategoryService.php
 M app/Services/Admin/AdminCourseService.php
 M app/Services/Admin/AdminPayoutAccountService.php
 M app/Services/Admin/AdminService.php
 D app/Services/AdminService.php
 M app/Services/Catalog/CatalogService.php
 M app/Services/Course/CourseAvailabilityService.php
 M app/Services/Course/CoursePublicService.php
 D app/Services/Course/CourseViewService.php
 M app/Services/Course/RelatedCourseService.php
 D app/Services/CoursePublicService.php
 M app/Services/Faq/FaqAdminService.php
 M app/Services/Instructor/CourseChecklistService.php
 M app/Services/Instructor/CourseCreditService.php
 M app/Services/Instructor/InstructorCourseService.php
 M app/Services/Instructor/InstructorCreditOrderService.php
 M app/Services/Instructor/InstructorUpgradeService.php
 M app/Services/Interaction/InstructorQuestionService.php
 D app/Services/InteractionService.php
 D app/Services/MarketingService.php
 M app/Services/Moderation/ModerationService.php
 D app/Services/ModerationService.php
 D app/Services/QuizService.php
 M app/Services/Report/ReportService.php
 M routes/api/admin.php
 D routes/api/admin.php.backup-20260731-232635
 M routes/api/course.php
?? .codegraph/
?? _audit/
?? _scripts/
```

## 8. git diff (2 route files only)
```diff
diff --git a/BE/routes/api/admin.php b/BE/routes/api/admin.php
index 92b8eaf..b8cf39a 100644
--- a/BE/routes/api/admin.php
+++ b/BE/routes/api/admin.php
@@ -119,8 +119,6 @@
         // Static route must stay before /categories/{id}.
         Route::put('/categories/reorder', [AdminCategoryController::class, 'reorder']);
 
-        Route::post('/categories/{id}/restore', [AdminCategoryController::class, 'restore'])
-            ->whereNumber('id');
 
         Route::get('/categories/{id}', [AdminCategoryController::class, 'show'])
             ->whereNumber('id');
diff --git a/BE/routes/api/course.php b/BE/routes/api/course.php
index 3081364..9a27513 100644
--- a/BE/routes/api/course.php
+++ b/BE/routes/api/course.php
@@ -14,7 +14,6 @@
 Route::get('/instructors/{id}', [CoursePublicController::class, 'showInstructor'])->where('id', '[0-9]+');
 Route::get('/courses/{id}/faqs', [CoursePublicController::class, 'faqs'])->where('id', '[0-9]+');
 Route::get('/courses/{courseId}/related', [CoursePublicController::class, 'relatedCourses'])->where('courseId', '[0-9]+');
-Route::post('/courses/{courseId}/view', [CoursePublicController::class, 'recordView'])->where('courseId', '[0-9]+');
 
 
 
```