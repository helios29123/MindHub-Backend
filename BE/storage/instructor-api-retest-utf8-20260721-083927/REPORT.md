# INSTRUCTOR API RETEST REPORT

## Git status
 M app/Http/Controllers/InstructorCourseController.php
 M app/Http/Controllers/InstructorWithdrawalController.php
 M app/Http/Controllers/InteractionController.php
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
 M routes/api/instructor.php
 M routes/api/marketing.php
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
?? database/migrations/2026_06_08_000000_import_base_schema.php
?? database/migrations/2026_07_15_000000_add_admin_columns.php
?? database/migrations/2026_07_15_000001_create_notifications_table.php
?? database/migrations/2026_07_20_000000_add_instructor_api_columns.php
?? database/migrations/2026_07_20_000000_create_commission_rules_table.php
?? database/migrations/2026_07_20_000001_add_revenue_share_source_columns.php
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
?? storage/convert-and-import.php
?? storage/copy-migrations-table.php
?? storage/debug-db.php
?? storage/find-create-revenues.php
?? storage/find-datn.php
?? storage/find-dots.php
?? storage/find-sessions.php
?? storage/fix-final-revenue-share-source-backup/
?? storage/fix-final-revenue-share-source-report/
?? storage/fix-instructor-api-p0-p1-backup/
?? storage/fix-revenue-share-source-errors-backup/
?? storage/fix_admin_model_relations.php
?? storage/fix_admin_revenue_groupby.php
?? storage/fix_admin_revenue_service_final.php
?? storage/fix_order_coupon_relation.php
?? storage/fix_order_revenue_relation.php
?? storage/get_admin_api_test_ids.php
?? storage/import-by-parts.php
?? storage/import-clean-db.php
?? storage/import-clean-no-tx.php
?? storage/import-no-truncate.php
?? storage/import-test-db.php
?? storage/import_sql.php
?? storage/instructor-api-full-retest-20260721-081126.txt
?? storage/instructor-api-full-retest-20260721-081622.txt
?? storage/instructor-api-retest-report/
?? storage/instructor-api-retest-utf8-20260721-083541/
?? storage/instructor-api-retest-utf8-20260721-083927/
?? storage/instructor-api-test-report/
?? storage/instructor-full-api-revenue-share-backup/
?? storage/instructor-full-api-test-20260721-080532.txt
?? storage/reset_admin_for_api_test.php
?? storage/revenue-share-final-retest-report/
?? storage/revenue-share-source-backup/
?? storage/revenue-share-source-retest-report/
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
?? test_block.php
?? tests/Feature/RevenueShareTest.php
?? ../MINDHUB_SQL_SCHEMA_FOR_ANTI.md

## Clear cache

   INFO  Clearing cached bootstrap files.  

  config ............................................................................................................................... 2.97ms DONE
  cache ................................................................................................................................ 7.59ms DONE
  compiled ............................................................................................................................. 3.44ms DONE
  events ............................................................................................................................... 1.70ms DONE
  routes ............................................................................................................................... 1.45ms DONE
  views ............................................................................................................................... 10.99ms DONE


   INFO  Configuration cache cleared successfully.  


   INFO  Application cache cleared successfully.  


   INFO  Route cache cleared successfully.  


## DB đang dùng
mindhub1

## Table check
users=OK
courses=OK
orders=OK
revenues=OK
commission_rules=OK
payout_accounts=OK
withdraw_requests=OK
comments=OK
coupons=OK
notifications=OK

