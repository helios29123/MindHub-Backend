# USER NAME COLUMN & AUTH INTEGRATION REPORT

## 1. Database Schema Status
- **Cột users.name**: Đã thêm thành công thông qua migration 2026_07_21_000000_add_name_column_to_users_table.
- **Cột users.remember_token**: Đã tự động bổ sung thông qua migration để giải quyết triệt để lỗi thiếu cột emember_token khi chạy factory.

## 2. Model & Factory Synchronizations
- **Đồng bộ 
ame và ull_name**: Đã cấu hình hook saving trong User model để tự động đồng bộ hai trường này. Đồng thời, cấu hình mutator setPasswordAttribute để tương thích ngược với các hàm gọi password thay vì password_hash.
- **UserFactory**: Đã cập nhật định nghĩa để điền cả 
ame và ull_name đồng nhất.
- **InstructorProfileFactory**: Đã bổ sung factory bị thiếu nhằm tránh lỗi Class not found trong quá trình chạy test.

## 3. Test Results
- **InstructorProfileTest**: **PASS** (8 tests, 8 passed, 25 assertions)
- **Auth Tests (filter=Auth)**: **PASS** (44 tests, 44 passed, 80 assertions)
- **Login/Register/Password/Google Tests**: Đã chạy qua và đã pass toàn bộ các tests nằm trong tập hợp Auth (các filter độc lập này không tìm thấy tests riêng biệt nào nằm ngoài tập hợp Auth).

## 4. Lỗi còn lại nếu có
- **Không có lỗi nào còn lại**. Tất cả các test nhắm đến đều pass hoàn toàn.

## 5. Git Status Short Output
``n M app/Http/Controllers/AuthController.php
 M app/Http/Controllers/InstructorCourseController.php
 M app/Http/Controllers/InstructorWithdrawalController.php
 M app/Http/Controllers/InteractionController.php
 M app/Http/Controllers/QuizController.php
 M app/Http/Controllers/ReportController.php
 M app/Http/Requests/Interaction/InstructorQuestionQueryRequest.php
 M app/Http/Resources/Instructor/InstructorPayoutAccountResource.php
 M app/Http/Resources/Interaction/InstructorQuestionResource.php
 M app/Http/Resources/Marketing/InstructorCouponResource.php
 M app/Models/InstructorProfile.php
 M app/Models/Order.php
 M app/Models/PayoutAccount.php
 M app/Models/Revenue.php
 M app/Models/User.php
 M app/Repositories/Instructor/InstructorRevenueRepository.php
 M app/Repositories/Instructor/InstructorWithdrawalRepository.php
 M app/Repositories/Interaction/InstructorQuestionRepository.php
 M app/Repositories/Marketing/MarketingCouponRepository.php
 M app/Services/Instructor/InstructorWithdrawalService.php
 M app/Services/Interaction/InstructorQuestionService.php
 M app/Services/Marketing/CouponService.php
 M app/Services/Payment/PaymentService.php
 M app/Services/Quiz/QuizService.php
 M database/factories/UserFactory.php
 M phpunit.xml
 M routes/api/auth.php
 M routes/api/instructor.php
 M routes/api/marketing.php
 M tests/Feature/AdminHomepageBannerTest.php
 M tests/Feature/AdminModerationTest.php
 M tests/Feature/AdminUserManagementTest.php
 M tests/Feature/BannerManagementTest.php
 M tests/Feature/CourseDashboardTest.php
 M tests/Feature/CourseLearnerTest.php
 M tests/Feature/DashboardReportTest.php
 M tests/Feature/InactiveLearnersReportTest.php
 M tests/Feature/Instructor/InstructorProfileTest.php
 M tests/Feature/InstructorQuestionApiTest.php
 M tests/Feature/InteractionCommentTest.php
 M tests/Feature/LearningCourseOutlineTest.php
 M tests/Feature/MarketingAnnouncementTest.php
 M tests/Feature/QuizAttemptResultTest.php
 M tests/Feature/QuizAttemptTest.php
 M tests/Feature/RevenueReportTest.php
 M tests/Feature/TopCoursesReportTest.php
 M tests/Feature/TopInstructorsReportTest.php