## MarketingAnnouncement

  [39;41;1m FAIL [39;49;22m[39m Tests\Feature\MarketingAnnouncementTest[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can request course announcement for owned course and receive 501 mock response[39m[90m                                                           [39m [90m0.38s[39m  
  [32;1m✓[39;22m[90m [39m[90munauthenticated users cannot access course announcements[39m[90m                                                                                            [39m [90m0.06s[39m  
  [31;1m⨯[39;22m[90m [39m[90munauthorized roles (learner) cannot access course announcements[39m[90m                                                                                     [39m [90m0.09s[39m  
  [31;1m⨯[39;22m[90m [39m[90minstructor cannot request announcement for course they do not own[39m[90m                                                                                   [39m [90m0.09s[39m  
  [31;1m⨯[39;22m[90m [39m[90mvalidation fails on invalid course announcements inputs[39m[90m                                                                                             [39m [90m0.10s[39m  
  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\MarketingAnnouncementTest[22m [90m>[39m instructor can request course announcement for owned course and receive 501 mock response                  
[39;1m  Expected response status code [501] but received 401.
Failed asserting that 401 is identical to 501.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\MarketingAnnouncementTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\MarketingAnnouncementTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\MarketingAnnouncementTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(8): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(21): getAuthHeadersForMarketingTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\MarketingAnnouncementTest->{closure}()
#46 [internal function]: P\Tests\Feature\MarketingAnnouncementTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\MarketingAnnouncementTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\MarketingAnnouncementTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(17): P\Tests\Feature\MarketingAnnouncementTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\MarketingAnnouncementTest->__pest_evaluable_instructor_can_request_course_announcement_for_owned_course_and_receive_501_mock_response()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\MarketingAnnouncementTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\MarketingAnnouncementTest.php[39m:[32m30[39m
     26▕         'title' => 'Thông báo mới về khóa học Laravel',
     27▕         'content' => 'Chào mọi người, bài tập mới đã được cập nhật.',
     28▕     ], $headers);
     29▕ 
  ➜  30▕     $response->assertStatus(501)
     31▕         ->assertJson([
     32▕             'success' => true,
     33▕             'message' => 'Thao tác thành công',
     34▕             'data' => json_encode(['banner_id' => 1, 'status' => 'active']),

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\MarketingAnnouncementTest[22m [90m>[39m unauthorized roles (learner) cannot access course announcements                                            
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\MarketingAnnouncementTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\MarketingAnnouncementTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\MarketingAnnouncementTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(8): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(53): getAuthHeadersForMarketingTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\MarketingAnnouncementTest->{closure}()
#46 [internal function]: P\Tests\Feature\MarketingAnnouncementTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\MarketingAnnouncementTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\MarketingAnnouncementTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(35): P\Tests\Feature\MarketingAnnouncementTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\MarketingAnnouncementTest->__pest_evaluable_unauthorized_roles__learner__cannot_access_course_announcements()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\MarketingAnnouncementTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\MarketingAnnouncementTest.php[39m:[32m61[39m
     57▕         'title' => 'Thông báo test',
     58▕         'content' => 'Nội dung test',
     59▕     ], $headers);
     60▕ 
  ➜  61▕     $response->assertStatus(403)
     62▕         ->assertJson([
     63▕             'success' => false,
     64▕             'message' => 'Bạn không có quyền thực hiện thao tác này.',
     65▕         ]);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\MarketingAnnouncementTest[22m [90m>[39m instructor cannot request announcement for course they do not own                                          
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\MarketingAnnouncementTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\MarketingAnnouncementTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\MarketingAnnouncementTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(8): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(70): getAuthHeadersForMarketingTest('instructor2@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\MarketingAnnouncementTest->{closure}()
#46 [internal function]: P\Tests\Feature\MarketingAnnouncementTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\MarketingAnnouncementTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\MarketingAnnouncementTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(44): P\Tests\Feature\MarketingAnnouncementTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\MarketingAnnouncementTest->__pest_evaluable_instructor_cannot_request_announcement_for_course_they_do_not_own()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\MarketingAnnouncementTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\MarketingAnnouncementTest.php[39m:[32m78[39m
     74▕         'title' => 'Thông báo khác',
     75▕         'content' => 'Tôi muốn thông báo',
     76▕     ], $headers);
     77▕ 
  ➜  78▕     $response->assertStatus(403)
     79▕         ->assertJson([
     80▕             'success' => false,
     81▕             'message' => 'Bạn không có quyền thực hiện thao tác này.',
     82▕         ]);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\MarketingAnnouncementTest[22m [90m>[39m validation fails on invalid course announcements inputs                                                    
[39;1m  Expected response status code [422] but received 401.
Failed asserting that 401 is identical to 422.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\MarketingAnnouncementTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\MarketingAnnouncementTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\MarketingAnnouncementTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(8): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\MarketingAnnouncementTest.php(86): getAuthHeadersForMarketingTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\MarketingAnnouncementTest->{closure}()
#46 [internal function]: P\Tests\Feature\MarketingAnnouncementTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\MarketingAnnouncementTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\MarketingAnnouncementTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(53): P\Tests\Feature\MarketingAnnouncementTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\MarketingAnnouncementTest->__pest_evaluable_validation_fails_on_invalid_course_announcements_inputs()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\MarketingAnnouncementTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\MarketingAnnouncementTest.php[39m:[32m93[39m
     89▕     $responseMissingCourse = $this->postJson('/api/instructor/course-announcements', [
     90▕         'title' => 'Test',
     91▕         'content' => 'Test',
     92▕     ], $headers);
  ➜  93▕     $responseMissingCourse->assertStatus(422)
     94▕         ->assertJson([
     95▕             'success' => false,
     96▕             'message' => 'Dữ liệu không hợp lệ.',
     97▕         ]);


  [90mTests:[39m    [31;1m4 failed[39;22m[90m,[39m[39m [39m[32;1m1 passed[39;22m[90m (6 assertions)[39m
  [90mDuration:[39m [39m1.17s[39m


## InteractionComment

  [39;41;1m FAIL [39;49;22m[39m Tests\Feature\InteractionCommentTest[39m
  [31;1m⨯[39;22m[90m [39m[90mlearner can view lesson comments list[39m[90m                                                                                                               [39m [90m0.58s[39m  
  [31;1m⨯[39;22m[90m [39m[90mlearner can post a new comment on lesson[39m[90m                                                                                                            [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mlearner can reply to another comment[39m[90m                                                                                                                [39m [90m0.15s[39m  
  [31;1m⨯[39;22m[90m [39m[90munauthenticated users cannot view or post comments[39m[90m                                                                                                  [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mnon-learner role is blocked[39m[90m                                                                                                                         [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mnon-existent lesson returns 404[39m[90m                                                                                                                     [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mlesson not published returns 403[39m[90m                                                                                                                    [39m [90m0.09s[39m  
  [31;1m⨯[39;22m[90m [39m[90mlearner with no enrollment is blocked[39m[90m                                                                                                               [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90minvalid query parameters return 422[39m[90m                                                                                                                 [39m [90m0.11s[39m  
  [31;1m⨯[39;22m[90m [39m[90mempty or too long content validation fails[39m[90m                                                                                                          [39m [90m0.09s[39m  
  [31;1m⨯[39;22m[90m [39m[90minstructor can reply to comment on their own published course lesson[39m[90m                                                                                [39m [90m0.11s[39m  
  [31;1m⨯[39;22m[90m [39m[90munauthenticated users cannot reply to comments[39m[90m                                                                                                      [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mlearners cannot reply using the instructor endpoint[39m[90m                                                                                                 [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mreplying to non-existent comment returns 404[39m[90m                                                                                                        [39m [90m0.10s[39m  
  [31;1m⨯[39;22m[90m [39m[90mreplying to hidden or deleted comment returns 404[39m[90m                                                                                                   [39m [90m0.07s[39m  
  [31;1m⨯[39;22m[90m [39m[90minstructor cannot reply to Q&A of a course they do not own[39m[90m                                                                                          [39m [90m0.09s[39m  
  [31;1m⨯[39;22m[90m [39m[90minstructor cannot reply if lesson or course is not published[39m[90m                                                                                        [39m [90m0.08s[39m  
  [31;1m⨯[39;22m[90m [39m[90mreply validation fails on invalid body inputs[39m[90m                                                                                                       [39m [90m0.12s[39m  
  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m learner can view lesson comments list                                                                         
[39;1m  Expected response status code [200] but received 401.
Failed asserting that 401 is identical to 200.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(28): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(17): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_learner_can_view_lesson_comments_list()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m32[39m
     28▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');
     29▕ 
     30▕     $response = $this->getJson('/api/lessons/2/comments', $headers);
     31▕ 
  ➜  32▕     $response->assertStatus(200)
     33▕         ->assertJson([
     34▕             'success' => true,
     35▕             'message' => 'Lấy danh sách bình luận thành công',
     36▕         ])

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m learner can post a new comment on lesson                                                                      
[39;1m  Expected response status code [201] but received 401.
Failed asserting that 401 is identical to 201.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(65): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(26): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_learner_can_post_a_new_comment_on_lesson()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m71[39m
     67▕     $response = $this->postJson('/api/lessons/2/comments', [
     68▕         'content' => 'Đây là một bình luận thử nghiệm tuyệt vời.',
     69▕     ], $headers);
     70▕ 
  ➜  71▕     $response->assertStatus(201)
     72▕         ->assertJson([
     73▕             'success' => true,
     74▕             'message' => 'Thao tác thành công',
     75▕         ])

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m learner can reply to another comment                                                                          
[39;1m  Expected response status code [201] but received 401.
Failed asserting that 401 is identical to 201.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(96): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(35): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_learner_can_reply_to_another_comment()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m103[39m
     99▕         'content' => 'Bình luận trả lời thử nghiệm.',
    100▕         'parent_id' => 1,
    101▕     ], $headers);
    102▕ 
  ➜ 103▕     $response->assertStatus(201);
    104▕     
    105▕     $commentId = $response->json('data.comment_id');
    106▕     $this->assertDatabaseHas('comments', [
    107▕         'id' => $commentId,

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m unauthenticated users cannot view or post comments                                          [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.comments' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: delete from `comments` where `id` > 5)[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39m:[32m605[39m
    601▕ 
    602▕             // For update or delete statements, we want to get the number of rows affected
    603▕             // by the statement and return that back to the developer. We'll first need
    604▕             // to execute the statement and then we'll use PDO to fetch the affected.
  ➜ 605▕             $statement = $this->getPdo()->prepare($query);
    606▕ 
    607▕             $this->bindValues($statement, $this->prepareBindings($bindings));
    608▕ 
    609▕             $statement->execute();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m605[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m non-learner role is blocked                                                                                   
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"admin...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(124): getAuthHeadersForCommentTest('admin@mindhub.t...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(53): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_non_learner_role_is_blocked()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m130[39m
    126▕     $response = $this->postJson('/api/lessons/2/comments', [
    127▕         'content' => 'Admin không được bình luận.',
    128▕     ], $headers);
    129▕ 
  ➜ 130▕     $response->assertStatus(403);
    131▕ });
    132▕ 
    133▕ test('non-existent lesson returns 404', function () {
    134▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m non-existent lesson returns 404                                                                               
[39;1m  Expected response status code [404] but received 401.
Failed asserting that 401 is identical to 404.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(134): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(62): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_non_existent_lesson_returns_404()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m137[39m
    133▕ test('non-existent lesson returns 404', function () {
    134▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');
    135▕ 
    136▕     $response = $this->getJson('/api/lessons/999/comments', $headers);
  ➜ 137▕     $response->assertStatus(404);
    138▕ 
    139▕     $responsePost = $this->postJson('/api/lessons/999/comments', [
    140▕         'content' => 'Thử nghiệm.',
    141▕     ], $headers);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m lesson not published returns 403                                                                              
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(146): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(71): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_lesson_not_published_returns_403()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m150[39m
    146▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');
    147▕ 
    148▕     // Lesson 5 is hidden
    149▕     $response = $this->getJson('/api/lessons/5/comments', $headers);
  ➜ 150▕     $response->assertStatus(403);
    151▕ });
    152▕ 
    153▕ test('learner with no enrollment is blocked', function () {
    154▕     // learner2@mindhub.test has no enrollment in course 1

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m learner with no enrollment is blocked                                                                         
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(155): getAuthHeadersForCommentTest('learner2@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(80): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_learner_with_no_enrollment_is_blocked()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m161[39m
    157▕     $response = $this->postJson('/api/lessons/2/comments', [
    158▕         'content' => 'Tôi chưa mua khóa học này.',
    159▕     ], $headers);
    160▕ 
  ➜ 161▕     $response->assertStatus(403);
    162▕ });
    163▕ 
    164▕ test('invalid query parameters return 422', function () {
    165▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m invalid query parameters return 422                                                                           
[39;1m  Expected response status code [422] but received 401.
Failed asserting that 401 is identical to 422.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(165): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(89): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_invalid_query_parameters_return_422()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m168[39m
    164▕ test('invalid query parameters return 422', function () {
    165▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');
    166▕ 
    167▕     $response = $this->getJson('/api/lessons/2/comments?rating=5', $headers);
  ➜ 168▕     $response->assertStatus(422);
    169▕ });
    170▕ 
    171▕ test('empty or too long content validation fails', function () {
    172▕     $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m empty or too long content validation fails                                                                    
[39;1m  Expected response status code [422] but received 401.
Failed asserting that 401 is identical to 422.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(172): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(98): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_empty_or_too_long_content_validation_fails()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m178[39m
    174▕     // Empty content
    175▕     $responseEmpty = $this->postJson('/api/lessons/2/comments', [
    176▕         'content' => '',
    177▕     ], $headers);
  ➜ 178▕     $responseEmpty->assertStatus(422);
    179▕ 
    180▕     // Too long content (>2000 chars)
    181▕     $responseLong = $this->postJson('/api/lessons/2/comments', [
    182▕         'content' => str_repeat('A', 2001),

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m instructor can reply to comment on their own published course lesson                                          
[39;1m  Expected response status code [201] but received 401.
Failed asserting that 401 is identical to 201.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(188): getAuthHeadersForCommentTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(107): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_instructor_can_reply_to_comment_on_their_own_published_course_lesson()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m194[39m
    190▕     $response = $this->postJson('/api/comments/1/replies', [
    191▕         'content' => 'Chào em, đây là câu trả lời từ giảng viên.',
    192▕     ], $headers);
    193▕ 
  ➜ 194▕     $response->assertStatus(201)
    195▕         ->assertJson([
    196▕             'success' => true,
    197▕             'message' => 'Thao tác thành công',
    198▕         ])

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m unauthenticated users cannot reply to comments                                              [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.comments' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: delete from `comments` where `id` > 5)[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39m:[32m605[39m
    601▕ 
    602▕             // For update or delete statements, we want to get the number of rows affected
    603▕             // by the statement and return that back to the developer. We'll first need
    604▕             // to execute the statement and then we'll use PDO to fetch the affected.
  ➜ 605▕             $statement = $this->getPdo()->prepare($query);
    606▕ 
    607▕             $this->bindValues($statement, $this->prepareBindings($bindings));
    608▕ 
    609▕             $statement->execute();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m605[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m learners cannot reply using the instructor endpoint                                                           
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"learn...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(227): getAuthHeadersForCommentTest('learner1@mindhu...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(125): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_learners_cannot_reply_using_the_instructor_endpoint()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m233[39m
    229▕     $response = $this->postJson('/api/comments/1/replies', [
    230▕         'content' => 'Học viên không được trả lời ở đây.',
    231▕     ], $headers);
    232▕ 
  ➜ 233▕     $response->assertStatus(403);
    234▕ });
    235▕ 
    236▕ test('replying to non-existent comment returns 404', function () {
    237▕     $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m replying to non-existent comment returns 404                                                                  
[39;1m  Expected response status code [404] but received 401.
Failed asserting that 401 is identical to 404.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(237): getAuthHeadersForCommentTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(134): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_replying_to_non_existent_comment_returns_404()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m243[39m
    239▕     $response = $this->postJson('/api/comments/999/replies', [
    240▕         'content' => 'Bình luận không tồn tại.',
    241▕     ], $headers);
    242▕ 
  ➜ 243▕     $response->assertStatus(404)
    244▕         ->assertJson([
    245▕             'success' => false,
    246▕             'message' => 'Không tìm thấy dữ liệu.',
    247▕         ]);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m replying to hidden or deleted comment returns 404                                                             
[39;1m  Expected response status code [404] but received 401.
Failed asserting that 401 is identical to 404.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(251): getAuthHeadersForCommentTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(143): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_replying_to_hidden_or_deleted_comment_returns_404()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m257[39m
    253▕     // Comment 3 is hidden, comment 4 is deleted
    254▕     $responseHidden = $this->postJson('/api/comments/3/replies', [
    255▕         'content' => 'Trả lời bình luận ẩn.',
    256▕     ], $headers);
  ➜ 257▕     $responseHidden->assertStatus(404);
    258▕ 
    259▕     $responseDeleted = $this->postJson('/api/comments/4/replies', [
    260▕         'content' => 'Trả lời bình luận đã xóa.',
    261▕     ], $headers);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m instructor cannot reply to Q&A of a course they do not own                                                    
[39;1m  Expected response status code [403] but received 401.
Failed asserting that 401 is identical to 403.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(267): getAuthHeadersForCommentTest('instructor2@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(152): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_instructor_cannot_reply_to_Q_A_of_a_course_they_do_not_own()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m273[39m
    269▕     $response = $this->postJson('/api/comments/1/replies', [
    270▕         'content' => 'Tôi không sở hữu khóa học này.',
    271▕     ], $headers);
    272▕ 
  ➜ 273▕     $response->assertStatus(403)
    274▕         ->assertJson([
    275▕             'success' => false,
    276▕             'message' => 'Bạn không được trả lời Q&A của khóa học này.',
    277▕         ]);

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m instructor cannot reply if lesson or course is not published                                [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.comments' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `comments` (`parent_id`, `user_id`, `lesson_id`, `content`, `status`, `updated_at`, `created_at`) values (?, 4, 5, Bình luận trên bài học bị ẩn., visible, 2026-07-21 01:39:39, 2026-07-21 01:39:39))[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InteractionCommentTest[22m [90m>[39m reply validation fails on invalid body inputs                                                                 
[39;1m  Expected response status code [422] but received 401.
Failed asserting that 401 is identical to 422.

The following exception occurred during the last request:

App\Exceptions\BusinessException: Email hoặc mật khẩu không đúng. in F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Services\Auth\AuthService.php:253
Stack trace:
#0 F:\Phatnt\laragon\www\MindHub-Backend\BE\app\Http\Controllers\AuthController.php(93): App\Services\Auth\AuthService->login(Array, Object(App\Http\Requests\Auth\LoginRequest))
#1 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php(46): App\Http\Controllers\AuthController->login(Object(App\Http\Requests\Auth\LoginRequest))
#2 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(265): Illuminate\Routing\ControllerDispatcher->dispatch(Object(Illuminate\Routing\Route), Object(App\Http\Controllers\AuthController), 'login')
#3 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Route.php(211): Illuminate\Routing\Route->runController()
#4 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(822): Illuminate\Routing\Route->run()
#5 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Routing\Router->Illuminate\Routing\{closure}(Object(Illuminate\Http\Request))
#6 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php(50): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#7 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Routing\Middleware\SubstituteBindings->handle(Object(Illuminate\Http\Request), Object(Closure))
#8 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#9 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(821): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#10 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(800): Illuminate\Routing\Router->runRouteWithinStack(Object(Illuminate\Routing\Route), Object(Illuminate\Http\Request))
#11 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(764): Illuminate\Routing\Router->runRoute(Object(Illuminate\Http\Request), Object(Illuminate\Routing\Route))
#12 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Routing\Router.php(753): Illuminate\Routing\Router->dispatchToRoute(Object(Illuminate\Http\Request))
#13 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(200): Illuminate\Routing\Router->dispatch(Object(Illuminate\Http\Request))
#14 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(180): Illuminate\Foundation\Http\Kernel->Illuminate\Foundation\Http\{closure}(Object(Illuminate\Http\Request))
#15 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#16 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php(31): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#17 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull->handle(Object(Illuminate\Http\Request), Object(Closure))
#18 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php(21): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#19 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php(51): Illuminate\Foundation\Http\Middleware\TransformsRequest->handle(Object(Illuminate\Http\Request), Object(Closure))
#20 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\TrimStrings->handle(Object(Illuminate\Http\Request), Object(Closure))
#21 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php(27): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#22 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePostSize->handle(Object(Illuminate\Http\Request), Object(Closure))
#23 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php(109): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#24 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance->handle(Object(Illuminate\Http\Request), Object(Closure))
#25 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php(74): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#26 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\HandleCors->handle(Object(Illuminate\Http\Request), Object(Closure))
#27 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php(58): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#28 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\TrustProxies->handle(Object(Illuminate\Http\Request), Object(Closure))
#29 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php(22): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#30 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks->handle(Object(Illuminate\Http\Request), Object(Closure))
#31 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php(26): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#32 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(219): Illuminate\Http\Middleware\ValidatePathEncoding->handle(Object(Illuminate\Http\Request), Object(Closure))
#33 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php(137): Illuminate\Pipeline\Pipeline->Illuminate\Pipeline\{closure}(Object(Illuminate\Http\Request))
#34 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(175): Illuminate\Pipeline\Pipeline->then(Object(Closure))
#35 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php(144): Illuminate\Foundation\Http\Kernel->sendRequestThroughRouter(Object(Illuminate\Http\Request))
#36 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(607): Illuminate\Foundation\Http\Kernel->handle(Object(Illuminate\Http\Request))
#37 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(573): Illuminate\Foundation\Testing\TestCase->call('POST', '/api/auth/login', Array, Array, Array, Array, '{"email":"instr...')
#38 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\laravel\framework\src\Illuminate\Foundation\Testing\Concerns\MakesHttpRequests.php(411): Illuminate\Foundation\Testing\TestCase->json('POST', '/api/auth/login', Array, Array, 0)
#39 [internal function]: Illuminate\Foundation\Testing\TestCase->postJson('/api/auth/login', Array)
#40 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\Reflection.php(38): ReflectionMethod->invoke(Object(P\Tests\Feature\InteractionCommentTest), '/api/auth/login', Array)
#41 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderMessage.php(58): Pest\Support\Reflection::call(Object(P\Tests\Feature\InteractionCommentTest), 'postJson', Array)
#42 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\HigherOrderTapProxy.php(64): Pest\Support\HigherOrderMessage->call(Object(P\Tests\Feature\InteractionCommentTest))
#43 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(9): Pest\Support\HigherOrderTapProxy->__call('postJson', Array)
#44 F:\Phatnt\laragon\www\MindHub-Backend\BE\tests\Feature\InteractionCommentTest.php(321): getAuthHeadersForCommentTest('instructor1@min...')
#45 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseMethodFactory.php(177): P\Tests\Feature\InteractionCommentTest->{closure}()
#46 [internal function]: P\Tests\Feature\InteractionCommentTest->Pest\Factories\{closure}()
#47 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): call_user_func_array(Object(Closure), Array)
#48 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Support\ExceptionTrace.php(26): P\Tests\Feature\InteractionCommentTest->Pest\Concerns\{closure}()
#49 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(576): Pest\Support\ExceptionTrace::ensure(Object(Closure))
#50 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Concerns\Testable.php(403): P\Tests\Feature\InteractionCommentTest->__callClosure(Object(Closure), Array)
#51 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Factories\TestCaseFactory.php(175) : eval()'d code(170): P\Tests\Feature\InteractionCommentTest->__runTest(Object(Closure))
#52 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1314): P\Tests\Feature\InteractionCommentTest->__pest_evaluable_reply_validation_fails_on_invalid_body_inputs()
#53 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(1351): PHPUnit\Framework\TestCase->invokeTestMethod('__pest_evaluabl...', Array)
#54 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(521): PHPUnit\Framework\TestCase->runTest()
#55 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestRunner\TestRunner.php(99): PHPUnit\Framework\TestCase->runBare()
#56 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestCase.php(361): PHPUnit\Framework\TestRunner->run(Object(P\Tests\Feature\InteractionCommentTest))
#57 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestCase->run()
#58 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#59 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\Framework\TestSuite.php(374): PHPUnit\Framework\TestSuite->run()
#60 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\TestRunner.php(64): PHPUnit\Framework\TestSuite->run()
#61 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\phpunit\phpunit\src\TextUI\Application.php(229): PHPUnit\TextUI\TestRunner->run(Object(PHPUnit\TextUI\Configuration\Configuration), Object(PHPUnit\Runner\ResultCache\DefaultResultCache), Object(PHPUnit\Framework\TestSuite))
#62 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\src\Kernel.php(117): PHPUnit\TextUI\Application->run(Array)
#63 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(207): Pest\Kernel->handle(Array, Array)
#64 F:\Phatnt\laragon\www\MindHub-Backend\BE\vendor\pestphp\pest\bin\pest(215): {closure}()
#65 {main}

----------------------------------------------------------------------------------

Email hoặc mật khẩu không đúng.[39;22m

  at [32mtests\Feature\InteractionCommentTest.php[39m:[32m327[39m
    323▕     // Empty content
    324▕     $responseEmpty = $this->postJson('/api/comments/1/replies', [
    325▕         'content' => '',
    326▕     ], $headers);
  ➜ 327▕     $responseEmpty->assertStatus(422);
    328▕ 
    329▕     // Too long content (>2000 chars)
    330▕     $responseLong = $this->postJson('/api/comments/1/replies', [
    331▕         'content' => str_repeat('A', 2001),


  [90mTests:[39m    [31;1m18 failed[39;22m[90m (18 assertions)[39m
  [90mDuration:[39m [39m2.83s[39m


## RevenueShare

  [30;42;1m PASS [39;49;22m[39m Tests\Feature\RevenueShareTest[39m
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with marketplace default[39m[90m                                                                                                    [39m [90m1.44s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with platform ads source[39m[90m                                                                                                    [39m [90m1.20s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with admin campaign source[39m[90m                                                                                                  [39m [90m1.37s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with instructor coupon[39m[90m                                                                                                      [39m [90m0.15s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with admin campaign coupon[39m[90m                                                                                                  [39m [90m0.14s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with instructor referral source[39m[90m                                                                                             [39m [90m0.13s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with invalid source fallback[39m[90m                                                                                                [39m [90m0.11s[39m  
  [32;1m✓[39;22m[90m [39m[90mcalculate revenue share with zero amount[39m[90m                                                                                                            [39m [90m0.13s[39m  
  [32;1m✓[39;22m[90m [39m[90mcreate revenue duplicate callback prevention[39m[90m                                                                                                        [39m [90m0.12s[39m  
  [32;1m✓[39;22m[90m [39m[90mgross consistency across all rules[39m[90m                                                                                                                  [39m [90m0.11s[39m  
  [32;1m✓[39;22m[90m [39m[90minstructor revenue list has metadata fields[39m[90m                                                                                                         [39m [90m0.18s[39m  
  [32;1m✓[39;22m[90m [39m[90minstructor revenue summary has source breakdown[39m[90m                                                                                                     [39m [90m0.17s[39m  

  [90mTests:[39m    [32;1m12 passed[39;22m[90m (90 assertions)[39m
  [90mDuration:[39m [39m5.68s[39m


## Withdrawal

  [39;41;1m FAIL [39;49;22m[39m Tests\Feature\InstructorWithdrawalApiTest[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can get withdrawal summary[39m
  [31;1m⨯[39;22m[90m [39m[90msummary returns notice when no active payout account[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can list withdrawals[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can filter withdrawals by status[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can filter withdrawals by requested date[39m
  [31;1m⨯[39;22m[90m [39m[90mindex rejects invalid status[39m
  [31;1m⨯[39;22m[90m [39m[90mindex rejects invalid date range[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can show withdrawal detail[39m
  [31;1m⨯[39;22m[90m [39m[90mshow rejects other instructor withdrawal[39m
  [31;1m⨯[39;22m[90m [39m[90mshow returns rejected reason[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can create withdrawal request[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate withdrawal reduces available balance in summary[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate rejects amount greater than available balance[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate rejects inactive payout account[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate rejects other user payout account[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate rejects client controlled fields[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate requires positive amount[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can get active payout accounts by default[39m
  [31;1m⨯[39;22m[90m [39m[90minstructor can get inactive payout accounts when filtered[39m
  [31;1m⨯[39;22m[90m [39m[90mpayout accounts reject invalid status[39m
  [31;1m⨯[39;22m[90m [39m[90msummary only subtracts pending and approved withdrawals[39m
  [31;1m⨯[39;22m[90m [39m[90msummary ignores non available revenues[39m
  [31;1m⨯[39;22m[90m [39m[90mcreate allows amount equal to available balance[39m
  [31;1m⨯[39;22m[90m [39m[90mcreated withdrawal has pending status and no approved or paid time[39m
  [31;1m⨯[39;22m[90m [39m[90mwithdrawal keeps payout account snapshot after account changes[39m
  [31;1m⨯[39;22m[90m [39m[90mwithdrawal list supports pagination[39m
  [31;1m⨯[39;22m[90m [39m[90mwithdrawal list status all returns all statuses[39m
  [31;1m⨯[39;22m[90m [39m[90mshow paid withdrawal has paid timeline completed[39m
  [31;1m⨯[39;22m[90m [39m[90mshow cancelled withdrawal has cancelled timeline[39m
  [31;1m⨯[39;22m[90m [39m[90mpayout account masks short account number[39m
  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can get withdrawal summary                                                  [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m summary returns notice when no active payout account                                   [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can list withdrawals                                                        [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can filter withdrawals by status                                            [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can filter withdrawals by requested date                                    [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m index rejects invalid status                                                           [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m index rejects invalid date range                                                       [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can show withdrawal detail                                                  [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m show rejects other instructor withdrawal                                               [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m show returns rejected reason                                                           [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can create withdrawal request                                               [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create withdrawal reduces available balance in summary                                 [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create rejects amount greater than available balance                                   [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create rejects inactive payout account                                                 [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create rejects other user payout account                                               [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create rejects client controlled fields                                                [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create requires positive amount                                                        [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can get active payout accounts by default                                   [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m instructor can get inactive payout accounts when filtered                              [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m payout accounts reject invalid status                                                  [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m summary only subtracts pending and approved withdrawals                                [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m summary ignores non available revenues                                                 [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m create allows amount equal to available balance                                        [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m created withdrawal has pending status and no approved or paid time                     [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m withdrawal keeps payout account snapshot after account changes                         [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m withdrawal list supports pagination                                                    [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m withdrawal list status all returns all statuses                                        [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m show paid withdrawal has paid timeline completed                                       [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m show cancelled withdrawal has cancelled timeline                                       [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m

  [31m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[39m  
  [41;1m FAILED [49;22m [1mTests\Feature\InstructorWithdrawalApiTest[22m [90m>[39m payout account masks short account number                                              [41;1m QueryException [49;22m  
[39;1m  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'mindhub.payout_accounts' doesn't exist (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: mindhub, SQL: insert into `payout_accounts` () values ())[39;22m

  at [32mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39m:[32m47[39m
     43▕             if ($this->pretending()) {
     44▕                 return true;
     45▕             }
     46▕ 
  ➜  47▕             $statement = $this->getPdo()->prepare($query);
     48▕ 
     49▕             $this->bindValues($statement, $this->prepareBindings($bindings));
     50▕ 
     51▕             $this->recordsHaveBeenModified();

  [33m1   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\MySqlConnection.php[39;22m:[39;1m47[39;22m
  [33m2   [39m[39;1mvendor\laravel\framework\src\Illuminate\Database\Connection.php[39;22m:[39;1m827[39;22m


  [90mTests:[39m    [31;1m30 failed[39;22m[90m (0 assertions)[39m
  [90mDuration:[39m [39m7.30s[39m


## Final git status
 M app/Http/Controllers/InstructorCourseController.php
 M app/Http/Controllers/InstructorWithdrawalController.php
 M app/Http/Controllers/InteractionController.php
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
 M routes/api/instructor.php
 M routes/api/marketing.php
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
?? database/migrations/2026_06_08_000000_import_base_schema.php
?? database/migrations/2026_07_15_000000_add_admin_columns.php
?? database/migrations/2026_07_15_000001_create_notifications_table.php
?? database/migrations/2026_07_20_000000_add_instructor_api_columns.php
?? database/migrations/2026_07_20_000000_create_commission_rules_table.php
?? database/migrations/2026_07_20_000001_add_revenue_share_source_columns.php
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
?? storage/convert-and-import.php
?? storage/copy-migrations-table.php
?? storage/debug-db.php
?? storage/find-create-revenues.php
?? storage/find-datn.php
?? storage/find-dots.php
?? storage/find-sessions.php
?? storage/fix-final-revenue-share-source-backup/
?? storage/fix-final-revenue-share-source-report/
?? storage/fix-instructor-api-p0-p1-backup/
?? storage/fix-revenue-share-source-errors-backup/
?? storage/fix_admin_model_relations.php
?? storage/fix_admin_revenue_groupby.php
?? storage/fix_admin_revenue_service_final.php
?? storage/fix_order_coupon_relation.php
?? storage/fix_order_revenue_relation.php
?? storage/get_admin_api_test_ids.php
?? storage/import-by-parts.php
?? storage/import-clean-db.php
?? storage/import-clean-no-tx.php
?? storage/import-no-truncate.php
?? storage/import-test-db.php
?? storage/import_sql.php
?? storage/instructor-api-full-retest-20260721-081126.txt
?? storage/instructor-api-full-retest-20260721-081622.txt
?? storage/instructor-api-retest-report/
?? storage/instructor-api-retest-utf8-20260721-083541/
?? storage/instructor-api-retest-utf8-20260721-083927/
?? storage/instructor-api-test-report/
?? storage/instructor-full-api-revenue-share-backup/
?? storage/instructor-full-api-test-20260721-080532.txt
?? storage/reset_admin_for_api_test.php
?? storage/revenue-share-final-retest-report/
?? storage/revenue-share-source-backup/
?? storage/revenue-share-source-retest-report/
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
?? test_block.php
?? tests/Feature/RevenueShareTest.php
?? ../MINDHUB_SQL_SCHEMA_FOR_ANTI.md