?? ../.vscode/
?? app/Http/Controllers/InstructorCouponController.php
?? app/Http/Controllers/InstructorNotificationController.php
?? app/Http/Controllers/InstructorPayoutAccountController.php
?? app/Http/Requests/Admin/AdminDashboardQueryRequest.php
?? app/Http/Requests/Admin/AdminNotificationQueryRequest.php
?? app/Http/Requests/Admin/AdminPayoutBatchRequest.php
?? app/Http/Requests/Admin/AdminPayoutQueryRequest.php
?? app/Http/Requests/Admin/AdminRevenueQueryRequest.php
?? app/Http/Requests/Admin/CommissionRuleUpdateRequest.php
?? app/Http/Requests/Admin/MarkOrderPaidRequest.php
?? app/Http/Requests/Admin/MarkPayoutItemPaidRequest.php
?? app/Http/Requests/Admin/PayoutAccountActionRequest.php
?? app/Http/Requests/Admin/ProcessCourseRequest.php
?? app/Http/Resources/Admin/AdminAuditLogResource.php
?? app/Http/Resources/Admin/AdminDashboardResource.php
?? app/Http/Resources/Admin/AdminNotificationResource.php
?? app/Http/Resources/Admin/AdminRevenueResource.php
?? app/Http/Resources/Admin/CommissionRuleResource.php
?? app/Http/Resources/Admin/PayoutAccountResource.php
?? app/Http/Resources/Admin/PayoutBatchResource.php
?? app/Http/Resources/Admin/PayoutItemResource.php
?? app/Http/Resources/Instructor/InstructorNotificationResource.php
?? app/Models/AdminAuditLog.php
?? app/Models/AdminNotification.php
?? app/Models/CommissionRole.php
?? app/Models/CommissionRule.php
?? app/Models/PayoutBatch.php
?? app/Models/PayoutItem.php
?? app/Models/PayoutItemRevenue.php
?? app/Models/ReferralLink.php
?? app/Repositories/Admin/
?? app/Services/Admin/AdminCommissionService.php
?? app/Services/Admin/AdminCourseService.php
?? app/Services/Admin/AdminDashboardService.php
?? app/Services/Admin/AdminNotificationService.php
?? app/Services/Admin/AdminPayoutAccountService.php
?? app/Services/Admin/AdminPayoutService.php
?? app/Services/Admin/AdminRevenueService.php
?? app/Services/Admin/AdminUserService.php
?? app/Services/Payment/RevenueShareService.php
?? columns.txt
?? database/factories/InstructorProfileFactory.php
?? database/migrations/2026_06_08_000000_import_base_schema.php
?? database/migrations/2026_07_15_000000_add_admin_columns.php
?? database/migrations/2026_07_15_000001_create_notifications_table.php
?? database/migrations/2026_07_20_000000_add_instructor_api_columns.php
?? database/migrations/2026_07_20_000000_create_commission_rules_table.php
?? database/migrations/2026_07_20_000001_add_revenue_share_source_columns.php
?? database/migrations/2026_07_21_000000_add_name_column_to_users_table.php
?? database/seeders/CommissionRuleSeeder.php
?? database/sql/elearning_erd_full_with_notebooklm_video_seed.sql
?? route_list.txt
?? route_list_utf8.txt
?? storage/admin-api-full-test-20260712-213336/
?? storage/admin-api-test-20260712-210809/
?? storage/admin-api-test-20260712-211503/
?? storage/api-audit-20260719-131158.zip
?? storage/api-audit-20260719-131158/
?? storage/api-test-result-20260721-075548.txt
?? storage/auth-api-test-20260721-194241/
?? storage/auth-smoke-test.php
?? storage/convert-and-import.php
?? storage/copy-migrations-table.php
?? storage/count_banners.php
?? storage/debug-db.php
?? storage/debug_import.php
?? storage/final-api-retest-after-authsession-fix-20260721-090846/
?? storage/find-create-revenues.php
?? storage/find-datn.php
?? storage/find-dots.php
?? storage/find-sessions.php
?? storage/find_video_progress.php
?? storage/fix-auth-helper-report/
?? storage/fix-comments-authsession-final-backup/
?? storage/fix-comments-authsession-final.php
?? storage/fix-final-revenue-share-source-backup/
?? storage/fix-final-revenue-share-source-report/
?? storage/fix-instructor-api-p0-p1-backup/
?? storage/fix-revenue-share-source-errors-backup/
?? storage/fix-test-actingas-auth.php
?? storage/fix-test-auth-actingas-backup/
?? storage/fix-test-db-auth-backup/
?? storage/fix-test-db-auth-report/
?? storage/fix-test-user-columns-backup/
?? storage/fix-test-users-no-name.php
?? storage/fix_admin_model_relations.php
?? storage/fix_admin_revenue_groupby.php
?? storage/fix_admin_revenue_service_final.php
?? storage/fix_order_coupon_relation.php
?? storage/fix_order_revenue_relation.php
?? storage/force-mindhub2-retest-20260721-093152/
?? storage/force-phatnt-full-api-test-20260721-094734/
?? storage/force-phatnt-full-api-test-20260721-100119/
?? storage/full-auth-api-test-20260721-195340/
?? storage/full-auth-smoke-test.php
?? storage/full-instructor-api-test-20260721-103456/
?? storage/get-lesson-5.php
?? storage/get-quiz-options-seed.php
?? storage/get_admin_api_test_ids.php
?? storage/import-by-parts.php
?? storage/import-clean-db.php
?? storage/import-clean-no-tx.php
?? storage/import-no-truncate.php
?? storage/import-phatnt-db.php
?? storage/import-test-db.php
?? storage/import_sql.php
?? storage/instructor-api-final-retest-20260721-085401/
?? storage/instructor-api-full-retest-20260721-081126.txt
?? storage/instructor-api-full-retest-20260721-081622.txt
?? storage/instructor-api-retest-report/
?? storage/instructor-api-retest-utf8-20260721-083541/
?? storage/instructor-api-retest-utf8-20260721-083927/
?? storage/instructor-api-test-report/
?? storage/instructor-full-api-revenue-share-backup/
?? storage/instructor-full-api-test-20260721-080532.txt
?? storage/rebuild-mindhub1-api-test-20260721-091147/
?? storage/replace-refresh-db.php
?? storage/reset-auth-test-users.php
?? storage/reset-full-auth-test-users.php
?? storage/reset-test-users.php
?? storage/reset_admin_for_api_test.php
?? storage/revenue-share-final-retest-report/
?? storage/revenue-share-source-backup/
?? storage/revenue-share-source-retest-report/
?? storage/run-migrations-cleanly.php
?? storage/schema-safe-api-retest-20260721-113418/
?? storage/search_users.php
?? storage/seed-test-users-dynamic.php
?? storage/task-backup-fix-admin-auth-20260712-211818/
?? storage/task-backup-fix-admin-last-3-20260713-202139/
?? storage/task-backup-fix-admin-model-relations-20260712-142154/
?? storage/task-backup-fix-admin-remaining-20260712-213704/
?? storage/task-backup-fix-admin-remaining-20260713-201319/
?? storage/task-backup-fix-admin-revenue-groupby-20260713-135332/
?? storage/task-backup-fix-admin-revenue-service-array-20260713-212208/
?? storage/task-backup-fix-admin-revenue-service-final-20260713-142820/
?? storage/task-backup-fix-order-coupon-20260712-142536/
?? storage/task-backup-fix-order-revenue-20260712-142845/
?? storage/test_parse.php
?? test_block.php
?? tests/Feature/RevenueShareTest.php
?? ../MINDHUB_SQL_SCHEMA_FOR_ANTI.md
``n