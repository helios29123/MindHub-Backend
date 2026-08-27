# GROUP 1 FINISH REPORT

**Generated:** 2026-08-27T11:25:48
**Result:** CHƯA PASS
**Backup:** `D:\laragon\www\datn\MindHub-Backend\BE\_audit\backup_group1_finish_20260827_112545`

## 1. What this script changed
- Removed CoursePublicController::recordView from app\Http\Controllers\CoursePublicController.php
- Deleted unused legacy app/Services/Course/CourseViewService.php

## 2. Blocked
- Không có.

## 3. Targeted verification
- Category restore route: FAIL - còn tồn tại
- Course view route: FAIL - còn tồn tại
- CoursePublicController recordView: PASS
- CourseViewService reference: PASS

## 4. Previous Group 1 leftovers
### InstructorCourseService legacy: PASS
- CLEAN
### InstructorCourseRepository Quiz placeholders: PASS
- CLEAN
### Category fake SoftDelete API: PASS
- CLEAN
### Moderation deleted_at: PASS
- CLEAN

## 5. PHP lint
### `app\Http\Controllers\CoursePublicController.php` — PASS
```text
No syntax errors detected in D:\laragon\www\datn\MindHub-Backend\BE\app\Http\Controllers\CoursePublicController.php
```

## 6. php artisan about
Exit code: 0
```text

  Environment ......................................................................................................................  
  Application Name ......................................................................................................... Laravel  
  Laravel Version .......................................................................................................... 12.61.1  
  PHP Version ............................................................................................................... 8.3.31  
  Composer Version ........................................................................................................... 2.9.3  
  Environment ................................................................................................................ local  
  Debug Mode ............................................................................................................... ENABLED  
  URL ............................................................................................................... localhost:8000  
  Maintenance Mode ............................................................................................................. OFF  
  Timezone ..................................................................................................................... UTC  
  Locale ........................................................................................................................ en  

  Cache ............................................................................................................................  
  Config ................................................................................................................ NOT CACHED  
  Events ................................................................................................................ NOT CACHED  
  Routes ................................................................................................................ NOT CACHED  
  Views ................................................................................................................. NOT CACHED  

  Drivers ..........................................................................................................................  
  Broadcasting ................................................................................................................. log  
  Cache ....................................................................................................................... file  
  Database ................................................................................................................... mysql  
  Logs .............................................................................................................. stack / single  
  Mail ........................................................................................................................ smtp  
  Queue ....................................................................................................................... sync  
  Session ..................................................................................................................... file  

  Storage ..........................................................................................................................  
  D:\laragon\www\datn\MindHub-Backend\BE\public\storage ..................................................................... LINKED  

```

## 7. php artisan route:list
Exit code: 0
```text

  GET|HEAD        / ............................................................................................... routes/web.php:6
  GET|POST|HEAD   api/admin/banners ........................................................................ AdminController@banners
  GET|PUT|PATCH|DELETE|HEAD api/admin/banners/{id} ......................................................... AdminController@banners
  GET|POST|HEAD   api/admin/campaigns .................................................................. MarketingController@banners
  GET|PUT|PATCH|DELETE|HEAD api/admin/campaigns/{id} ................................................... MarketingController@banners
  GET|HEAD        api/admin/categories ............................................................... AdminCategoryController@index
  POST            api/admin/categories ............................................................... AdminCategoryController@store
  PUT             api/admin/categories/reorder ..................................................... AdminCategoryController@reorder
  GET|HEAD        api/admin/categories/{id} ........................................................... AdminCategoryController@show
  PUT             api/admin/categories/{id} ......................................................... AdminCategoryController@update
  PATCH           api/admin/categories/{id} ......................................................... AdminCategoryController@update
  DELETE          api/admin/categories/{id} ........................................................ AdminCategoryController@destroy
  POST            api/admin/categories/{id}/restore ................................................ AdminCategoryController@restore
  GET|HEAD        api/admin/course-reviews ................................................. AdminModerationController@courseReviews
  GET|HEAD        api/admin/courses ........................................................................ AdminController@courses
  PATCH           api/admin/courses/{courseId}/approve ....................................... AdminCourseApprovalController@approve
  PATCH           api/admin/courses/{courseId}/reject ......................................... AdminCourseApprovalController@reject
  GET|HEAD        api/admin/courses/{id} ................................................................ AdminController@showCourse
  PATCH           api/admin/courses/{id} .............................................................. AdminController@updateCourse
  GET|HEAD        api/admin/credit-packages ..................................................... AdminCreditPackageController@index
  POST            api/admin/credit-packages ..................................................... AdminCreditPackageController@store
  PATCH           api/admin/credit-packages/{packageId} ........................................ AdminCreditPackageController@update
  DELETE          api/admin/credit-packages/{packageId} ....................................... AdminCreditPackageController@destroy
  GET|HEAD        api/admin/dashboard ................................................................... ReportController@dashboard
  GET|HEAD        api/admin/faqs .......................................................................... AdminFaqController@index
  POST            api/admin/faqs .......................................................................... AdminFaqController@store
  PATCH           api/admin/faqs/reorder ................................................................ AdminFaqController@reorder
  GET|HEAD        api/admin/faqs/{id} ...................................................................... AdminFaqController@show
  PATCH           api/admin/faqs/{id} .................................................................... AdminFaqController@update
  DELETE          api/admin/faqs/{id} ................................................................... AdminFaqController@destroy
  PATCH           api/admin/faqs/{id}/courses ....................................................... AdminFaqController@syncCourses
  GET|HEAD        api/admin/instructor-upgrade-requests ..................................... InstructorUpgradeController@adminIndex
  GET|HEAD        api/admin/instructor-upgrade-requests/{userId} ............................. InstructorUpgradeController@adminShow
  PATCH           api/admin/instructor-upgrade-requests/{userId}/approve ....................... InstructorUpgradeController@approve
  PATCH           api/admin/instructor-upgrade-requests/{userId}/reject ......................... InstructorUpgradeController@reject
  GET|HEAD        api/admin/instructors/{instructorId}/credit-transactions ............ AdminInstructorCreditController@transactions
  GET|HEAD        api/admin/instructors/{instructorId}/credits ................................ AdminInstructorCreditController@show
  POST            api/admin/instructors/{instructorId}/credits/adjust ....................... AdminInstructorCreditController@adjust
  GET|HEAD        api/admin/moderation/items ............................................. AdminModerationController@moderationItems
  PATCH           api/admin/moderation/items/{id} ........................................... AdminModerationController@moderateItem
  GET|HEAD        api/admin/moderation/items/{targetType}/{id} ...................... AdminModerationController@moderationItemDetail
  GET|HEAD        api/admin/orders .......................................................................... AdminController@orders
  GET|HEAD        api/admin/orders/{id} .................................................................. AdminController@showOrder
  GET|HEAD        api/admin/payout-accounts ..................................................... AdminPayoutAccountController@index
  GET|HEAD        api/admin/payout-accounts/{id} ................................................. AdminPayoutAccountController@show
  PATCH           api/admin/payout-accounts/{id}/approve ...................................... AdminPayoutAccountController@approve
  PATCH           api/admin/payout-accounts/{id}/disable ...................................... AdminPayoutAccountController@disable
  PATCH           api/admin/payout-accounts/{id}/reject ........................................ AdminPayoutAccountController@reject
  GET|HEAD        api/admin/reports/instructors .................................................... ReportController@topInstructors
  GET|HEAD        api/admin/reports/revenue ......................................................... ReportController@revenueReport
  GET|HEAD        api/admin/reports/top-courses ........................................................ ReportController@topCourses
  GET|HEAD        api/admin/revenues ...................................................................... AdminController@revenues
  GET|HEAD        api/admin/revenues/{id} .............................................................. AdminController@showRevenue
  GET|HEAD        api/admin/roles ............................................................................ AdminController@roles
  POST            api/admin/roles ............................................................................ AdminController@roles
  GET|HEAD        api/admin/roles/{id} ....................................................................... AdminController@roles
  PUT             api/admin/roles/{id} ....................................................................... AdminController@roles
  PATCH           api/admin/roles/{id} ....................................................................... AdminController@roles
  DELETE          api/admin/roles/{id} ....................................................................... AdminController@roles
  GET|HEAD        api/admin/test ........................................................................... routes/api/admin.php:36
  GET|HEAD        api/admin/users ............................................................................ AdminController@users
  POST            api/admin/users ........................................................................ AdminController@storeUser
  GET|HEAD        api/admin/users/{id} .................................................................... AdminController@showUser
  PUT             api/admin/users/{id} .................................................................. AdminController@updateUser
  PATCH           api/admin/users/{id} .................................................................. AdminController@updateUser
  DELETE          api/admin/users/{id} .................................................................. AdminController@deleteUser
  GET|HEAD        api/admin/withdrawals ............................................................ AdminWithdrawalController@index
  GET|HEAD        api/admin/withdrawals/{id} ........................................................ AdminWithdrawalController@show
  PATCH           api/admin/withdrawals/{id}/approve ............................................. AdminWithdrawalController@approve
  PATCH           api/admin/withdrawals/{id}/mark-failed ...................................... AdminWithdrawalController@markFailed
  PATCH           api/admin/withdrawals/{id}/mark-paid .......................................... AdminWithdrawalController@markPaid
  PATCH           api/admin/withdrawals/{id}/reject ............................................... AdminWithdrawalController@reject
  POST            api/auth/forgot-password ........................................................... AuthController@forgotPassword
  POST            api/auth/google ....................................................................... AuthController@googleLogin
  GET|HEAD        api/auth/google/callback ........................................................... AuthController@googleCallback
  GET|HEAD        api/auth/google/redirect ........................................................... AuthController@googleRedirect
  POST            api/auth/login .............................................................................. AuthController@login
  POST            api/auth/logout ............................................................................ AuthController@logout
  GET|HEAD        api/auth/me .................................................................................... AuthController@me
  POST            api/auth/register ................................................................. AuthController@registerLearner
  POST            api/auth/register/instructor ................................................... AuthController@registerInstructor
  POST            api/auth/register/learner ......................................................... AuthController@registerLearner
  POST            api/auth/reset-password ............................................................. AuthController@resetPassword
  POST            api/auth/verify-email/resend .................................................... AuthController@resendVerifyEmail
  GET|HEAD        api/auth/verify-email/{id}/{hash} ................................. auth.verify-email › AuthController@verifyEmail
  POST            api/auth/verify-otp ..................................................................... AuthController@verifyOtp
  GET|HEAD        api/categories ...................................................................... CatalogController@categories
  POST            api/comments/{id}/replies ..................................................... InteractionController@replyComment
  GET|POST|HEAD   api/coupons/validate ............................................................ PaymentController@validateCoupon
  GET|HEAD        api/courses ......................................................................... CoursePublicController@index
  GET|HEAD        api/courses/ai-prompt ............................................................ CoursePublicController@aiPrompt
  POST            api/courses/ai-search ............................................................ CoursePublicController@aiSearch
  GET|HEAD        api/courses/featured ........................................................... CatalogController@featuredCourses
  GET|HEAD        api/courses/latest ............................................................... CatalogController@latestCourses
  GET|HEAD        api/courses/preview-lessons ............................................ CoursePublicController@previewLessonsList
  GET|HEAD        api/courses/sort ................................................................... CatalogController@sortCourses
  GET|HEAD        api/courses/{courseId}/related ............................................. CoursePublicController@relatedCourses
  POST            api/courses/{courseId}/view .................................................... CoursePublicController@recordView
  GET|HEAD        api/courses/{id}/completion-status ............................................... QuizController@completionStatus
  GET|HEAD        api/courses/{id}/faqs ................................................................ CoursePublicController@faqs
  GET|HEAD        api/courses/{id}/outline .......................................................... CoursePublicController@outline
  GET|HEAD        api/courses/{id}/reviews .......................................................... CoursePublicController@reviews
  POST            api/courses/{id}/reviews ....................................................... InteractionController@storeReview
  GET|HEAD        api/courses/{slug} ................................................................... CoursePublicController@show
  GET|HEAD        api/home .................................................................................. CatalogController@home
  GET|HEAD        api/instructor/coupons .......................................................... InstructorCouponController@index
  POST            api/instructor/coupons .......................................................... InstructorCouponController@store
  GET|HEAD        api/instructor/coupons/check-code ........................................... InstructorCouponController@checkCode
  GET|HEAD        api/instructor/coupons/course-options ................................... InstructorCouponController@courseOptions
  GET|HEAD        api/instructor/coupons/summary ................................................ InstructorCouponController@summary
  GET|HEAD        api/instructor/coupons/{id} ...................................................... InstructorCouponController@show
  PATCH           api/instructor/coupons/{id} .................................................... InstructorCouponController@update
  DELETE          api/instructor/coupons/{id} ................................................... InstructorCouponController@destroy
  PATCH           api/instructor/coupons/{id}/disable ........................................... InstructorCouponController@disable
  PATCH           api/instructor/coupons/{id}/enable ............................................. InstructorCouponController@enable
  PATCH           api/instructor/coupons/{id}/status ....................................... InstructorCouponController@updateStatus
  POST            api/instructor/course-announcements ...................................... MarketingController@courseAnnouncements
  GET|HEAD        api/instructor/course-credits ................................................. InstructorCreditController@balance
  POST            api/instructor/courses .......................................................... InstructorCourseController@store
  GET|HEAD        api/instructor/courses ............................... instructor.courses.index › InstructorCourseController@index
  POST            api/instructor/courses/draft ............................................... InstructorCourseController@storeDraft
  GET|HEAD        api/instructor/courses/summary ................................................ InstructorCourseController@summary
  GET|HEAD        api/instructor/courses/{courseId}/analytics ..................................... ReportController@courseAnalytics
  GET|HEAD        api/instructor/courses/{courseId}/checklist ................................. InstructorCourseController@checklist
  GET|HEAD        api/instructor/courses/{courseId}/learner-risk ...................................... ReportController@learnerRisk
  GET|HEAD        api/instructor/courses/{id} ...................................................... InstructorCourseController@show
  PATCH           api/instructor/courses/{id} .................................................... InstructorCourseController@update
  DELETE          api/instructor/courses/{id} ................................................... InstructorCourseController@destroy
  GET|HEAD        api/instructor/courses/{id}/content ........................................... InstructorCourseController@content
  GET|HEAD        api/instructor/courses/{id}/dashboard ........................................... ReportController@courseDashboard
  PATCH           api/instructor/courses/{id}/draft ......................................... InstructorCourseController@updateDraft
  PATCH           api/instructor/courses/{id}/hide ................................................. InstructorCourseController@hide
  GET|HEAD        api/instructor/courses/{id}/learners ......................................... InstructorCourseController@learners
  GET|HEAD        api/instructor/courses/{id}/review-notes .................................. InstructorCourseController@reviewNotes
  POST            api/instructor/courses/{id}/submit .................................... InstructorCourseController@submitForReview
  PATCH           api/instructor/courses/{id}/unhide ............................................. InstructorCourseController@unhide
  POST            api/instructor/credit-orders .............................................. InstructorCreditController@createOrder
  GET|HEAD        api/instructor/credit-packages ............................................... InstructorCreditController@packages
  GET|HEAD        api/instructor/credit-transactions ....................................... InstructorCreditController@transactions
  GET|HEAD        api/instructor/dashboard ............................. instructor.dashboard › ReportController@instructorDashboard
  GET|HEAD        api/instructor/dashboard/alerts ......... instructor.dashboard.alerts › ReportController@instructorDashboardAlerts
  GET|HEAD        api/instructor/dashboard/enrollment-chart instructor.dashboard.enrollment-chart › ReportController@instructorEnro…
  GET|HEAD        api/instructor/dashboard/incomplete-courses instructor.dashboard.incomplete-courses › ReportController@incomplete…
  GET|HEAD        api/instructor/dashboard/revenue-chart instructor.dashboard.revenue-chart › ReportController@instructorRevenueCha…
  GET|HEAD        api/instructor/dashboard/top-courses .... instructor.dashboard.top-courses › ReportController@instructorTopCourses
  GET|HEAD        api/instructor/discount-codes ................................................... InstructorCouponController@index
  POST            api/instructor/discount-codes ................................................... InstructorCouponController@store
  GET|HEAD        api/instructor/discount-codes/check-code .................................... InstructorCouponController@checkCode
  GET|HEAD        api/instructor/discount-codes/course-options ............................ InstructorCouponController@courseOptions
  GET|HEAD        api/instructor/discount-codes/summary ......................................... InstructorCouponController@summary
  GET|HEAD        api/instructor/discount-codes/{id} ............................................... InstructorCouponController@show
  PATCH           api/instructor/discount-codes/{id} ............................................. InstructorCouponController@update
  DELETE          api/instructor/discount-codes/{id} ............................................ InstructorCouponController@destroy
  PATCH           api/instructor/discount-codes/{id}/disable .................................... InstructorCouponController@disable
  PATCH           api/instructor/discount-codes/{id}/enable ...................................... InstructorCouponController@enable
  PATCH           api/instructor/discount-codes/{id}/status ................................ InstructorCouponController@updateStatus
  GET|HEAD        api/instructor/early-withdrawals ............................................ InstructorWithdrawalController@index
  POST            api/instructor/early-withdrawals ............................ InstructorWithdrawalController@createEarlyWithdrawal
  POST            api/instructor/early-withdrawals/request-otp ............ InstructorWithdrawalController@requestEarlyWithdrawalOtp
  GET|HEAD        api/instructor/early-withdrawals/{id} ........................................ InstructorWithdrawalController@show
  PATCH           api/instructor/early-withdrawals/{id}/cancel ............................... InstructorWithdrawalController@cancel
  GET|HEAD        api/instructor/enrollments ................................................ InstructorCourseController@allLearners
  GET|HEAD        api/instructor/learners ....................... instructor.learners.index › InstructorCourseController@allLearners
  GET|HEAD        api/instructor/learners/chart ........................................... InstructorCourseController@learnersChart
  GET|HEAD        api/instructor/learners/export ......................................... InstructorCourseController@exportLearners
  GET|HEAD        api/instructor/learners/summary ....................................... InstructorCourseController@learnersSummary
  GET|HEAD        api/instructor/learners/{id} ....................................... InstructorCourseController@showLearnerDetails
  GET|HEAD        api/instructor/lessons ................................................... InstructorCourseController@indexLessons
  POST            api/instructor/lessons .................................................... InstructorCourseController@storeLesson
  GET|HEAD        api/instructor/lessons/{id} ................................................ InstructorCourseController@showLesson
  PUT|PATCH       api/instructor/lessons/{id} .............................................. InstructorCourseController@updateLesson
  DELETE          api/instructor/lessons/{id} ............................................. InstructorCourseController@destroyLesson
  POST            api/instructor/lessons/{id}/assets ........................................ InstructorCourseController@uploadAsset
  PATCH           api/instructor/lessons/{id}/preview ..................................... InstructorCourseController@togglePreview
  POST            api/instructor/lessons/{id}/video ......................................... InstructorCourseController@uploadVideo
  POST            api/instructor/media/upload ............................................... InstructorCourseController@uploadMedia
  GET|HEAD        api/instructor/notifications .............................................. InstructorNotificationController@index
  PATCH           api/instructor/notifications/read-all ................................... InstructorNotificationController@readAll
  GET|HEAD        api/instructor/notifications/unread-count ........................... InstructorNotificationController@unreadCount
  GET|HEAD        api/instructor/notifications/{id} .......................................... InstructorNotificationController@show
  DELETE          api/instructor/notifications/{id} ....................................... InstructorNotificationController@destroy
  PATCH           api/instructor/notifications/{id}/read ..................................... InstructorNotificationController@read
  GET|HEAD        api/instructor/payments/summary ........................................... InstructorWithdrawalController@summary
  GET|HEAD        api/instructor/payout-accounts ........................................... InstructorPayoutAccountController@index
  POST            api/instructor/payout-accounts ........................................... InstructorPayoutAccountController@store
  GET|HEAD        api/instructor/payout-accounts/default ................................. InstructorPayoutAccountController@default
  GET|HEAD        api/instructor/payout-accounts/{id} ....................................... InstructorPayoutAccountController@show
  PATCH           api/instructor/payout-accounts/{id} ..................................... InstructorPayoutAccountController@update
  DELETE          api/instructor/payout-accounts/{id} .................................... InstructorPayoutAccountController@destroy
  POST            api/instructor/payout-accounts/{id}/reveal .............................. InstructorPayoutAccountController@reveal
  POST            api/instructor/payout-accounts/{id}/send-change-otp .............. InstructorPayoutAccountController@sendChangeOtp
  PATCH           api/instructor/payout-accounts/{id}/set-default ..................... InstructorPayoutAccountController@setDefault
  POST            api/instructor/payout-accounts/{id}/verify-change ................. InstructorPayoutAccountController@verifyChange
  GET|HEAD        api/instructor/payouts ...................................................... InstructorWithdrawalController@index
  GET|HEAD        api/instructor/payouts/{id} .................................................. InstructorWithdrawalController@show
  GET|HEAD        api/instructor/profile .......................................................... InstructorProfileController@show
  PATCH           api/instructor/profile ........................................................ InstructorProfileController@update
  PATCH           api/instructor/profile/account ......................................... InstructorProfileController@updateAccount
  GET|HEAD        api/instructor/profile/account-status ............................... InstructorProfileController@getAccountStatus
  POST            api/instructor/profile/avatar ........................................... InstructorProfileController@uploadAvatar
  DELETE          api/instructor/profile/avatar ........................................... InstructorProfileController@deleteAvatar
  PATCH           api/instructor/profile/avatar/preset .............................. InstructorProfileController@selectAvatarPreset
  GET|HEAD        api/instructor/profile/completion ......................................... InstructorProfileController@completion
  PATCH           api/instructor/profile/expertise ..................................... InstructorProfileController@updateExpertise
  PATCH           api/instructor/profile/introduction ............................... InstructorProfileController@updateIntroduction
  GET|HEAD        api/instructor/profile/notification-preferences ....................... InstructorProfileController@getPreferences
  PATCH           api/instructor/profile/notification-preferences .................... InstructorProfileController@updatePreferences
  PATCH           api/instructor/profile/password ....................................... InstructorProfileController@changePassword
  POST            api/instructor/profile/password/send-otp ............................. InstructorProfileController@sendPasswordOtp
  GET|HEAD        api/instructor/profile/privacy ............................................ InstructorProfileController@getPrivacy
  PATCH           api/instructor/profile/privacy ......................................... InstructorProfileController@updatePrivacy
  GET|HEAD        api/instructor/profile/sessions .......................................... InstructorProfileController@getSessions
  DELETE          api/instructor/profile/sessions/others ........................... InstructorProfileController@revokeOtherSessions
  DELETE          api/instructor/profile/sessions/{id} ................................... InstructorProfileController@revokeSession
  GET|HEAD        api/instructor/questions .................. instructor.questions.index › InteractionController@instructorQuestions
  GET|HEAD        api/instructor/questions/course-options .................... InteractionController@instructorQuestionCourseOptions
  GET|HEAD        api/instructor/questions/lesson-options .................... InteractionController@instructorQuestionLessonOptions
  GET|HEAD        api/instructor/questions/summary ................................. InteractionController@instructorQuestionSummary
  GET|HEAD        api/instructor/questions/{id} ....................................... InteractionController@showInstructorQuestion
  DELETE          api/instructor/questions/{id} ..................................... InteractionController@deleteInstructorQuestion
  PATCH           api/instructor/questions/{id}/hide .................................. InteractionController@hideInstructorQuestion
  PATCH           api/instructor/questions/{id}/replies/{replyId} .............. InteractionController@updateInstructorQuestionReply
  DELETE          api/instructor/questions/{id}/replies/{replyId} .............. InteractionController@deleteInstructorQuestionReply
  POST            api/instructor/questions/{id}/reply ................................ InteractionController@replyInstructorQuestion
  PATCH           api/instructor/questions/{id}/show ............................ InteractionController@showHiddenInstructorQuestion
  POST            api/instructor/questions/{id}/star .................................. InteractionController@starInstructorQuestion
  DELETE          api/instructor/questions/{id}/star ................................ InteractionController@unstarInstructorQuestion
  PATCH           api/instructor/questions/{id}/status ........................ InteractionController@updateInstructorQuestionStatus
  GET|POST|HEAD   api/instructor/quizzes .................................................. InstructorCourseController@manageQuizzes
  GET|PUT|PATCH|DELETE|HEAD api/instructor/quizzes/{id} ................................... InstructorCourseController@manageQuizzes
  GET|HEAD        api/instructor/reports/completion-rate ........................................... ReportController@completionRate
  GET|HEAD        api/instructor/reports/enrollment-chart instructor.reports.enrollment-chart › ReportController@instructorEnrollme…
  GET|HEAD        api/instructor/reports/inactive-learners ....................................... ReportController@inactiveLearners
  GET|HEAD        api/instructor/reports/revenue-chart .. instructor.reports.revenue-chart › ReportController@instructorRevenueChart
  GET|HEAD        api/instructor/reports/top-courses ........ instructor.reports.top-courses › ReportController@instructorTopCourses
  GET|HEAD        api/instructor/revenue ........................................................ InstructorCourseController@revenue
  GET|HEAD        api/instructor/revenues .......................................................... ReportController@revenueDetails
  GET|HEAD        api/instructor/revenues/chart ............................................ ReportController@instructorRevenueChart
  GET|HEAD        api/instructor/revenues/course-breakdown ........................................ ReportController@courseBreakdown
  GET|HEAD        api/instructor/revenues/details .................................................. ReportController@revenueDetails
  GET|HEAD        api/instructor/revenues/enrollment-chart .............................. ReportController@instructorEnrollmentChart
  GET|HEAD        api/instructor/revenues/export ................................................... ReportController@exportRevenues
  GET|HEAD        api/instructor/revenues/summary .................................................. ReportController@revenueSummary
  GET|HEAD        api/instructor/revenues/top-courses ......................................... ReportController@topCoursesByRevenue
  GET|HEAD        api/instructor/sections ...................................................... InstructorCourseController@sections
  POST            api/instructor/sections .................................................. InstructorCourseController@storeSection
  GET|HEAD        api/instructor/sections/{id} .............................................. InstructorCourseController@showSection
  PUT             api/instructor/sections/{id} ............................................ InstructorCourseController@updateSection
  PATCH           api/instructor/sections/{id} ............................................ InstructorCourseController@updateSection
  DELETE          api/instructor/sections/{id} ............................................ InstructorCourseController@deleteSection
  GET|HEAD        api/instructor/withdrawals ................... instructor.withdrawals.index › InstructorWithdrawalController@index
  POST            api/instructor/withdrawals .................................................. InstructorWithdrawalController@store
  GET|HEAD        api/instructor/withdrawals/summary ....... instructor.withdrawals.summary › InstructorWithdrawalController@summary
  GET|HEAD        api/instructor/withdrawals/{id} .............................................. InstructorWithdrawalController@show
  PATCH           api/instructor/withdrawals/{id}/cancel ..................................... InstructorWithdrawalController@cancel
  GET|HEAD        api/instructor/{instructorId}/enrollments ................................. InstructorCourseController@allLearners
  GET|HEAD        api/instructors ............................................................ CatalogController@featuredInstructors
  GET|HEAD        api/instructors/featured ................................................... CatalogController@featuredInstructors
  GET|HEAD        api/instructors/{id} ....................................................... CoursePublicController@showInstructor
  POST            api/learn/assets/{assetId}/signed-url .......................................... LearningController@signedAssetUrl
  GET|HEAD        api/learn/assets/{id}/download .................................................. LearningController@downloadAsset
  GET|HEAD        api/learn/courses/{id}/outline ........................................................ LearningController@outline
  GET|HEAD        api/learn/courses/{id}/progress ................................................ LearningController@courseProgress
  GET|HEAD        api/learn/lessons/{id} ............................................................. LearningController@showLesson
  GET|HEAD        api/learn/lessons/{id}/check-access ........................................... LearningController@canAccessLesson
  PATCH           api/learn/lessons/{id}/complete ................................................ LearningController@completeLesson
  GET|HEAD        api/learn/lessons/{id}/next ........................................................ LearningController@nextLesson
  GET|HEAD        api/learn/lessons/{id}/notes ................................................... LearningController@getLessonNotes
  POST            api/learn/lessons/{id}/notes ................................................. LearningController@createLessonNote
  PATCH           api/learn/lessons/{id}/progress ............................................. LearningController@saveVideoProgress
  GET|HEAD        api/learn/lessons/{id}/stream ........................ learn.lessons.stream › LearningController@streamLessonVideo
  GET|HEAD        api/learn/lessons/{id}/video-url ......................................... LearningController@signedLessonVideoUrl
  GET|HEAD        api/learn/lessons/{lessonId}/watermark-info ..................................... LearningController@watermarkInfo
  PUT             api/learn/notes/{id} ......................................................... LearningController@updateLessonNote
  DELETE          api/learn/notes/{id} ......................................................... LearningController@deleteLessonNote
  GET|HEAD        api/learn/resume ....................................................................... LearningController@resume
  GET|HEAD        api/learning-logs/my ............................................................. LearningController@learningLogs
  GET|POST|HEAD   api/lessons/{id}/comments ................................................... InteractionController@lessonComments
  GET|HEAD        api/lessons/{id}/preview .................................................... CoursePublicController@previewLesson
  GET|HEAD        api/me/activity-calendar ..................................................... LearningController@activityCalendar
  GET|HEAD        api/me/courses ...................................................................... LearningController@myCourses
  GET|HEAD        api/me/dynamic-alerts ........................................................... LearningController@dynamicAlerts
  GET|HEAD        api/me/instructor-upgrade .............................................. InstructorUpgradeController@myApplication
  POST            api/me/instructor-upgrade ...................................................... InstructorUpgradeController@store
  PUT             api/me/instructor-upgrade ..................................................... InstructorUpgradeController@update
  GET|HEAD        api/me/learning-dashboard ........................................................... LearningController@dashboard
  GET|HEAD        api/me/learning-path/next .................................................... LearningController@nextLearningPath
  GET|HEAD        api/me/recommendations/rule-based .................................... LearningController@ruleBasedRecommendations
  GET|HEAD        api/me/streak .......................................................................... LearningController@streak
  GET|HEAD        api/notifications ............................................................... UserNotificationController@index
  DELETE          api/notifications/clear-all .................................................. UserNotificationController@clearAll
  PATCH           api/notifications/read-all .................................................... UserNotificationController@readAll
  DELETE          api/notifications/{id} ........................................................ UserNotificationController@destroy
  PATCH           api/notifications/{id}/read ...................................................... UserNotificationController@read
  POST            api/orders .......................................................................... PaymentController@storeOrder
  POST            api/orders/apply-coupon ............................................................ PaymentController@applyCoupon
  GET|HEAD        api/orders/check-coupon ............................................................ PaymentController@checkCoupon
  GET|HEAD        api/orders/my ......................................................................... PaymentController@myOrders
  GET|HEAD        api/orders/{id} ...................................................................... PaymentController@showOrder
  PATCH           api/orders/{orderId}/cancel ........................................................ PaymentController@cancelOrder
  POST            api/orders/{orderId}/retry-payment ................................................ PaymentController@retryPayment
  POST            api/payments ...................................................................... PaymentController@storePayment
  POST            api/payments/sepay/confirm ................................................. PaymentController@confirmSepayPayment
  POST            api/payments/sepay/create ................................................... PaymentController@createSepayPayment
  POST            api/payments/sepay/webhook ........................................................ PaymentController@sepayWebhook
  GET|HEAD        api/payments/vnpay-return .......................................................... PaymentController@vnpayReturn
  POST            api/payments/vnpay/create ................................................... PaymentController@createVnpayPayment
  POST            api/payments/webhook ................................................................... PaymentController@webhook
  GET|HEAD        api/quiz-attempts/{id} ................................................................ QuizController@showAttempt
  POST            api/quizzes/{id}/attempts ............................................................ QuizController@storeAttempt
  GET|HEAD        api/search/suggestions ....................................................... CatalogController@searchSuggestions
  GET|HEAD        api/users/me ............................................................................ UserProfileController@me
  PATCH           api/users/me ...................................................................... UserProfileController@updateMe
  POST            api/users/me/avatar ........................................................... UserProfileController@uploadAvatar
  DELETE          api/users/me/avatar ........................................................... UserProfileController@deleteAvatar
  PATCH           api/users/me/avatar/preset .............................................. UserProfileController@selectAvatarPreset
  PATCH           api/users/me/password ....................................................... UserProfileController@changePassword
  GET|HEAD        api/wishlists ........................................................................... WishlistController@index
  POST            api/wishlists ........................................................................... WishlistController@store
  DELETE          api/wishlists/{courseId} .............................................................. WishlistController@destroy
  GET|HEAD        auth/google/callback ............................................................... AuthController@googleCallback
  GET|HEAD        auth/google/redirect ............................................................... AuthController@googleRedirect
  GET|HEAD        sanctum/csrf-cookie ............................ sanctum.csrf-cookie › Laravel\Sanctum › CsrfCookieController@show
  GET|HEAD        storage/{path} storage.local › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.php:98
  PUT             storage/{path} storage.local.upload › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvide…
  GET|HEAD        up ................... vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:219

                                                                                                                Showing [326] routes

```

## 8. git status
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
?? .codegraph/
?? _audit/
?? _scripts/
```

## 9. git diff --stat
```text
 .../Http/Controllers/AdminCategoryController.php   |   7 -
 BE/app/Http/Controllers/CoursePublicController.php |  18 --
 BE/app/Http/Controllers/InteractionController.php  |   2 +-
 BE/app/Http/Controllers/ReportController.php       |  10 +-
 .../Repositories/Admin/AdminCategoryRepository.php |  39 +--
 .../Repositories/Admin/AdminCourseRepository.php   |   6 +-
 BE/app/Repositories/Catalog/BannerRepository.php   |   2 +-
 .../Catalog/CatalogCourseRepository.php            |  43 +--
 BE/app/Repositories/Catalog/CategoryRepository.php |   9 +-
 .../Catalog/FeaturedInstructorRepository.php       |   2 +-
 .../Instructor/InstructorCourseRepository.php      | 153 ++-------
 .../Instructor/InstructorLearnerRepository.php     |  12 +-
 .../Instructor/InstructorLessonRepository.php      |  16 +-
 .../Instructor/InstructorRevenueRepository.php     |   2 +-
 .../Interaction/InstructorQuestionRepository.php   |   2 +-
 .../Moderation/CourseModerationRepository.php      |   3 +-
 .../Report/InstructorDashboardAlertRepository.php  |   2 +-
 .../Report/InstructorDashboardRepository.php       |   6 +-
 .../Report/InstructorEnrollmentChartRepository.php |   2 +-
 .../Repositories/Wishlist/WishlistRepository.php   |  10 +-
 BE/app/Services/Admin/AdminCategoryService.php     |  34 +-
 BE/app/Services/Admin/AdminCourseService.php       |  17 +-
 .../Services/Admin/AdminPayoutAccountService.php   |   2 +-
 BE/app/Services/Admin/AdminService.php             |  17 +-
 BE/app/Services/AdminService.php                   |  57 ----
 BE/app/Services/Catalog/CatalogService.php         |  12 +-
 .../Services/Course/CourseAvailabilityService.php  |   4 +-
 BE/app/Services/Course/CoursePublicService.php     |   3 -
 BE/app/Services/Course/CourseViewService.php       | 121 --------
 BE/app/Services/Course/RelatedCourseService.php    |   2 +-
 BE/app/Services/CoursePublicService.php            | 343 ---------------------
 BE/app/Services/Faq/FaqAdminService.php            |  29 +-
 .../Services/Instructor/CourseChecklistService.php |  62 +---
 BE/app/Services/Instructor/CourseCreditService.php |   5 -
 .../Instructor/InstructorCourseService.php         | 320 ++++---------------
 .../Instructor/InstructorCreditOrderService.php    |   2 -
 .../Instructor/InstructorUpgradeService.php        |   6 +-
 .../Interaction/InstructorQuestionService.php      |  19 +-
 BE/app/Services/InteractionService.php             | 158 ----------
 BE/app/Services/MarketingService.php               |  65 ----
 BE/app/Services/Moderation/ModerationService.php   |  12 +-
 BE/app/Services/ModerationService.php              |  47 ---
 BE/app/Services/QuizService.php                    | 147 ---------
 BE/app/Services/Report/ReportService.php           |   2 +-
 44 files changed, 189 insertions(+), 1643 deletions(-)
```

## 10. full git diff
```diff
diff --git a/BE/app/Http/Controllers/AdminCategoryController.php b/BE/app/Http/Controllers/AdminCategoryController.php
index d86a52c..b23c05b 100644
--- a/BE/app/Http/Controllers/AdminCategoryController.php
+++ b/BE/app/Http/Controllers/AdminCategoryController.php
@@ -70,13 +70,6 @@ public function destroy(int $id): JsonResponse
         return ApiResponse::success(null, 'Xóa danh mục thành công.');
     }
 
-    public function restore(int $id): JsonResponse
-    {
-        return ApiResponse::success(
-            new AdminCategoryResource($this->categoryService->restore($id)),
-            'Khôi phục danh mục thành công.'
-        );
-    }
 
     public function reorder(ReorderCategoryRequest $request): JsonResponse
     {
diff --git a/BE/app/Http/Controllers/CoursePublicController.php b/BE/app/Http/Controllers/CoursePublicController.php
index 06c909b..4cb0a16 100644
--- a/BE/app/Http/Controllers/CoursePublicController.php
+++ b/BE/app/Http/Controllers/CoursePublicController.php
@@ -246,24 +246,6 @@ public function relatedCourses(\App\Http\Requests\Course\RelatedCourseRequest $r
         );
     }
 
-    public function recordView(mixed $id): JsonResponse
-    {
-        $course = \App\Models\Course::find($id);
-
-        if (!$course) {
-            return ApiResponse::error('Không tìm thấy khóa học.', [], 404);
-        }
-
-        $recorded = app(\App\Services\Course\CourseViewService::class)->recordView(
-            $course,
-            request()->user(),
-            request()
-        );
-
-        return ApiResponse::success([
-            'recorded' => $recorded,
-        ], 'Ghi nhận lượt xem thành công.');
-    }
 
     public function aiSearch(): JsonResponse
     {
diff --git a/BE/app/Http/Controllers/InteractionController.php b/BE/app/Http/Controllers/InteractionController.php
index 49989a0..fa904bf 100644
--- a/BE/app/Http/Controllers/InteractionController.php
+++ b/BE/app/Http/Controllers/InteractionController.php
@@ -208,7 +208,7 @@ public function instructorQuestionLessonOptions(Request $request): JsonResponse
             $lessons = \Illuminate\Support\Facades\DB::table('lessons')
                 ->join('courses', 'courses.id', '=', 'lessons.course_id')
                 ->where('courses.instructor_id', $instructorId)
-                ->whereNull('courses.deleted_at')
+                
                 ->select('lessons.id', 'lessons.title')
                 ->when($courseId, function ($query, $courseId) {
                     $query->where('lessons.course_id', $courseId);
diff --git a/BE/app/Http/Controllers/ReportController.php b/BE/app/Http/Controllers/ReportController.php
index 0096268..53b7064 100644
--- a/BE/app/Http/Controllers/ReportController.php
+++ b/BE/app/Http/Controllers/ReportController.php
@@ -388,7 +388,7 @@ public function courseBreakdown(Request $request): JsonResponse
                     ->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
             })
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at');
+            ;
 
         if ($request->query('course_id') && $request->query('course_id') !== 'all') {
             $query->where('courses.id', (int) $request->query('course_id'));
@@ -432,7 +432,7 @@ public function topCoursesByRevenue(Request $request): JsonResponse
 
         $coursesQuery = \Illuminate\Support\Facades\DB::table('courses')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at');
+            ;
 
         if ($request->query('course_id') && $request->query('course_id') !== 'all') {
             $coursesQuery->where('courses.id', (int) $request->query('course_id'));
@@ -529,7 +529,7 @@ public function revenueSummary(\Illuminate\Http\Request $request): JsonResponse
                 $q->where('revenues.instructor_id', $instructorId)
                   ->orWhere('courses.instructor_id', $instructorId);
             })
-            ->whereNull('courses.deleted_at')
+            
             ->whereIn('revenues.status', ['pending', 'available', 'scheduled', 'included_in_payout', 'paid', 'withdrawn']);
 
         if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
@@ -666,7 +666,7 @@ public function revenueDetails(Request $request): JsonResponse
                 $q->where('revenues.instructor_id', $instructorId)
                   ->orWhere('courses.instructor_id', $instructorId);
             })
-            ->whereNull('courses.deleted_at');
+            ;
 
         if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
             $query->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
@@ -746,7 +746,7 @@ public function exportRevenues(Request $request): \Symfony\Component\HttpFoundat
                 $q->where('revenues.instructor_id', $instructorId)
                   ->orWhere('courses.instructor_id', $instructorId);
             })
-            ->whereNull('courses.deleted_at');
+            ;
 
         if ($period['preset'] !== 'all' && $period['preset'] !== 'all_time') {
             $query->whereBetween('revenues.earned_at', [$period['current_from'], $period['current_to']]);
diff --git a/BE/app/Repositories/Admin/AdminCategoryRepository.php b/BE/app/Repositories/Admin/AdminCategoryRepository.php
index c1d584a..88c724e 100644
--- a/BE/app/Repositories/Admin/AdminCategoryRepository.php
+++ b/BE/app/Repositories/Admin/AdminCategoryRepository.php
@@ -8,7 +8,6 @@
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\DB;
-use Illuminate\Support\Facades\Schema;
 
 final class AdminCategoryRepository
 {
@@ -17,11 +16,7 @@ public function paginate(array $filters): LengthAwarePaginator
         $query = Category::query()->with('parent')->withCount('courses');
         $status = $filters['status'] ?? null;
 
-        if ($status === 'deleted') {
-            $query->onlyTrashed();
-        } elseif ($status === 'all_with_deleted') {
-            $query->withTrashed();
-        } elseif (in_array($status, ['active', 'inactive'], true)) {
+        if (in_array($status, ['active', 'inactive'], true)) {
             $query->where('status', $status);
         }
 
@@ -101,19 +96,11 @@ public function findWithRelations(int $id): ?Category
             ->groupBy('course_id')
             ->pluck('aggregate', 'course_id');
 
-        $reviewQuery = DB::table('course_reviews')->whereIn('course_id', $courseIds);
-        if (Schema::hasColumn('course_reviews', 'deleted_at')) {
-            $reviewQuery->whereNull('deleted_at');
-        }
         $reviewAggregates = empty($courseIds)
             ? collect()
             : DB::table('course_reviews as reviews')
             ->join('orders as orders', 'orders.id', '=', 'reviews.order_id')
             ->whereIn('orders.course_id', $courseIds)
-            ->when(
-                Schema::hasColumn('course_reviews', 'deleted_at'),
-                fn($query) => $query->whereNull('reviews.deleted_at')
-            )
             ->selectRaw(
                 'orders.course_id as course_id,
              COUNT(reviews.id) as review_count,
@@ -149,15 +136,7 @@ public function findWithRelations(int $id): ?Category
         return $category;
     }
 
-    public function findWithTrashed(int $id): ?Category
-    {
-        return Category::withTrashed()->find($id);
-    }
 
-    public function findOnlyTrashed(int $id): ?Category
-    {
-        return Category::onlyTrashed()->find($id);
-    }
 
     public function findActiveRoot(int $id): ?Category
     {
@@ -185,22 +164,10 @@ public function hasCourses(Category $category): bool
         return $category->courses()->exists();
     }
 
-    public function nextSortOrder(?int $parentId): string
+    public function nextSortOrder(?int $parentId): int
     {
         $max = Category::query()->where('parent_id', $parentId)->max('sort_order');
-        if (!$max) {
-            return 'a';
-        }
-        
-        $len = strlen($max);
-        $lastChar = $max[$len - 1];
-        
-        if ($lastChar < 'z') {
-            $max[$len - 1] = chr(ord($lastChar) + 1);
-            return $max;
-        } else {
-            return $max . 'n';
-        }
+        return ((int) $max) + 1;
     }
 
     public function allParentMap(): Collection
diff --git a/BE/app/Repositories/Admin/AdminCourseRepository.php b/BE/app/Repositories/Admin/AdminCourseRepository.php
index fa1aee6..d45b490 100644
--- a/BE/app/Repositories/Admin/AdminCourseRepository.php
+++ b/BE/app/Repositories/Admin/AdminCourseRepository.php
@@ -8,7 +8,7 @@ final class AdminCourseRepository
 {
     public function paginate(array $filters)
     {
-        $q = Course::query()->with(['instructor', 'category'])->latest();
+        $q = Course::query()->with(['instructor', 'categories'])->latest();
         if (!empty($filters['q'])) {
             $q->where('title', 'like', '%' . $filters['q'] . '%');
         }
@@ -16,7 +16,8 @@ public function paginate(array $filters)
             $q->where('status', $filters['status']);
         }
         if (!empty($filters['category_id'])) {
-            $q->where('category_id', $filters['category_id']);
+            $categoryId = (int) $filters['category_id'];
+            $q->whereHas('categories', fn($c) => $c->where('categories.id', $categoryId));
         }
         if (!empty($filters['instructor_id'])) {
             $q->where('instructor_id', $filters['instructor_id']);
@@ -24,3 +25,4 @@ public function paginate(array $filters)
         return $q->paginate($filters['per_page'] ?? 15);
     }
 }
+
diff --git a/BE/app/Repositories/Catalog/BannerRepository.php b/BE/app/Repositories/Catalog/BannerRepository.php
index d56d7d8..e82ef22 100644
--- a/BE/app/Repositories/Catalog/BannerRepository.php
+++ b/BE/app/Repositories/Catalog/BannerRepository.php
@@ -8,7 +8,6 @@ public function getActiveHomeBanners()
     return \App\Models\Banner::query()
         ->where('status', 'active')
         ->where('position', 'home')
-        ->whereNull('deleted_at')
         ->where(function ($query) {
             $query->whereNull('start_at')
                 ->orWhere('start_at', '<=', now());
@@ -22,3 +21,4 @@ public function getActiveHomeBanners()
         ->get();
 }
 }
+
diff --git a/BE/app/Repositories/Catalog/CatalogCourseRepository.php b/BE/app/Repositories/Catalog/CatalogCourseRepository.php
index 53e8d45..689043a 100644
--- a/BE/app/Repositories/Catalog/CatalogCourseRepository.php
+++ b/BE/app/Repositories/Catalog/CatalogCourseRepository.php
@@ -35,8 +35,7 @@ public function search(array $filters)
 
             $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryIds) {
                 $categoryQuery->whereIn('categories.id', $categoryIds)
-                    ->where('categories.status', 'active')
-                    ->whereNull('categories.deleted_at');
+                    ->where('categories.status', 'active');
             });
         }
 
@@ -53,20 +52,18 @@ public function search(array $filters)
 
                 $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryIds) {
                     $categoryQuery->whereIn('categories.id', $categoryIds)
-                        ->where('categories.status', 'active')
-                        ->whereNull('categories.deleted_at');
+                        ->where('categories.status', 'active');
                 });
             } else {
                 $query->whereHas('categories', function (Builder $categoryQuery) use ($categorySlug) {
                     $categoryQuery->where('categories.slug', $categorySlug)
-                        ->where('categories.status', 'active')
-                        ->whereNull('categories.deleted_at');
+                        ->where('categories.status', 'active');
                 });
             }
         }
 
         if (! empty($filters['level'])) {
-            $query->where('courses.level', $filters['level']);
+            $query->where('courses.course_level', $filters['level']);
         }
 
         if (! empty($filters['instructor_id'])) {
@@ -190,13 +187,11 @@ public function suggestions(string $keyword, int $limit = 10): Collection
                 DB::raw("'course' as type"),
             ])
             ->where('courses.status', 'published')
-            ->whereNull('courses.deleted_at')
             ->whereExists(function ($query) {
                 $query->selectRaw('1')
                     ->from('users')
                     ->whereColumn('users.id', 'courses.instructor_id')
                     ->where('users.status', 'active')
-                    ->whereNull('users.deleted_at')
                     ->where(function ($userQuery) {
                         $userQuery->whereNull('users.locked')
                             ->orWhere('users.locked', 0);
@@ -214,10 +209,6 @@ public function suggestions(string $keyword, int $limit = 10): Collection
         /*
          * Gợi ý danh mục:
          * Chỉ lấy danh mục active và có ít nhất 1 khóa học public hợp lệ.
-         *
-         * Có 2 trường hợp:
-         * 1. Danh mục đó có khóa học trực tiếp.
-         * 2. Danh mục cha có danh mục con đang có khóa học.
          */
         $categories = DB::table('categories')
             ->select([
@@ -227,12 +218,7 @@ public function suggestions(string $keyword, int $limit = 10): Collection
                 DB::raw("'category' as type"),
             ])
             ->where('categories.status', 'active')
-            ->whereNull('categories.deleted_at')
             ->where(function ($categoryQuery) {
-                /*
-                 * Trường hợp 1:
-                 * Danh mục hiện tại có khóa học public trực tiếp.
-                 */
                 $categoryQuery->whereExists(function ($exists) {
                     $exists->selectRaw('1')
                         ->from('course_categories')
@@ -240,19 +226,12 @@ public function suggestions(string $keyword, int $limit = 10): Collection
                         ->join('users', 'users.id', '=', 'courses.instructor_id')
                         ->whereColumn('course_categories.category_id', 'categories.id')
                         ->where('courses.status', 'published')
-                        ->whereNull('courses.deleted_at')
                         ->where('users.status', 'active')
-                        ->whereNull('users.deleted_at')
                         ->where(function ($userQuery) {
                             $userQuery->whereNull('users.locked')
                                 ->orWhere('users.locked', 0);
                         });
                 })
-
-                /*
-                 * Trường hợp 2:
-                 * Danh mục cha không có khóa trực tiếp nhưng danh mục con có khóa học public.
-                 */
                 ->orWhereExists(function ($exists) {
                     $exists->selectRaw('1')
                         ->from('categories as child_categories')
@@ -261,11 +240,8 @@ public function suggestions(string $keyword, int $limit = 10): Collection
                         ->join('users', 'users.id', '=', 'courses.instructor_id')
                         ->whereColumn('child_categories.parent_id', 'categories.id')
                         ->where('child_categories.status', 'active')
-                        ->whereNull('child_categories.deleted_at')
                         ->where('courses.status', 'published')
-                        ->whereNull('courses.deleted_at')
                         ->where('users.status', 'active')
-                        ->whereNull('users.deleted_at')
                         ->where(function ($userQuery) {
                             $userQuery->whereNull('users.locked')
                                 ->orWhere('users.locked', 0);
@@ -295,34 +271,24 @@ private function publicCourseQuery()
                 'categories:id,parent_id,name,slug,description,sort_order',
             ])
             ->where('courses.status', 'published')
-            ->whereNull('courses.deleted_at')
-
-            /*
-             * Rule mới:
-             * Instructor bị khóa/inactive thì course không hiển thị public.
-             */
             ->whereHas('instructor', function (Builder $instructorQuery) {
                 $instructorQuery->where('users.status', 'active')
-                    ->whereNull('users.deleted_at')
                     ->where(function (Builder $lockedQuery) {
                         $lockedQuery->whereNull('users.locked')
                             ->orWhere('users.locked', 0);
                     });
             })
-
             ->select('courses.*')
             ->selectSub(function ($query) {
                 $query->from('orders')
                     ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                     ->whereColumn('orders.course_id', 'courses.id')
-                    ->whereNull('course_reviews.deleted_at')
                     ->selectRaw('COALESCE(AVG(course_reviews.rating), 0)');
             }, 'average_rating')
             ->selectSub(function ($query) {
                 $query->from('orders')
                     ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                     ->whereColumn('orders.course_id', 'courses.id')
-                    ->whereNull('course_reviews.deleted_at')
                     ->selectRaw('COUNT(course_reviews.id)');
             }, 'reviews_count')
             ->selectSub(function ($query) {
@@ -372,3 +338,4 @@ private function applySort(Builder $query, ?string $sort): void
         };
     }
 }
+
diff --git a/BE/app/Repositories/Catalog/CategoryRepository.php b/BE/app/Repositories/Catalog/CategoryRepository.php
index 187d57b..424fc80 100644
--- a/BE/app/Repositories/Catalog/CategoryRepository.php
+++ b/BE/app/Repositories/Catalog/CategoryRepository.php
@@ -10,10 +10,9 @@ public function getActiveForHome()
     {
         return Category::query()
             ->withCount(['courses' => function ($q) {
-                $q->where('courses.status', 'published')->whereNull('courses.deleted_at');
+                $q->where('courses.status', 'published');
             }])
             ->where('status', 'active')
-            ->whereNull('deleted_at')
             ->orderBy('sort_order')
             ->orderByDesc('id')
             ->limit(12)
@@ -24,7 +23,7 @@ public function getActiveForHome()
                     $allCategoryIds = array_merge([$cat->id], $childCategoryIds);
                     $totalCourses = \App\Models\Course::whereHas('categories', function ($q) use ($allCategoryIds) {
                         $q->whereIn('categories.id', $allCategoryIds);
-                    })->where('status', 'published')->whereNull('deleted_at')->distinct('courses.id')->count('courses.id');
+                    })->where('status', 'published')->distinct('courses.id')->count('courses.id');
                     $cat->courses_count = max($cat->courses_count ?? 0, $totalCourses);
                 }
                 return $cat;
@@ -35,12 +34,12 @@ public function paginateActive(int $perPage = 50)
     {
         return Category::query()
             ->withCount(['courses' => function ($q) {
-                $q->where('courses.status', 'published')->whereNull('courses.deleted_at');
+                $q->where('courses.status', 'published');
             }])
             ->where('status', 'active')
-            ->whereNull('deleted_at')
             ->orderBy('sort_order')
             ->orderByDesc('id')
             ->paginate($perPage);
     }
 }
+
diff --git a/BE/app/Repositories/Catalog/FeaturedInstructorRepository.php b/BE/app/Repositories/Catalog/FeaturedInstructorRepository.php
index 5a8cb3e..74da2e3 100644
--- a/BE/app/Repositories/Catalog/FeaturedInstructorRepository.php
+++ b/BE/app/Repositories/Catalog/FeaturedInstructorRepository.php
@@ -51,7 +51,6 @@ private function featuredQuery(): Builder
                     ->join('courses', 'courses.id', '=', 'orders.course_id')
                     ->whereColumn('courses.instructor_id', 'users.id')
                     ->where('courses.status', 'published')
-                    ->whereNull('course_reviews.deleted_at')
                     ->select(DB::raw('AVG(course_reviews.rating)'));
             }, 'average_rating')
             ->selectSub(function ($query) use ($timeframe) {
@@ -77,3 +76,4 @@ private function featuredQuery(): Builder
             ->orderByDesc('published_courses_count');
     }
 }
+
diff --git a/BE/app/Repositories/Instructor/InstructorCourseRepository.php b/BE/app/Repositories/Instructor/InstructorCourseRepository.php
index 657e03e..f05aa60 100644
--- a/BE/app/Repositories/Instructor/InstructorCourseRepository.php
+++ b/BE/app/Repositories/Instructor/InstructorCourseRepository.php
@@ -24,12 +24,15 @@ public function findWithCategories(int $courseId): Course
         return Course::query()
             ->with(['categories'])
             ->findOrFail($courseId);
-    }    public function findByIdWithReviewRelations(int $courseId): ?Course
+    }
+
+    public function findByIdWithReviewRelations(int $courseId): ?Course
     {
         return Course::query()
             ->with(['categories', 'sections.lessons'])
             ->find($courseId);
     }
+
     public function markAsPendingReview(Course $course): Course
     {
         $course->forceFill([
@@ -40,11 +43,11 @@ public function markAsPendingReview(Course $course): Course
             ?? $course->fresh(['categories', 'sections.lessons'])
             ?? $course;
     }
+
     public function findCourseForChecklist(int $courseId): ?object
     {
-        return \Illuminate\Support\Facades\DB::table('courses')
+        return DB::table('courses')
             ->where('id', $courseId)
-            ->whereNull('deleted_at')
             ->select([
                 'id',
                 'instructor_id',
@@ -64,15 +67,12 @@ public function findCourseForChecklist(int $courseId): ?object
 
     public function getChecklistCategories(int $courseId): \Illuminate\Support\Collection
     {
-        $table = \Illuminate\Support\Facades\Schema::hasTable('course_category') ? 'course_category' : 'course_categories';
-        return \Illuminate\Support\Facades\DB::table("{$table} as cc")
+        return DB::table('course_categories as cc')
             ->join('categories as c', 'c.id', '=', 'cc.category_id')
             ->where('cc.course_id', $courseId)
-            ->whereNull('c.deleted_at')
             ->where(function ($query): void {
                 $query->whereNull('c.status')
-                    ->orWhere('c.status', 'active')
-                    ->orWhere('c.status', 'published');
+                    ->orWhere('c.status', 'active');
             })
             ->select([
                 'c.id',
@@ -84,9 +84,8 @@ public function getChecklistCategories(int $courseId): \Illuminate\Support\Colle
 
     public function getChecklistSections(int $courseId): \Illuminate\Support\Collection
     {
-        return \Illuminate\Support\Facades\DB::table('course_sections')
+        return DB::table('course_sections')
             ->where('course_id', $courseId)
-            ->whereNull('deleted_at')
             ->select([
                 'id',
                 'course_id',
@@ -102,14 +101,9 @@ public function getChecklistSections(int $courseId): \Illuminate\Support\Collect
 
     public function getChecklistLessons(int $courseId): \Illuminate\Support\Collection
     {
-        return \Illuminate\Support\Facades\DB::table('lessons as l')
+        return DB::table('lessons as l')
             ->leftJoin('course_sections as cs', 'cs.id', '=', 'l.course_section_id')
             ->where('l.course_id', $courseId)
-            ->whereNull('l.deleted_at')
-            ->where(function ($query): void {
-                $query->whereNull('cs.deleted_at')
-                    ->orWhereNull('l.course_section_id');
-            })
             ->select([
                 'l.id',
                 'l.course_id',
@@ -131,81 +125,30 @@ public function getChecklistLessons(int $courseId): \Illuminate\Support\Collecti
 
     public function countChecklistLessonAssets(int $courseId): int
     {
-        return (int) \Illuminate\Support\Facades\DB::table('lesson_assets as la')
+        return (int) DB::table('lesson_assets as la')
             ->join('lessons as l', 'l.id', '=', 'la.lesson_id')
             ->where('l.course_id', $courseId)
-            ->whereNull('l.deleted_at')
-            ->whereNull('la.deleted_at')
             ->count();
     }
 
-    public function getChecklistQuizzes(int $courseId): \Illuminate\Support\Collection
-    {
-        return \Illuminate\Support\Facades\DB::table('quizzes')
-            ->where('course_id', $courseId)
-            ->whereNull('deleted_at')
-            ->select([
-                'id',
-                'course_id',
-                'lesson_id',
-                'title',
-                'description',
-                'passing_score',
-                'status',
-            ])
-            ->orderBy('id')
-            ->get();
-    }
 
-    public function getChecklistQuizQuestionStats(int $courseId): \Illuminate\Support\Collection
-    {
-        return \Illuminate\Support\Facades\DB::table('quiz_questions as qq')
-            ->join('quizzes as q', 'q.id', '=', 'qq.quiz_id')
-            ->leftJoin('quiz_options as qo', 'qo.question_id', '=', 'qq.id')
-            ->where('q.course_id', $courseId)
-            ->whereNull('q.deleted_at')
-            ->where('q.status', 'published')
-            ->select([
-                'qq.id',
-                'qq.quiz_id',
-                'qq.question_text',
-                'qq.question_type',
-                'qq.score',
-                'qq.sort_order',
-                \Illuminate\Support\Facades\DB::raw('COUNT(qo.id) as options_count'),
-                \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qo.is_correct = 1 THEN 1 ELSE 0 END) as correct_options_count'),
-            ])
-            ->groupBy([
-                'qq.id',
-                'qq.quiz_id',
-                'qq.question_text',
-                'qq.question_type',
-                'qq.score',
-                'qq.sort_order',
-            ])
-            ->orderBy('qq.quiz_id')
-            ->orderBy('qq.sort_order')
-            ->orderBy('qq.id')
-            ->get();
-    }
 
-public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
+    public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
     {
         $page = max((int) ($filters['page'] ?? 1), 1);
         $perPage = min(max((int) ($filters['per_page'] ?? 10), 1), 50);
 
         $query = DB::table('courses')
-            ->where('instructor_id', $instructorId)
-            ->whereNull('deleted_at');
+            ->where('instructor_id', $instructorId);
 
         if (!empty($filters['status']) && $filters['status'] !== 'all') {
             $statusInput = strtolower(trim((string) $filters['status']));
             if ($statusInput === 'published' || $statusInput === 'active') {
-                $query->whereIn('status', ['published', 'approved', 'active']);
+                $query->whereIn('status', ['published', 'approved']);
             } elseif ($statusInput === 'pending' || $statusInput === 'pending_review') {
-                $query->whereIn('status', ['pending', 'pending_review', 'submitted']);
+                $query->whereIn('status', ['pending_review']);
             } elseif ($statusInput === 'hidden') {
-                $query->whereIn('status', ['hidden', 'inactive']);
+                $query->whereIn('status', ['hidden']);
             } else {
                 $query->where('status', $statusInput);
             }
@@ -234,7 +177,7 @@ public function paginateCourses(int $instructorId, array $filters): LengthAwareP
         // 1. Enrollment count
         $enrollmentCounts = DB::table('enrollments')
             ->whereIn('course_id', $courseIds)
-            ->whereIn('status', ['active', 'completed', 'enrolled'])
+            ->whereIn('status', ['active', 'completed'])
             ->groupBy('course_id')
             ->select('course_id', DB::raw('COUNT(id) as count'))
             ->pluck('count', 'course_id')
@@ -252,41 +195,22 @@ public function paginateCourses(int $instructorId, array $filters): LengthAwareP
             ->all();
 
         // 3. Rating & Review Count
-        $hasCourseReviewsTable = Schema::hasTable('course_reviews');
-        $reviewsMap = collect();
-
-        if ($hasCourseReviewsTable) {
-            $reviewsMap = DB::table('course_reviews')
-                ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
-                ->whereIn('orders.course_id', $courseIds)
-                ->whereNull('course_reviews.deleted_at')
-                ->groupBy('orders.course_id')
-                ->select(
-                    'orders.course_id',
-                    DB::raw('COUNT(course_reviews.id) as count'),
-                    DB::raw('ROUND(AVG(course_reviews.rating), 1) as avg_rating')
-                )
-                ->get()
-                ->keyBy('course_id');
-        } elseif (Schema::hasTable('reviews')) {
-            $reviewsMap = DB::table('reviews')
-                ->whereIn('course_id', $courseIds)
-                ->whereNull('deleted_at')
-                ->groupBy('course_id')
-                ->select(
-                    'course_id',
-                    DB::raw('COUNT(id) as count'),
-                    DB::raw('ROUND(AVG(rating), 1) as avg_rating')
-                )
-                ->get()
-                ->keyBy('course_id');
-        }
+        $reviewsMap = DB::table('course_reviews')
+            ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
+            ->whereIn('orders.course_id', $courseIds)
+            ->groupBy('orders.course_id')
+            ->select(
+                'orders.course_id',
+                DB::raw('COUNT(course_reviews.id) as count'),
+                DB::raw('ROUND(AVG(course_reviews.rating), 1) as avg_rating')
+            )
+            ->get()
+            ->keyBy('course_id');
 
         // 4. Categories Map
         $categoriesMap = DB::table('course_categories as cc')
             ->join('categories as c', 'c.id', '=', 'cc.category_id')
             ->whereIn('cc.course_id', $courseIds)
-            ->whereNull('c.deleted_at')
             ->select('cc.course_id', 'c.id', 'c.name')
             ->get()
             ->groupBy('course_id');
@@ -317,23 +241,20 @@ public function paginateCourses(int $instructorId, array $filters): LengthAwareP
         return $paginator;
     }
 
-public function instructorOwnsCourse(int $instructorId, int $courseId): bool
+    public function instructorOwnsCourse(int $instructorId, int $courseId): bool
     {
         return DB::table('courses')
             ->where('id', $courseId)
             ->where('instructor_id', $instructorId)
-            ->whereNull('deleted_at')
             ->exists();
     }
 
-
     public function findOwnedCourseForDetail(int $courseId, int $instructorId): ?Course
     {
         $course = Course::query()
             ->with(['categories'])
             ->where('id', $courseId)
             ->where('instructor_id', $instructorId)
-            ->whereNull('deleted_at')
             ->first();
 
         if (!$course) {
@@ -342,24 +263,19 @@ public function findOwnedCourseForDetail(int $courseId, int $instructorId): ?Cou
 
         $course->setAttribute('section_count', (int) DB::table('course_sections')
             ->where('course_id', $courseId)
-            ->whereNull('deleted_at')
             ->count());
 
         $course->setAttribute('lesson_count', (int) DB::table('lessons')
             ->where('course_id', $courseId)
-            ->whereNull('deleted_at')
             ->count());
 
         $course->setAttribute('asset_count', (int) DB::table('lesson_assets as la')
             ->join('lessons as l', 'l.id', '=', 'la.lesson_id')
             ->where('l.course_id', $courseId)
-            ->whereNull('l.deleted_at')
-            ->whereNull('la.deleted_at')
             ->count());
 
         $course->setAttribute('preview_lesson_count', (int) DB::table('lessons')
             ->where('course_id', $courseId)
-            ->whereNull('deleted_at')
             ->where('is_preview', true)
             ->count());
 
@@ -379,23 +295,19 @@ public function findOwnedCourseForContent(int $courseId, int $instructorId): ?Co
         return Course::query()
             ->with([
                 'sections' => function ($query): void {
-                    $query->whereNull('deleted_at')
-                        ->orderBy('sort_order')
+                    $query->orderBy('sort_order')
                         ->orderBy('id');
                 },
                 'sections.lessons' => function ($query): void {
-                    $query->whereNull('deleted_at')
-                        ->orderBy('sort_order')
+                    $query->orderBy('sort_order')
                         ->orderBy('id');
                 },
                 'sections.lessons.assets' => function ($query): void {
-                    $query->whereNull('deleted_at')
-                        ->orderBy('id');
+                    $query->orderBy('id');
                 },
             ])
             ->where('id', $courseId)
             ->where('instructor_id', $instructorId)
-            ->whereNull('deleted_at')
             ->first();
     }
 
@@ -410,5 +322,4 @@ public function updateCourseWithCategories(Course $course, array $data, ?array $
 
         return $course->refresh()->load('categories');
     }
-
 }
diff --git a/BE/app/Repositories/Instructor/InstructorLearnerRepository.php b/BE/app/Repositories/Instructor/InstructorLearnerRepository.php
index 4552a7d..1d6e72e 100644
--- a/BE/app/Repositories/Instructor/InstructorLearnerRepository.php
+++ b/BE/app/Repositories/Instructor/InstructorLearnerRepository.php
@@ -17,7 +17,7 @@ public function paginateLearners(int $instructorId, array $filters): LengthAware
             ->join('courses', 'courses.id', '=', 'enrollments.course_id')
             ->join('users', 'users.id', '=', 'enrollments.user_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereIn('enrollments.status', ['active', 'completed'])
             ->select([
                 'enrollments.id as enrollment_id',
@@ -183,7 +183,7 @@ public function getLearnersSummary(int $instructorId, array $filters = []): arra
         $baseQuery = DB::table('enrollments')
             ->join('courses', 'courses.id', '=', 'enrollments.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereIn('enrollments.status', ['active', 'completed']);
 
         if (!empty($filters['course_id']) && $filters['course_id'] !== 'all') {
@@ -281,7 +281,7 @@ public function getLearnersChart(int $instructorId, array $filters = []): array
             $records = DB::table('enrollments')
                 ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                 ->where('courses.instructor_id', $instructorId)
-                ->whereNull('courses.deleted_at')
+                
                 ->whereIn('enrollments.status', ['active', 'completed'])
                 ->whereBetween('enrollments.enrolled_at', [$startDate, $endDate])
                 ->selectRaw("DATE_FORMAT(enrollments.enrolled_at, '%Y-%m') as date, COUNT(*) as total_count, SUM(CASE WHEN enrollments.status = 'active' AND enrollments.progress_percent < 100 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN enrollments.status = 'completed' OR enrollments.progress_percent >= 100 THEN 1 ELSE 0 END) as completed_count")
@@ -307,7 +307,7 @@ public function getLearnersChart(int $instructorId, array $filters = []): array
             $records = DB::table('enrollments')
                 ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                 ->where('courses.instructor_id', $instructorId)
-                ->whereNull('courses.deleted_at')
+                
                 ->whereIn('enrollments.status', ['active', 'completed'])
                 ->whereBetween('enrollments.enrolled_at', [$startDate, $endDate])
                 ->selectRaw("DATE(enrollments.enrolled_at) as date, COUNT(*) as total_count, SUM(CASE WHEN enrollments.status = 'active' AND enrollments.progress_percent < 100 THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN enrollments.status = 'completed' OR enrollments.progress_percent >= 100 THEN 1 ELSE 0 END) as completed_count")
@@ -351,7 +351,7 @@ public function getLearnerDetails(int $instructorId, int $enrollmentId): array
             ->join('users', 'users.id', '=', 'enrollments.user_id')
             ->where('enrollments.id', $enrollmentId)
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->select([
                 'enrollments.id as enrollment_id',
                 'users.id as user_id',
@@ -614,7 +614,7 @@ public function exportLearners(int $instructorId, array $filters = []): \Illumin
             ->join('courses', 'courses.id', '=', 'enrollments.course_id')
             ->join('users', 'users.id', '=', 'enrollments.user_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereIn('enrollments.status', ['active', 'completed'])
             ->select([
                 'enrollments.id as enrollment_id',
diff --git a/BE/app/Repositories/Instructor/InstructorLessonRepository.php b/BE/app/Repositories/Instructor/InstructorLessonRepository.php
index f7e95a6..4d57c8d 100644
--- a/BE/app/Repositories/Instructor/InstructorLessonRepository.php
+++ b/BE/app/Repositories/Instructor/InstructorLessonRepository.php
@@ -28,10 +28,7 @@ public function paginateOwnedLessons(User $instructor, array $filters): LengthAw
         }
         if (!empty($filters['search'])) {
             $search = trim((string) $filters['search']);
-            $query->where(function ($query) use ($search): void {
-                $query->where('title', 'like', '%' . $search . '%')
-                    ->orWhere('slug', 'like', '%' . $search . '%');
-            });
+            $query->where('title', 'like', '%' . $search . '%');
         }
         $sortBy = $filters['sort_by'] ?? 'sort_order';
         $sortDirection = $filters['sort_direction'] ?? 'asc';
@@ -90,14 +87,5 @@ public function getNextSortOrder(int $sectionId): int
             ->max('sort_order');
         return ((int) $maxSortOrder) + 1;
     }
-    public function slugExistsInCourse(int $courseId, string $slug, ?int $ignoreLessonId = null): bool
-    {
-        return Lesson::query()
-            ->where('course_id', $courseId)
-            ->where('slug', $slug)
-            ->when($ignoreLessonId !== null, function ($query) use ($ignoreLessonId): void {
-                $query->whereKeyNot($ignoreLessonId);
-            })
-            ->exists();
-    }
 }
+
diff --git a/BE/app/Repositories/Instructor/InstructorRevenueRepository.php b/BE/app/Repositories/Instructor/InstructorRevenueRepository.php
index 6541c74..ffb63b3 100644
--- a/BE/app/Repositories/Instructor/InstructorRevenueRepository.php
+++ b/BE/app/Repositories/Instructor/InstructorRevenueRepository.php
@@ -116,7 +116,7 @@ private function baseRevenueQuery(int $instructorId, array $filters): Builder
             ->join('courses', 'courses.id', '=', 'revenues.course_id')
             ->leftJoin('orders', 'orders.id', '=', 'revenues.order_id')
             ->where('revenues.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at');
+            ;
 
         if (!empty($filters['course_id'])) {
             $query->where('revenues.course_id', (int) $filters['course_id']);
diff --git a/BE/app/Repositories/Interaction/InstructorQuestionRepository.php b/BE/app/Repositories/Interaction/InstructorQuestionRepository.php
index 40df4ff..1c2c87f 100644
--- a/BE/app/Repositories/Interaction/InstructorQuestionRepository.php
+++ b/BE/app/Repositories/Interaction/InstructorQuestionRepository.php
@@ -26,7 +26,7 @@ public function paginateQuestions(int $instructorId, array $filters): LengthAwar
                     ->where('instructor_replies.user_id', '=', $instructorId);
             })
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereNull('q.parent_id')
             ->where('q.status', 'visible')
             ->where('learner.role', 'learner')
diff --git a/BE/app/Repositories/Moderation/CourseModerationRepository.php b/BE/app/Repositories/Moderation/CourseModerationRepository.php
index 4b94c03..555f18e 100644
--- a/BE/app/Repositories/Moderation/CourseModerationRepository.php
+++ b/BE/app/Repositories/Moderation/CourseModerationRepository.php
@@ -22,8 +22,7 @@ public function paginateCourseReviews(array $filters): LengthAwarePaginator
                     ]);
                 },
                 'categories'
-            ])
-            ->with('categories');
+            ]);
 
         $status = $filters['status'] ?? null;
         if ($status === 'pending') {
diff --git a/BE/app/Repositories/Report/InstructorDashboardAlertRepository.php b/BE/app/Repositories/Report/InstructorDashboardAlertRepository.php
index c4670d6..67b2a7f 100644
--- a/BE/app/Repositories/Report/InstructorDashboardAlertRepository.php
+++ b/BE/app/Repositories/Report/InstructorDashboardAlertRepository.php
@@ -51,7 +51,7 @@ private function fallbackAlerts(int $instructorId): array
             ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
             ->join('courses', 'courses.id', '=', 'lessons.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereNull('q.parent_id')
             ->where('q.status', 'visible')
             ->where('learner.role', 'learner')
diff --git a/BE/app/Repositories/Report/InstructorDashboardRepository.php b/BE/app/Repositories/Report/InstructorDashboardRepository.php
index 336d4cf..ef026d6 100644
--- a/BE/app/Repositories/Report/InstructorDashboardRepository.php
+++ b/BE/app/Repositories/Report/InstructorDashboardRepository.php
@@ -70,7 +70,7 @@ private function enrollmentSummary(int $instructorId, Carbon $startDate, Carbon
         $query = DB::table('enrollments')
             ->join('courses', 'courses.id', '=', 'enrollments.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereIn('enrollments.status', ['active', 'completed']);
 
         $totalEnrollments = (int) (clone $query)->count();
@@ -225,7 +225,7 @@ private function viewsSummary(int $instructorId, Carbon $startDate, Carbon $endD
         $query = DB::table('course_views')
             ->join('courses', 'courses.id', '=', 'course_views.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at');
+            ;
 
         $viewsThisPeriod = (int) (clone $query)
             ->whereBetween('course_views.viewed_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
@@ -257,7 +257,7 @@ private function countUnansweredQuestions(int $instructorId): int
             ->join('lessons', 'lessons.id', '=', 'q.lesson_id')
             ->join('courses', 'courses.id', '=', 'lessons.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereNull('q.parent_id')
             ->where('q.status', 'visible')
             ->where('learner.role', 'learner')
diff --git a/BE/app/Repositories/Report/InstructorEnrollmentChartRepository.php b/BE/app/Repositories/Report/InstructorEnrollmentChartRepository.php
index 1090671..dce4506 100644
--- a/BE/app/Repositories/Report/InstructorEnrollmentChartRepository.php
+++ b/BE/app/Repositories/Report/InstructorEnrollmentChartRepository.php
@@ -16,7 +16,7 @@ public function getChart(int $instructorId, array $filters): array
         $query = DB::table('enrollments')
             ->join('courses', 'courses.id', '=', 'enrollments.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
+            
             ->whereBetween('enrollments.enrolled_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
             ->selectRaw("
                 DATE_FORMAT(enrollments.enrolled_at, '$format') as period,
diff --git a/BE/app/Repositories/Wishlist/WishlistRepository.php b/BE/app/Repositories/Wishlist/WishlistRepository.php
index d5fe4de..0029dcf 100644
--- a/BE/app/Repositories/Wishlist/WishlistRepository.php
+++ b/BE/app/Repositories/Wishlist/WishlistRepository.php
@@ -18,20 +18,16 @@ public function paginatePublishedCoursesByUser(int $userId, int $perPage = 10):
                             'thumbnail_url',
                             'price',
                             'sale_price',
-                            'level',
                             'language',
                             'status',
-                            'deleted_at',
                         ])
-                        ->where('status', 'published')
-                        ->whereNull('deleted_at');
+                        ->where('status', 'published');
                 },
             ])
             ->where('user_id', $userId)
             ->whereHas('course', function ($query): void {
                 $query
-                    ->where('status', 'published')
-                    ->whereNull('deleted_at');
+                    ->where('status', 'published');
             })
             ->orderByDesc('created_at')
             ->orderByDesc('id')
@@ -41,7 +37,6 @@ public function findCourse(int $courseId): ?Course
     {
         return Course::query()
             ->whereKey($courseId)
-            ->whereNull('deleted_at')
             ->first();
     }
     public function findPublishedCourse(int $courseId): ?Course
@@ -49,7 +44,6 @@ public function findPublishedCourse(int $courseId): ?Course
         return Course::query()
             ->whereKey($courseId)
             ->where('status', 'published')
-            ->whereNull('deleted_at')
             ->first();
     }
     public function exists(int $userId, int $courseId): bool
diff --git a/BE/app/Services/Admin/AdminCategoryService.php b/BE/app/Services/Admin/AdminCategoryService.php
index 161de37..9c1e657 100644
--- a/BE/app/Services/Admin/AdminCategoryService.php
+++ b/BE/app/Services/Admin/AdminCategoryService.php
@@ -78,38 +78,6 @@ public function delete(int $id): void
         });
     }
 
-    public function restore(int $id): Category
-    {
-        return DB::transaction(function () use ($id): Category {
-            $category = $this->categoryRepository->findOnlyTrashed($id);
-
-            if (!$category) {
-                throw new BusinessException('Không tìm thấy danh mục đã xóa.', 404);
-            }
-
-            $slugConflict = Category::withTrashed()
-                ->where('slug', $category->slug)
-                ->where('id', '<>', $category->id)
-                ->exists();
-
-            if ($slugConflict) {
-                throw new BusinessException('Không thể khôi phục vì slug đã được sử dụng.', 409);
-            }
-
-            if ($category->parent_id !== null) {
-                $parent = $this->categoryRepository->find((int) $category->parent_id);
-                if (!$parent || $parent->parent_id !== null) {
-                    throw new BusinessException('Không thể khôi phục vì danh mục cha không còn hợp lệ.', 409);
-                }
-            }
-
-            $category->restore();
-
-            return $category->refresh()
-                ->load(['parent', 'children'])
-                ->loadCount('courses');
-        });
-    }
 
     public function reorder(array $items): void
     {
@@ -137,7 +105,7 @@ public function reorder(array $items): void
             foreach ($items as $item) {
                 Category::query()->whereKey((int) $item['id'])->update([
                     'parent_id' => $item['parent_id'],
-                    'sort_order' => (string) $item['sort_order'],
+                    'sort_order' => (int) $item['sort_order'],
                     'updated_at' => now(),
                 ]);
             }
diff --git a/BE/app/Services/Admin/AdminCourseService.php b/BE/app/Services/Admin/AdminCourseService.php
index dcfe51a..0dbbb64 100644
--- a/BE/app/Services/Admin/AdminCourseService.php
+++ b/BE/app/Services/Admin/AdminCourseService.php
@@ -15,25 +15,25 @@ public function paginate(array $filters)
     }
     public function show(Course $course): Course
     {
-        return $course->load(['instructor', 'category', 'sections.lessons']);
+        return $course->load(['instructor', 'categories', 'sections.lessons']);
     }
     public function approve(Course $course, User $admin): Course
     {
-        $course->update(['status' => 'published', 'approved_at' => now(), 'approved_by' => $admin->id]);
+        $course->update(['status' => 'published', 'published_at' => now(), 'reviewed_by' => $admin->id]);
         $this->notifications->audit($admin, 'course.approve', $course);
-        return $course->fresh(['instructor', 'category']);
+        return $course->fresh(['instructor', 'categories']);
     }
     public function reject(Course $course, ?string $reason, User $admin): Course
     {
-        $course->update(['status' => 'rejected', 'rejected_reason' => $reason]);
+        $course->update(['status' => 'rejected', 'admin_reject_reason' => $reason, 'reviewed_by' => $admin->id]);
         $this->notifications->audit($admin, 'course.reject', $course, [], ['reason' => $reason]);
-        return $course->fresh(['instructor', 'category']);
+        return $course->fresh(['instructor', 'categories']);
     }
     public function hide(Course $course, ?string $reason, User $admin): Course
     {
-        $course->update(['status' => 'hidden', 'hidden_reason' => $reason]);
+        $course->update(['status' => 'hidden']);
         $this->notifications->audit($admin, 'course.hide', $course, [], ['reason' => $reason]);
-        return $course->fresh(['instructor', 'category']);
+        return $course->fresh(['instructor', 'categories']);
     }
     public function publish(Course $course, User $admin): Course
     {
@@ -41,7 +41,8 @@ public function publish(Course $course, User $admin): Course
     }
     public function bulkApprove(array $ids, User $admin): array
     {
-        $count = Course::query()->whereIn('id', $ids)->update(['status' => 'published', 'approved_by' => $admin->id, 'approved_at' => now()]);
+        $count = Course::query()->whereIn('id', $ids)->update(['status' => 'published', 'reviewed_by' => $admin->id, 'published_at' => now()]);
         return ['updated' => $count];
     }
 }
+
diff --git a/BE/app/Services/Admin/AdminPayoutAccountService.php b/BE/app/Services/Admin/AdminPayoutAccountService.php
index 77770a1..8b29518 100644
--- a/BE/app/Services/Admin/AdminPayoutAccountService.php
+++ b/BE/app/Services/Admin/AdminPayoutAccountService.php
@@ -27,7 +27,7 @@ public function approve(PayoutAccount $account, User $admin): PayoutAccount
     }
     public function reject(PayoutAccount $account, ?string $reason, User $admin): PayoutAccount
     {
-        $account->update(['status' => 'rejected', 'rejected_reason' => $reason]);
+        $account->update(['status' => 'rejected', 'rejection_reason' => $reason]);
         $this->notifications->audit($admin, 'payout_account.reject', $account, [], ['reason' => $reason]);
         return $account->fresh('user');
     }
diff --git a/BE/app/Services/Admin/AdminService.php b/BE/app/Services/Admin/AdminService.php
index 47b808a..aff12bc 100644
--- a/BE/app/Services/Admin/AdminService.php
+++ b/BE/app/Services/Admin/AdminService.php
@@ -275,7 +275,6 @@ public function getCourses(array $queryParams): LengthAwarePaginator
                 'sections',
                 'lessons',
                 'reviews as review_count',
-                'comments as comment_count' => fn($q) => $q->where('comments.status', 'visible'),
                 'orders as paid_order_count' => fn($q) => $q->where('orders.status', 'paid'),
             ])
             ->withSum([
@@ -307,7 +306,7 @@ public function getCourses(array $queryParams): LengthAwarePaginator
         }
 
         if (!empty($queryParams['level'])) {
-            $query->where('level', $queryParams['level']);
+            $query->where('course_level', $queryParams['level']);
         }
 
         $sortBy = $queryParams['sort_by'] ?? 'created_at';
@@ -423,12 +422,16 @@ public function updateCourse(int $id, array $data): \App\Models\Course
                 'intro_video_url',
                 'price',
                 'sale_price',
-                'level',
+                'course_level',
                 'language',
                 'requirements',
                 'outcomes'
             ];
 
+            if (isset($data['level']) && !isset($data['course_level'])) {
+                $data['course_level'] = $data['level'];
+            }
+
             $updateData = [];
             foreach ($allowedFields as $field) {
                 if (array_key_exists($field, $data)) {
@@ -474,7 +477,7 @@ public function updateCourse(int $id, array $data): \App\Models\Course
     public function getUsers(array $queryParams): LengthAwarePaginator
     {
         $perPage = min((int) ($queryParams['per_page'] ?? 15), 100);
-        $query = \App\Models\User::query()->whereNull('deleted_at');
+        $query = \App\Models\User::query();
 
         if (!empty($queryParams['search'])) {
             $search = trim((string) $queryParams['search']);
@@ -504,7 +507,7 @@ public function getUsers(array $queryParams): LengthAwarePaginator
 
     public function getUsersReport(array $queryParams): array
     {
-        $baseQuery = \App\Models\User::query()->whereNull('deleted_at');
+        $baseQuery = \App\Models\User::query();
 
         $totalUsers = (clone $baseQuery)->count();
         $totalLearners = (clone $baseQuery)->where('role', 'learner')->count();
@@ -544,7 +547,7 @@ public function getUsersReport(array $queryParams): array
             'new_users_in_period' => $newUsersInPeriod,
         ];
 
-        $query = \App\Models\User::query()->whereNull('deleted_at');
+        $query = \App\Models\User::query();
 
         if (!empty($queryParams['search'])) {
             $search = trim((string) $queryParams['search']);
@@ -630,7 +633,7 @@ public function getUsersReport(array $queryParams): array
 
     public function getUser(int $id): \App\Models\User
     {
-        $user = \App\Models\User::where('id', $id)->whereNull('deleted_at')->first();
+        $user = \App\Models\User::where('id', $id)->first();
 
         if (!$user) {
             throw new BusinessException('Không tìm thấy dữ liệu.', 404);
diff --git a/BE/app/Services/AdminService.php b/BE/app/Services/AdminService.php
deleted file mode 100644
index 9e02b3a..0000000
--- a/BE/app/Services/AdminService.php
+++ /dev/null
@@ -1,57 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Exceptions\BusinessException;
-use App\Models\Banner;
-use Illuminate\Contracts\Pagination\LengthAwarePaginator;
-
-class AdminService
-{
-    public function getBanners(array $queryParams): LengthAwarePaginator
-    {
-        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
-        return Banner::orderBy('sort_order')
-            ->orderByDesc('id')
-            ->paginate($perPage);
-    }
-
-    public function getBanner(int $id): Banner
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        return $banner;
-    }
-
-    public function createBanner(array $data): Banner
-    {
-        return Banner::create($data);
-    }
-
-    public function updateBanner(int $id, array $data): Banner
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $banner->update($data);
-        return $banner;
-    }
-
-    public function deleteBanner(int $id): void
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $banner->delete();
-    }
-}
diff --git a/BE/app/Services/Catalog/CatalogService.php b/BE/app/Services/Catalog/CatalogService.php
index 00d8402..f64b2cb 100644
--- a/BE/app/Services/Catalog/CatalogService.php
+++ b/BE/app/Services/Catalog/CatalogService.php
@@ -24,7 +24,6 @@ public function home(array $filters): array
 
         $faqs = \App\Models\Faq::query()
             ->where('status', 'active')
-            ->whereNull('deleted_at')
             ->orderBy('sort_order')
             ->orderByDesc('id')
             ->limit(6)
@@ -35,7 +34,6 @@ public function home(array $filters): array
             ->where('rating', '>=', 4)
             ->whereNotNull('comment')
             ->where('comment', '!=', '')
-            ->whereNull('deleted_at')
             ->orderByDesc('rating')
             ->orderByDesc('id')
             ->limit(6)
@@ -43,7 +41,6 @@ public function home(array $filters): array
 
         $vouchers = \App\Models\Coupon::query()
             ->where('status', 'active')
-            ->whereNull('deleted_at')
             ->where(function ($q) use ($now) {
                 $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
             })
@@ -55,10 +52,10 @@ public function home(array $filters): array
             ->get();
 
         $stats = [
-            'total_courses' => \App\Models\Course::query()->where('status', 'published')->whereNull('deleted_at')->count(),
-            'total_students' => \App\Models\User::query()->where('role', 'learner')->whereNull('deleted_at')->count(),
-            'total_instructors' => \App\Models\User::query()->where('role', 'instructor')->whereNull('deleted_at')->count(),
-            'total_reviews' => \App\Models\CourseReview::query()->whereNull('deleted_at')->count(),
+            'total_courses' => \App\Models\Course::query()->where('status', 'published')->count(),
+            'total_students' => \App\Models\User::query()->where('role', 'learner')->count(),
+            'total_instructors' => \App\Models\User::query()->where('role', 'instructor')->count(),
+            'total_reviews' => \App\Models\CourseReview::query()->count(),
         ];
 
         return [
@@ -130,3 +127,4 @@ public function suggestions(array $filters): Collection
         return $this->courseRepository->suggestions($keyword, $limit);
     }
 }
+
diff --git a/BE/app/Services/Course/CourseAvailabilityService.php b/BE/app/Services/Course/CourseAvailabilityService.php
index 49cab32..0b48c9c 100644
--- a/BE/app/Services/Course/CourseAvailabilityService.php
+++ b/BE/app/Services/Course/CourseAvailabilityService.php
@@ -11,7 +11,6 @@ public function assertCourseIsPurchasable(int $courseId): object
     {
         $course = DB::table('courses')
             ->where('id', $courseId)
-            ->whereNull('deleted_at')
             ->first();
 
         if (! $course) {
@@ -24,7 +23,6 @@ public function assertCourseIsPurchasable(int $courseId): object
 
         $instructor = DB::table('users')
             ->where('id', $course->instructor_id)
-            ->whereNull('deleted_at')
             ->first();
 
         if (! $instructor) {
@@ -46,7 +44,6 @@ public function instructorIsActiveAndUnlocked(int $instructorId): bool
     {
         $instructor = DB::table('users')
             ->where('id', $instructorId)
-            ->whereNull('deleted_at')
             ->first();
 
         return $instructor
@@ -54,3 +51,4 @@ public function instructorIsActiveAndUnlocked(int $instructorId): bool
             && (int) ($instructor->locked ?? 0) === 0;
     }
 }
+
diff --git a/BE/app/Services/Course/CoursePublicService.php b/BE/app/Services/Course/CoursePublicService.php
index 9563c2e..66405e1 100644
--- a/BE/app/Services/Course/CoursePublicService.php
+++ b/BE/app/Services/Course/CoursePublicService.php
@@ -38,8 +38,6 @@ public function show(string $slug): array
         // 2. Resolve optional authenticated user from Bearer token
         $user = $this->resolveOptionalUser();
 
-        // 2.1 Record course view asynchronously / safely
-        app(\App\Services\Course\CourseViewService::class)->recordView($course, $user, request());
 
         // 3. Eager load relationships with status and ordering constraints
         $course->load([
@@ -243,7 +241,6 @@ public function showInstructor(int $id): array
                     ->join('courses', 'courses.id', '=', 'orders.course_id')
                     ->whereColumn('courses.instructor_id', 'users.id')
                     ->where('courses.status', 'published')
-                    ->whereNull('course_reviews.deleted_at')
                     ->select(\Illuminate\Support\Facades\DB::raw('AVG(course_reviews.rating)'));
             }, 'average_rating')
             ->first();
diff --git a/BE/app/Services/Course/CourseViewService.php b/BE/app/Services/Course/CourseViewService.php
deleted file mode 100644
index 7acdd18..0000000
--- a/BE/app/Services/Course/CourseViewService.php
+++ /dev/null
@@ -1,121 +0,0 @@
-<?php
-
-namespace App\Services\Course;
-
-use App\Models\Course;
-use App\Models\CourseView;
-use App\Models\User;
-use Illuminate\Http\Request;
-use Illuminate\Support\Carbon;
-use Illuminate\Support\Facades\Log;
-
-class CourseViewService
-{
-    /**
-     * Anti-duplicate window in minutes.
-     */
-    public const DUPLICATE_WINDOW_MINUTES = 30;
-
-    /**
-     * Record a course view if anti-duplicate rules pass.
-     */
-    public function recordView(Course $course, ?User $user = null, ?Request $request = null): bool
-    {
-        try {
-            $request = $request ?? request();
-
-            // 1. Exclude bot / crawler requests
-            if ($this->isBot($request)) {
-                return false;
-            }
-
-            // 2. Exclude instructor viewing their own course
-            if ($user && (int) $user->id === (int) $course->instructor_id) {
-                return false;
-            }
-
-            $ip = $request->ip();
-            $userAgent = $request->header('User-Agent');
-            $sessionId = $request->hasSession() ? $request->session()->getId() : $request->header('X-Session-ID');
-
-            $ipHash = $ip ? hash('sha256', $ip) : null;
-            $uaHash = $userAgent ? hash('sha256', $userAgent) : null;
-
-            // 3. Anti-duplicate check (last 30 minutes)
-            $since = Carbon::now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES);
-
-            $query = CourseView::where('course_id', $course->id)
-                ->where('viewed_at', '>=', $since);
-
-            if ($user) {
-                $query->where(function ($q) use ($user, $sessionId, $ipHash) {
-                    $q->where('user_id', $user->id);
-                    if ($sessionId) {
-                        $q->orWhere('session_id', $sessionId);
-                    }
-                    if ($ipHash) {
-                        $q->orWhere('ip_hash', $ipHash);
-                    }
-                });
-            } elseif ($sessionId) {
-                $query->where(function ($q) use ($sessionId, $ipHash) {
-                    $q->where('session_id', $sessionId);
-                    if ($ipHash) {
-                        $q->orWhere('ip_hash', $ipHash);
-                    }
-                });
-            } else {
-                $query->where('ip_hash', $ipHash)
-                    ->where('user_agent_hash', $uaHash);
-            }
-
-            if ($query->exists()) {
-                return false; // Duplicate view within window
-            }
-
-            // 4. Create view record
-            CourseView::create([
-                'course_id' => $course->id,
-                'user_id' => $user?->id,
-                'session_id' => $sessionId,
-                'ip_hash' => $ipHash,
-                'user_agent_hash' => $uaHash,
-                'viewed_at' => Carbon::now(),
-            ]);
-
-            return true;
-        } catch (\Throwable $e) {
-            // View recording should never crash the main detail response
-            Log::error('Failed to log course view: ' . $e->getMessage(), [
-                'course_id' => $course->id,
-                'exception' => $e,
-            ]);
-            return false;
-        }
-    }
-
-    /**
-     * Determine if the request comes from a bot or crawler.
-     */
-    private function isBot(Request $request): bool
-    {
-        $userAgent = strtolower((string) $request->header('User-Agent'));
-
-        if ($userAgent === '') {
-            return false;
-        }
-
-        $botKeywords = [
-            'bot', 'crawler', 'spider', 'slurp', 'bingbot', 'googlebot',
-            'curl', 'wget', 'python-requests', 'postman', 'uptime', 'healthcheck'
-        ];
-
-        foreach ($botKeywords as $keyword) {
-            if (str_contains($userAgent, $keyword)) {
-                return true;
-            }
-        }
-
-        return false;
-    }
-}
diff --git a/BE/app/Services/Course/RelatedCourseService.php b/BE/app/Services/Course/RelatedCourseService.php
index 11bbf2f..9797ec0 100644
--- a/BE/app/Services/Course/RelatedCourseService.php
+++ b/BE/app/Services/Course/RelatedCourseService.php
@@ -71,7 +71,7 @@ private function scoreCandidates(Course $currentCourse, Collection $candidates):
             }
 
             // 2. Same level (+40)
-            if ($currentCourse->level && $candidate->level === $currentCourse->level) {
+            if ($currentCourse->course_level && $candidate->course_level === $currentCourse->course_level) {
                 $score += 40;
                 $reasons[] = 'Cùng cấp độ';
             }
diff --git a/BE/app/Services/CoursePublicService.php b/BE/app/Services/CoursePublicService.php
deleted file mode 100644
index 996100d..0000000
--- a/BE/app/Services/CoursePublicService.php
+++ /dev/null
@@ -1,343 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Models\Course;
-use App\Models\Enrollment;
-use App\Models\Wishlist;
-use App\Repositories\SessionRepository;
-use App\Repositories\UserRepository;
-use App\Services\AccessTokenService;
-use Illuminate\Database\Eloquent\ModelNotFoundException;
-
-class CoursePublicService
-{
-    public function __construct(
-        private readonly AccessTokenService $accessTokenService,
-        private readonly SessionRepository $sessionRepository,
-        private readonly UserRepository $userRepository
-    ) {
-    }
-
-    public function show(string $slug): array
-    {
-        // 1. Fetch the course
-        $course = Course::where('slug', $slug)
-            ->where('status', 'published')
-            ->first();
-
-        if (!$course) {
-            throw new ModelNotFoundException("Không tìm thấy dữ liệu.");
-        }
-
-        // 2. Resolve optional authenticated user from Bearer token
-        $user = $this->resolveOptionalUser();
-
-        // 3. Eager load relationships with status and ordering constraints
-        $course->load([
-            'instructor.instructorProfile',
-            'sections' => function ($query) {
-                $query->where('status', 'published')->orderBy('sort_order');
-            },
-            'sections.lessons' => function ($query) {
-                $query->where('status', 'published')->orderBy('sort_order');
-            },
-            'reviews' => function ($query) {
-                $query->orderBy('created_at', 'desc');
-            },
-            'reviews.order.user',
-            'faqs' => function ($query) {
-                $query->where('status', 'active')->orderBy('course_faqs.sort_order');
-            }
-        ]);
-
-        // 4. Calculate personalized details
-        $isEnrolled = false;
-        $enrollmentStatus = null;
-        $isInWishlist = false;
-        $hasAccess = false;
-
-        if ($user) {
-            // Check enrollment
-            $enrollment = Enrollment::where('user_id', $user->id)
-                ->where('course_id', $course->id)
-                ->first();
-
-            if ($enrollment) {
-                $isEnrolled = true;
-                $enrollmentStatus = $enrollment->status;
-                // If enrollment is active or completed, user has full access to content
-                if (in_array($enrollment->status, ['active', 'completed'])) {
-                    $hasAccess = true;
-                }
-            }
-
-            // Check wishlist
-            $isInWishlist = Wishlist::where('user_id', $user->id)
-                ->where('course_id', $course->id)
-                ->exists();
-
-            // Check if current user is the instructor of this course
-            if ((int) $course->instructor_id === (int) $user->id) {
-                $hasAccess = true;
-            }
-        }
-
-        return [
-            'course' => $course,
-            'is_enrolled' => $isEnrolled,
-            'enrollment_status' => $enrollmentStatus,
-            'is_in_wishlist' => $isInWishlist,
-            'has_access' => $hasAccess,
-        ];
-    }
-
-    public function outline(int $id): array
-    {
-        // 1. Fetch the course by ID
-        $course = Course::where('id', $id)
-            ->where('status', 'published')
-            ->first();
-
-        if (!$course) {
-            throw new ModelNotFoundException("Không tìm thấy dữ liệu.");
-        }
-
-        // 2. Resolve optional authenticated user from Bearer token
-        $user = $this->resolveOptionalUser();
-
-        // 3. Eager load sections and lessons with status and ordering constraints
-        $course->load([
-            'sections' => function ($query) {
-                $query->where('status', 'published')->orderBy('sort_order');
-            },
-            'sections.lessons' => function ($query) {
-                $query->where('status', 'published')->orderBy('sort_order');
-            }
-        ]);
-
-        // 4. Calculate if the user has full access to the outline lessons
-        $hasAccess = false;
-        if ($user) {
-            $enrollment = Enrollment::where('user_id', $user->id)
-                ->where('course_id', $course->id)
-                ->first();
-
-            if ($enrollment && in_array($enrollment->status, ['active', 'completed'])) {
-                $hasAccess = true;
-            }
-
-            if ((int) $course->instructor_id === (int) $user->id) {
-                $hasAccess = true;
-            }
-        }
-
-        return [
-            'sections' => $course->sections,
-            'has_access' => $hasAccess,
-        ];
-    }
-
-    public function previewLesson(int $id): \App\Models\Lesson
-    {
-        // 1. Fetch lesson by ID (not soft-deleted)
-        $lesson = \App\Models\Lesson::with('course')->find($id);
-
-        if (!$lesson) {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        // 2. Check if course is published
-        $course = $lesson->course;
-        if (!$course || $course->status !== 'published') {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
-        }
-
-        // 3. Check if lesson status is hidden
-        if ($lesson->status === 'hidden') {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
-        }
-
-        // 4. Check if lesson is previewable and published
-        if (!$lesson->is_preview || $lesson->status !== 'published') {
-            throw new \App\Exceptions\BusinessException('Bài học này không được xem trước.', 403);
-        }
-
-        return $lesson;
-    }
-
-    public function reviews(int $id, array $params): array
-    {
-        // 1. Fetch course by ID
-        $course = Course::find($id);
-
-        if (!$course) {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        if ($course->status !== 'published') {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
-        }
-
-        // 2. Query reviews
-        $query = $course->reviews()->with('order.user');
-
-        // Apply rating filter
-        if (isset($params['rating'])) {
-            $query->where('rating', (int) $params['rating']);
-        }
-
-        // Apply sorting
-        $sort = $params['sort'] ?? 'newest';
-        if ($sort === 'newest') {
-            $query->orderBy('course_reviews.created_at', 'desc');
-        } elseif ($sort === 'highest_rating') {
-            $query->orderBy('rating', 'desc')->orderBy('course_reviews.created_at', 'desc');
-        } elseif ($sort === 'lowest_rating') {
-            $query->orderBy('rating', 'asc')->orderBy('course_reviews.created_at', 'desc');
-        }
-
-        // Paginate reviews
-        $perPage = (int) ($params['per_page'] ?? 10);
-        $paginator = $query->paginate($perPage);
-
-        return [
-            'paginator' => $paginator,
-        ];
-    }
-
-    public function showInstructor(int $id): array
-    {
-        // 1. Fetch user by ID
-        $user = \App\Models\User::find($id);
-
-        if (!$user) {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        // 2. Check if user is an active instructor
-        if ($user->role !== 'instructor' || $user->status !== 'active') {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
-        }
-
-        // 3. Eager load and calculate stats
-        $instructor = \App\Models\User::query()
-            ->select('users.*')
-            ->where('users.id', $id)
-            ->with(['instructorProfile', 'publishedCourses'])
-            ->withCount([
-                'publishedCourses as published_courses_count',
-                'courseEnrollments as total_enrollments_count',
-            ])
-            ->selectSub(function ($query) {
-                $query->from('course_reviews')
-                    ->join('orders', 'orders.id', '=', 'course_reviews.order_id')
-                    ->join('courses', 'courses.id', '=', 'orders.course_id')
-                    ->whereColumn('courses.instructor_id', 'users.id')
-                    ->where('courses.status', 'published')
-                    ->whereNull('course_reviews.deleted_at')
-                    ->select(\Illuminate\Support\Facades\DB::raw('AVG(course_reviews.rating)'));
-            }, 'average_rating')
-            ->first();
-
-        // 4. Resolve optional authenticated user
-        $currentUser = $this->resolveOptionalUser();
-
-        foreach ($instructor->publishedCourses as $course) {
-            $isEnrolled = false;
-            $enrollmentStatus = null;
-            $hasAccess = false;
-            $isInWishlist = false;
-
-            if ($currentUser) {
-                $enrollment = \App\Models\Enrollment::where('user_id', $currentUser->id)
-                    ->where('course_id', $course->id)
-                    ->first();
-
-                if ($enrollment) {
-                    $isEnrolled = true;
-                    $enrollmentStatus = $enrollment->status;
-                    if (in_array($enrollment->status, ['active', 'completed'])) {
-                        $hasAccess = true;
-                    }
-                }
-
-                $isInWishlist = \App\Models\Wishlist::where('user_id', $currentUser->id)
-                    ->where('course_id', $course->id)
-                    ->exists();
-
-                if ((int) $course->instructor_id === (int) $currentUser->id) {
-                    $hasAccess = true;
-                }
-            }
-
-            $course->is_enrolled = $isEnrolled;
-            $course->enrollment_status = $enrollmentStatus;
-            $course->is_in_wishlist = $isInWishlist;
-            $course->has_access = $hasAccess;
-        }
-
-        return [
-            'instructor' => $instructor,
-        ];
-    }
-
-    public function faqs(int $id, array $params): array
-    {
-        // 1. Fetch course by ID
-        $course = Course::find($id);
-
-        if (!$course) {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        // 2. Check if course is published
-        if ($course->status !== 'published') {
-            throw new \App\Exceptions\BusinessException('Không tìm thấy dữ liệu phù hợp.', 404);
-        }
-
-        // 3. Query faqs (only active)
-        $query = $course->faqs()->where('faqs.status', 'active');
-
-        // Paginate faqs
-        $perPage = (int) ($params['per_page'] ?? 10);
-        $paginator = $query->paginate($perPage);
-
-        return [
-            'paginator' => $paginator,
-        ];
-    }
-
-    private function resolveOptionalUser()
-    {
-        $plainAccessToken = request()->bearerToken();
-
-        if (!$plainAccessToken) {
-            return null;
-        }
-
-        try {
-            $tokenPayload = $this->accessTokenService->parseAccessToken($plainAccessToken);
-            $session = $this->sessionRepository->findActiveById($tokenPayload['session_id']);
-
-            if (!$session) {
-                return null;
-            }
-
-            $user = $this->userRepository->findById($tokenPayload['user_id']);
-
-            if (!$user || !$user->isActive()) {
-                return null;
-            }
-
-            if ((int) $session->user_id !== (int) $user->id) {
-                return null;
-            }
-
-            return $user;
-        } catch (\Exception $exception) {
-            // Silence any exceptions during optional auth parsing
-            return null;
-        }
-    }
-}
diff --git a/BE/app/Services/Faq/FaqAdminService.php b/BE/app/Services/Faq/FaqAdminService.php
index 70566b1..451c67b 100644
--- a/BE/app/Services/Faq/FaqAdminService.php
+++ b/BE/app/Services/Faq/FaqAdminService.php
@@ -5,6 +5,7 @@
 use App\Models\Faq;
 use App\Models\Course;
 use Illuminate\Support\Facades\DB;
+use Illuminate\Support\Facades\Log;
 
 class FaqAdminService
 {
@@ -200,14 +201,10 @@ public function deleteFaq(int $id): bool
         }
 
         return DB::transaction(function () use ($faq) {
-            // Free up sort_order and soft-delete pivot links
+            // Delete pivot links
             DB::table('course_faqs')
                 ->where('faq_id', $faq->id)
-                ->whereNull('deleted_at')
-                ->update([
-                    'sort_order' => DB::raw('faq_id + 1000000'),
-                    'deleted_at' => now()
-                ]);
+                ->delete();
 
             return (bool) $faq->delete();
         });
@@ -253,39 +250,35 @@ public function syncFaqCourses(int $id, array $courseIds): ?array
     /**
      * Custom sync courses to support soft delete and prevent sort_order collisions.
      */
-    private function customSyncCourses($faq, array $courseIds): void
+    private function customSyncCourses(Faq $faq, array $courseIds): void
     {
         $now = now();
         
-        // 1. Get existing pivot records (including soft-deleted ones)
+        // 1. Get existing pivot records
         $existing = DB::table('course_faqs')
             ->where('faq_id', $faq->id)
             ->get()
             ->keyBy('course_id');
 
-        // 2. Determine which courses to detach (soft delete)
+        // 2. Determine which courses to detach (delete)
         foreach ($existing as $courseId => $pivot) {
-            if (!in_array($courseId, $courseIds) && $pivot->deleted_at === null) {
+            if (!in_array($courseId, $courseIds)) {
                 DB::table('course_faqs')
                     ->where('faq_id', $faq->id)
                     ->where('course_id', $courseId)
-                    ->update([
-                        'sort_order' => $faq->id + 1000000 + $courseId, // ensure uniqueness
-                        'deleted_at' => $now
-                    ]);
+                    ->delete();
             }
         }
 
-        // 3. Determine which courses to attach / restore / update
+        // 3. Determine which courses to attach / update
         foreach ($courseIds as $index => $courseId) {
             if (isset($existing[$courseId])) {
-                // If it exists (active or soft-deleted), restore it and update sort_order
+                // If it exists, update sort_order
                 DB::table('course_faqs')
                     ->where('faq_id', $faq->id)
                     ->where('course_id', $courseId)
                     ->update([
                         'sort_order' => $index,
-                        'deleted_at' => null,
                         'created_at' => $existing[$courseId]->created_at ?? $now
                     ]);
             } else {
@@ -295,7 +288,6 @@ private function customSyncCourses($faq, array $courseIds): void
                     'course_id' => $courseId,
                     'sort_order' => $index,
                     'created_at' => $now,
-                    'deleted_at' => null
                 ]);
             }
         }
@@ -315,7 +307,6 @@ private function calculateSummary(): array
 
         // Count unique courses that have at least one active FAQ linked
         $linkedCourses = DB::table('course_faqs')
-            ->whereNull('deleted_at')
             ->distinct('course_id')
             ->count('course_id');
 
diff --git a/BE/app/Services/Instructor/CourseChecklistService.php b/BE/app/Services/Instructor/CourseChecklistService.php
index a8ab778..21a990a 100644
--- a/BE/app/Services/Instructor/CourseChecklistService.php
+++ b/BE/app/Services/Instructor/CourseChecklistService.php
@@ -97,7 +97,7 @@ public function calculateCompletion(int $instructorId, object|int $courseOrId):
             [
                 'key' => 'lesson_media',
                 'label' => 'Nội dung/Video bài học',
-                'passed' => $lessons->where('status', 'published')->filter(function ($l) {
+                'passed' => $lessons->where('status', 'published')->filter(function ($l) use ($lessons) {
                     $lType = strtolower((string) ($l->lesson_type ?? 'video'));
                     if ($lType === 'video') {
                         $vUrl = (string) ($l->video_url ?? '');
@@ -305,7 +305,7 @@ private function checkCourseInfo(object $course, array &$checks, array &$missing
             $missingItems,
             $warnings,
             'course_basic_info',
-            'Thﾃｴng tin cﾆ｡ b蘯｣n c盻ｧa khﾃｳa h盻皇',
+            'Thông tin cơ bản của khóa học',
             $missing,
             $checkWarnings
         );
@@ -398,7 +398,7 @@ private function checkLessons(
                 $missing[] = 'lesson_content';
             }
 
-            if (! in_array($lessonType, ['video', 'text', 'article', 'document', 'quiz'], true)
+            if (! in_array($lessonType, ['video', 'text', 'article', 'document'], true)
                 && $this->blank($lesson->content)
                 && $this->blank($lesson->video_url)
             ) {
@@ -423,60 +423,7 @@ private function checkLessons(
             $missingItems,
             $warnings,
             'lesson_content',
-            'Bﾃi h盻皇 vﾃ n盻冓 dung bﾃi h盻皇',
-            $missing,
-            $checkWarnings
-        );
-    }
-
-    private function checkQuizzes(
-        Collection $quizzes,
-        Collection $questionStats,
-        array &$checks,
-        array &$missingItems,
-        array &$warnings
-    ): void {
-        $missing = [];
-        $checkWarnings = [];
-
-        $publishedQuizzes = $quizzes->where('status', 'published');
-
-        if ($publishedQuizzes->isEmpty()) {
-            $missing[] = 'quiz';
-        }
-
-        if ($questionStats->isEmpty()) {
-            $missing[] = 'quiz_question';
-        }
-
-        foreach ($questionStats as $question) {
-            if ($this->blank($question->question_text)) {
-                $missing[] = 'quiz_question_text';
-            }
-
-            $questionType = strtolower((string) $question->question_type);
-
-            if (in_array($questionType, ['single_choice', 'multiple_choice', 'choice'], true)) {
-                if ((int) $question->options_count < 2) {
-                    $missing[] = 'quiz_option';
-                }
-
-                if ((int) $question->correct_options_count < 1) {
-                    $missing[] = 'quiz_correct_option';
-                }
-            }
-        }
-
-        if ($quizzes->where('status', '!=', 'published')->isNotEmpty()) {
-            $checkWarnings[] = 'draft_or_hidden_quiz';
-        }
-
-        $this->pushCheck(
-            $checks,
-            $missingItems,
-            $warnings,
-            'quiz',
-            'Quiz vﾃ cﾃ｢u h盻淑 quiz',
+            'Bài học và nội dung bài học',
             $missing,
             $checkWarnings
         );
@@ -516,3 +463,4 @@ private function blank(mixed $value): bool
         return trim((string) $value) === '';
     }
 }
+
diff --git a/BE/app/Services/Instructor/CourseCreditService.php b/BE/app/Services/Instructor/CourseCreditService.php
index f529293..30cb41c 100644
--- a/BE/app/Services/Instructor/CourseCreditService.php
+++ b/BE/app/Services/Instructor/CourseCreditService.php
@@ -30,8 +30,6 @@ public function getBalanceForDisplay(int $instructorId): array
 {
     $instructor = User::query()
         ->where('id', $instructorId)
-        ->where('role', 'instructor')
-        ->whereNull('deleted_at')
         ->first();
 
     if (! $instructor) {
@@ -62,7 +60,6 @@ public function addCreditsFromPaidOrder(Order $order): void
             }
 
             $package = CourseCreditPackage::query()
-                ->withTrashed()
                 ->find((int) $order->credit_package_id);
 
             if (! $package) {
@@ -115,8 +112,6 @@ public function approveCourseAndDeductCredit(int $courseId): object
             }
 
             $instructor = DB::table('users')
-                ->where('id', $course->instructor_id)
-                ->whereNull('deleted_at')
                 ->first();
 
             if (! $instructor || ($instructor->status ?? null) !== 'active' || (int) ($instructor->locked ?? 0) === 1) {
diff --git a/BE/app/Services/Instructor/InstructorCourseService.php b/BE/app/Services/Instructor/InstructorCourseService.php
index 132515a..dd713d9 100644
--- a/BE/app/Services/Instructor/InstructorCourseService.php
+++ b/BE/app/Services/Instructor/InstructorCourseService.php
@@ -1,4 +1,5 @@
 <?php
+
 namespace App\Services\Instructor;
 
 use App\Exceptions\BusinessException;
@@ -14,6 +15,7 @@
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 use Illuminate\Http\UploadedFile;
 use Illuminate\Support\Facades\DB;
+use Illuminate\Support\Facades\Schema;
 use Illuminate\Support\Str;
 use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
 use Symfony\Component\HttpKernel\Exception\HttpException;
@@ -26,6 +28,7 @@ public function __construct(
         private readonly InstructorLessonRepository $instructorLessonRepository,
         private readonly FileUpload $fileUpload,
     ) {}
+
     public function createCourse(User $instructor, array $validatedData): Course
     {
         return DB::transaction(function () use ($instructor, $validatedData): Course {
@@ -45,11 +48,10 @@ public function createCourse(User $instructor, array $validatedData): Course
                 'slug' => $this->makeUniqueCourseSlug((string) $slugSource),
                 'status' => 'draft',
                 'is_featured' => false,
-                'total_duration_seconds' => 0,
                 'published_at' => null,
                 'admin_reject_reason' => null,
                 'language' => $validatedData['language'] ?? 'vi',
-                'level' => $validatedData['level'] ?? 'beginner',
+                'course_level' => $validatedData['course_level'] ?? $validatedData['level'] ?? 'beginner',
             ]);
 
             $course = $this->instructorCourseRepository->create($courseData);
@@ -61,6 +63,7 @@ public function createCourse(User $instructor, array $validatedData): Course
             return $this->instructorCourseRepository->findWithCategories((int) $course->id);
         });
     }
+
     public function paginateLessons(
         User $instructor,
         array $filters,
@@ -85,6 +88,7 @@ public function paginateLessons(
             $filters,
         );
     }
+
     public function createLesson(User $instructor, array $validatedData): Lesson
     {
         return DB::transaction(function () use (
@@ -104,19 +108,15 @@ public function createLesson(User $instructor, array $validatedData): Lesson
                 "course_id" => $course->id,
                 "course_section_id" => $section->id,
                 "title" => $validatedData["title"],
-                "slug" => $this->makeUniqueLessonSlug(
-                    $course->id,
-                    $validatedData["title"],
-                ),
                 "lesson_type" => $lessonType,
                 "content" => $validatedData["content"] ?? null,
                 "video_url" => $validatedData["video_url"] ?? null,
                 "video_duration_seconds" =>
-                    $validatedData["video_duration_seconds"] ?? 0,
+                $validatedData["video_duration_seconds"] ?? 0,
                 "is_preview" => $validatedData["is_preview"] ?? false,
                 "status" => $validatedData["status"] ?? "draft",
                 "sort_order" =>
-                    $validatedData["sort_order"] ??
+                $validatedData["sort_order"] ??
                     $this->instructorLessonRepository->getNextSortOrder(
                         $section->id,
                     ),
@@ -130,10 +130,12 @@ public function createLesson(User $instructor, array $validatedData): Lesson
                 ->load(["course", "section", "assets"]);
         });
     }
+
     public function getLesson(User $instructor, int $lessonId): Lesson
     {
         return $this->findOwnedLessonOrFail($instructor, $lessonId);
     }
+
     public function updateLesson(
         User $instructor,
         int $lessonId,
@@ -178,13 +180,6 @@ public function updateLesson(
                     $lessonData[$field] = $validatedData[$field];
                 }
             }
-            if (array_key_exists("title", $validatedData)) {
-                $lessonData["slug"] = $this->makeUniqueLessonSlug(
-                    $course->id,
-                    $validatedData["title"],
-                    $lesson->id,
-                );
-            }
             if ($lessonType === "text") {
                 $lessonData["video_url"] = null;
                 $lessonData["video_duration_seconds"] = 0;
@@ -194,6 +189,7 @@ public function updateLesson(
                 ->load(["course", "section", "assets"]);
         });
     }
+
     public function deleteLesson(User $instructor, int $lessonId): void
     {
         DB::transaction(function () use ($instructor, $lessonId): void {
@@ -201,6 +197,7 @@ public function deleteLesson(User $instructor, int $lessonId): void
             $this->instructorLessonRepository->delete($lesson);
         });
     }
+
     public function toggleLessonPreview(
         User $instructor,
         int $lessonId,
@@ -214,7 +211,7 @@ public function toggleLessonPreview(
             $lesson = $this->findOwnedLessonOrFail($instructor, $lessonId);
             if ($isPreview && $lesson->status === "hidden") {
                 throw new BusinessException(
-                    "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                    "Bài học đang ở trạng thái ẩn không thể xem trước.",
                     400,
                 );
             }
@@ -225,6 +222,7 @@ public function toggleLessonPreview(
                 ->load(["course", "section", "assets"]);
         });
     }
+
     public function uploadLessonVideo(
         User $instructor,
         int $lessonId,
@@ -251,6 +249,7 @@ public function uploadLessonVideo(
                 ->load(["course", "section", "assets"]);
         });
     }
+
     public function uploadLessonAsset(
         User $instructor,
         int $lessonId,
@@ -333,11 +332,13 @@ public function submitForReview(User $instructor, int $courseId): Course
                         'channel' => 'database',
                     ]);
                 }
-            } catch (\Throwable $e) {}
+            } catch (\Throwable $e) {
+            }
 
             return $updatedCourse;
         });
     }
+
     public function getRejectedReviewNotes(
         User $instructor,
         int $courseId,
@@ -346,16 +347,16 @@ public function getRejectedReviewNotes(
             $courseId,
         );
         if (!$course) {
-            throw new NotFoundHttpException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
+            throw new NotFoundHttpException("Không tìm thấy khóa học.");
         }
         if ((int) $course->instructor_id !== (int) $instructor->id) {
             throw new BusinessException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền xem thông tin khóa học này.",
                 403,
             );
         }
         if ($course->status !== "rejected") {
-            throw new NotFoundHttpException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
+            throw new NotFoundHttpException("Khóa học không ở trạng thái bị từ chối.");
         }
         return $course;
     }
@@ -371,7 +372,7 @@ private function courseCanBeSubmitted(Course $course): bool
                 "slug",
                 "short_description",
                 "description",
-                "level",
+                "course_level",
                 "language",
             ]
             as $requiredField
@@ -415,70 +416,53 @@ private function findOwnedLessonOrFail(
             $lessonId,
         );
         if (!$lesson) {
-            throw new NotFoundHttpException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
+            throw new NotFoundHttpException("Không tìm thấy bài học.");
         }
         if (
             !$lesson->course ||
             (int) $lesson->course->instructor_id !== (int) $instructor->id
         ) {
             throw new AccessDeniedHttpException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền truy cập bài học này.",
             );
         }
         return $lesson->load(["course", "section", "assets"]);
     }
+
     private function assertCourseOwnedByInstructor(
         int $courseId,
         User $instructor,
     ): Course {
         $course = $this->instructorLessonRepository->findCourseById($courseId);
         if (!$course) {
-            throw new NotFoundHttpException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
+            throw new NotFoundHttpException("Không tìm thấy khóa học.");
         }
         if ((int) $course->instructor_id !== (int) $instructor->id) {
             throw new AccessDeniedHttpException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền thao tác trên khóa học này.",
             );
         }
         return $course;
     }
+
     private function findSectionOrFail(int $sectionId): CourseSection
     {
         $section = $this->instructorLessonRepository->findSectionById(
             $sectionId,
         );
         if (!$section) {
-            throw new NotFoundHttpException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
+            throw new NotFoundHttpException("Không tìm thấy chương học.");
         }
         return $section;
     }
+
     private function assertSectionBelongsToCourse(
         CourseSection $section,
         Course $course,
     ): void {
         if ((int) $section->course_id !== (int) $course->id) {
-            throw new HttpException(422, "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・");
-        }
-    }
-    private function makeUniqueLessonSlug(
-        int $courseId,
-        string $title,
-        ?int $ignoreLessonId = null,
-    ): string {
-        $baseSlug = Str::slug($title);
-        $slug = $baseSlug;
-        $counter = 1;
-        while (
-            $this->instructorLessonRepository->slugExistsInCourse(
-                $courseId,
-                $slug,
-                $ignoreLessonId,
-            )
-        ) {
-            $counter++;
-            $slug = $baseSlug . "-" . $counter;
+            throw new HttpException(422, "Chương học không thuộc khóa học này.");
         }
-        return $slug;
     }
 
     public function updateCourse(
@@ -489,12 +473,12 @@ public function updateCourse(
         $course = Course::query()->where("id", $courseId)->first();
 
         if (!$course) {
-            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 404);
+            throw new BusinessException("Không tìm thấy khóa học.", 404);
         }
 
         if ((int) $course->instructor_id !== (int) $instructorId) {
             throw new BusinessException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền cập nhật khóa học này.",
                 403,
             );
         }
@@ -604,11 +588,10 @@ private function validateCategoryIds(array $categoryIds): void
         $validCategoryCount = Category::query()
             ->whereIn("id", $categoryIds)
             ->where("status", "active")
-            ->whereNull("deleted_at")
             ->count();
 
         if ($validCategoryCount !== count(array_unique($categoryIds))) {
-            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 422);
+            throw new BusinessException("Danh mục đã chọn không hợp lệ.", 422);
         }
     }
 
@@ -618,10 +601,10 @@ private function removeForbiddenFields(array &$data): void
             $data["id"],
             $data["instructor_id"],
             $data["is_featured"],
-            $data["total_duration_seconds"],
+            
             $data["published_at"],
             $data["admin_reject_reason"],
-            $data["deleted_at"],
+            
             $data["created_at"],
             $data["updated_at"],
         );
@@ -740,12 +723,12 @@ private function ensureCourseBelongsToInstructor(
         $course = Course::query()->find($courseId);
 
         if (!$course) {
-            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 404);
+            throw new BusinessException("Không tìm thấy khóa học.", 404);
         }
 
         if ((int) $course->instructor_id !== (int) $instructorId) {
             throw new BusinessException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền thao tác trên khóa học này.",
                 403,
             );
         }
@@ -762,16 +745,16 @@ private function getOwnedSection(
             ->find($sectionId);
 
         if (!$section) {
-            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 404);
+            throw new BusinessException("Không tìm thấy chương học.", 404);
         }
 
         if (!$section->course) {
-            throw new BusinessException("D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・", 404);
+            throw new BusinessException("Không tìm thấy thông tin khóa học.", 404);
         }
 
         if ((int) $section->course->instructor_id !== (int) $instructorId) {
             throw new BusinessException(
-                "D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・",
+                "Bạn không có quyền thao tác trên chương học này.",
                 403,
             );
         }
@@ -793,12 +776,13 @@ private function removeForbiddenSectionFields(array &$data): void
         unset(
             $data["id"],
             $data["course_id"],
-            $data["deleted_at"],
+            
             $data["created_at"],
             $data["updated_at"],
         );
     }
 
+
     public function getInstructorProfile(int $userId): \App\Models\InstructorProfile
     {
         $profile = \App\Models\InstructorProfile::query()
@@ -850,182 +834,6 @@ public function getRevenueReport(\App\Models\User $instructor, array $filters):
         return $repository->getRevenueReport((int) $instructor->id, $filters);
     }
 
-    public function paginateInstructorQuizzes(User $instructor, array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
-    {
-        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);
-
-        if (!empty($filters['course_id'])) {
-            $this->ensureCourseBelongsToInstructor((int) $filters['course_id'], (int) $instructor->id);
-        }
-
-        if (!empty($filters['lesson_id'])) {
-            $lesson = $repository->findLessonWithCourse((int) $filters['lesson_id']);
-
-            if (!$lesson || !$lesson->course) {
-                throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 404);
-            }
-
-            if ((int) $lesson->course->instructor_id !== (int) $instructor->id) {
-                throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 403);
-            }
-        }
-
-        return $repository->paginateOwnedQuizzes((int) $instructor->id, $filters);
-    }
-
-    public function getInstructorQuiz(User $instructor, int $quizId): \App\Models\Quiz
-    {
-        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);
-
-        $quiz = $repository->findOwnedQuiz((int) $instructor->id, $quizId);
-
-        if (!$quiz) {
-            throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 404);
-        }
-
-        return $quiz;
-    }
-
-    public function createInstructorQuiz(User $instructor, array $data): \App\Models\Quiz
-    {
-        return DB::transaction(function () use ($instructor, $data): \App\Models\Quiz {
-            $lesson = $this->getQuizLessonOwnedByInstructor((int) $data['lesson_id'], (int) $instructor->id);
-
-            foreach ($data['questions'] as $question) {
-                $this->assertQuizQuestionHasCorrectOption($question);
-            }
-
-            $quiz = \App\Models\Quiz::query()->create([
-                'course_id' => (int) $lesson->course_id,
-                'lesson_id' => (int) $lesson->id,
-                'title' => $data['title'],
-                'description' => $data['description'] ?? null,
-                'passing_score' => $data['passing_score'] ?? 0,
-                'status' => $data['status'] ?? 'draft',
-            ]);
-
-            $this->syncInstructorQuizQuestions($quiz, $data['questions']);
-
-            return $quiz->refresh()->load([
-                'course:id,instructor_id,title,status',
-                'lesson:id,course_id,title,status',
-                'questions.options',
-            ]);
-        });
-    }
-
-    public function updateInstructorQuiz(User $instructor, int $quizId, array $data): \App\Models\Quiz
-    {
-        return DB::transaction(function () use ($instructor, $quizId, $data): \App\Models\Quiz {
-            $quiz = $this->getInstructorQuiz($instructor, $quizId);
-
-            $updateData = [];
-
-            if (array_key_exists('lesson_id', $data)) {
-                $lesson = $this->getQuizLessonOwnedByInstructor((int) $data['lesson_id'], (int) $instructor->id);
-                $updateData['lesson_id'] = (int) $lesson->id;
-                $updateData['course_id'] = (int) $lesson->course_id;
-            }
-
-            foreach (['title', 'description', 'passing_score', 'status'] as $field) {
-                if (array_key_exists($field, $data)) {
-                    $updateData[$field] = $data[$field];
-                }
-            }
-
-            if ($updateData !== []) {
-                $quiz->update($updateData);
-            }
-
-            if (array_key_exists('questions', $data)) {
-                foreach ($data['questions'] as $question) {
-                    $this->assertQuizQuestionHasCorrectOption($question);
-                }
-
-                $this->syncInstructorQuizQuestions($quiz, $data['questions']);
-            }
-
-            return $quiz->refresh()->load([
-                'course:id,instructor_id,title,status',
-                'lesson:id,course_id,title,status',
-                'questions.options',
-            ]);
-        });
-    }
-
-    public function deleteInstructorQuiz(User $instructor, int $quizId): void
-    {
-        DB::transaction(function () use ($instructor, $quizId): void {
-            $quiz = $this->getInstructorQuiz($instructor, $quizId);
-            $quiz->delete();
-        });
-    }
-
-    private function getQuizLessonOwnedByInstructor(int $lessonId, int $instructorId): \App\Models\Lesson
-    {
-        $repository = app(\App\Repositories\Instructor\InstructorQuizRepository::class);
-
-        $lesson = $repository->findLessonWithCourse($lessonId);
-
-        if (!$lesson || !$lesson->course) {
-            throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 404);
-        }
-
-        if ((int) $lesson->course->instructor_id !== $instructorId) {
-            throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 403);
-        }
-
-        return $lesson;
-    }
-
-    private function assertQuizQuestionHasCorrectOption(array $question): void
-    {
-        $hasCorrectOption = collect($question['options'] ?? [])
-            ->contains(fn (array $option): bool => (bool) ($option['is_correct'] ?? false));
-
-        if (!$hasCorrectOption) {
-            throw new BusinessException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・', 422);
-        }
-    }
-
-    private function syncInstructorQuizQuestions(\App\Models\Quiz $quiz, array $questions): void
-    {
-        $oldQuestionIds = \App\Models\QuizQuestion::query()
-            ->where('quiz_id', $quiz->id)
-            ->pluck('id')
-            ->all();
-
-        if ($oldQuestionIds !== []) {
-            \App\Models\QuizOption::query()
-                ->whereIn('question_id', $oldQuestionIds)
-                ->delete();
-
-            \App\Models\QuizQuestion::query()
-                ->whereIn('id', $oldQuestionIds)
-                ->delete();
-        }
-
-        foreach (array_values($questions) as $questionIndex => $questionData) {
-            $question = \App\Models\QuizQuestion::query()->create([
-                'quiz_id' => (int) $quiz->id,
-                'question_text' => $questionData['question_text'],
-                'question_type' => $questionData['question_type'],
-                'score' => $questionData['score'],
-                'sort_order' => $questionIndex + 1,
-                'explanation' => $questionData['explanation'] ?? null,
-            ]);
-
-            foreach (array_values($questionData['options']) as $optionIndex => $optionData) {
-                \App\Models\QuizOption::query()->create([
-                    'question_id' => (int) $question->id,
-                    'option_text' => $optionData['option_text'],
-                    'is_correct' => (bool) $optionData['is_correct'],
-                    'sort_order' => $optionIndex + 1,
-                ]);
-            }
-        }
-    }
-
     public function createWithdrawRequest(User $instructor, array $data): \App\Models\WithdrawRequest
     {
         return DB::transaction(function () use ($instructor, $data): \App\Models\WithdrawRequest {
@@ -1057,9 +865,9 @@ public function createWithdrawRequest(User $instructor, array $data): \App\Model
                 'amount' => $amount,
                 'status' => 'pending',
                 'requested_at' => now(),
-                'approved_at' => null,
+                'reviewed_by' => null,
                 'paid_at' => null,
-                'rejected_reason' => null,
+                'admin_reject_reason' => null,
                 'provider_payout_id' => null,
                 'account_number_snapshot' => $payoutAccount->account_number,
                 'account_name_snapshot' => $payoutAccount->account_name,
@@ -1081,17 +889,17 @@ public function createWithdrawRequest(User $instructor, array $data): \App\Model
 
     public function getCourseLearners(int $courseId, int $instructorId, array $filters)
     {
-        $course = \DB::table('courses')->where('id', $courseId)->whereNull('deleted_at')->first();
+        $course = DB::table('courses')->where('id', $courseId)->first();
 
         if (!$course) {
-            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・');
+            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Không tìm thấy khóa học.');
         }
 
-        if ($course->instructor_id !== $instructorId) {
-            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('D盻ｯ li盻㎡ khﾃｴng h盻｣p l盻・');
+        if ((int) $course->instructor_id !== (int) $instructorId) {
+            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Bạn không có quyền xem thông tin khóa học này.');
         }
 
-        $query = \DB::table('enrollments')
+        $query = DB::table('enrollments')
             ->join('users', 'enrollments.user_id', '=', 'users.id')
             ->where('enrollments.course_id', $courseId)
             ->where('users.role', 'learner')
@@ -1106,10 +914,10 @@ public function getCourseLearners(int $courseId, int $instructorId, array $filte
                 'enrollments.last_accessed_at',
                 'enrollments.completed_at'
             );
-        
-        if (\Schema::hasColumn('enrollments', 'enrolled_at')) {
+
+        if (Schema::hasColumn('enrollments', 'enrolled_at')) {
             $query->addSelect('enrollments.enrolled_at');
-        } elseif (\Schema::hasColumn('enrollments', 'created_at')) {
+        } elseif (Schema::hasColumn('enrollments', 'created_at')) {
             $query->addSelect('enrollments.created_at as enrolled_at');
         }
 
@@ -1118,7 +926,7 @@ public function getCourseLearners(int $courseId, int $instructorId, array $filte
             $search = $filters['search'];
             $query->where(function ($q) use ($search) {
                 $q->where('users.full_name', 'like', "%{$search}%")
-                  ->orWhere('users.email', 'like', "%{$search}%");
+                    ->orWhere('users.email', 'like', "%{$search}%");
             });
         }
 
@@ -1127,7 +935,7 @@ public function getCourseLearners(int $courseId, int $instructorId, array $filte
             $query->where('enrollments.status', $filters['status']);
         }
 
-        if (\Schema::hasColumn('enrollments', 'progress_percent')) {
+        if (Schema::hasColumn('enrollments', 'progress_percent')) {
             $query->addSelect('enrollments.progress_percent');
         } else {
             $query->selectRaw('0 as progress_percent');
@@ -1148,22 +956,21 @@ public function getCourseLearners(int $courseId, int $instructorId, array $filte
         ];
 
         $orderCol = $sortMap[$sortBy] ?? 'enrollments.last_accessed_at';
-        if ($orderCol === 'enrollments.created_at' && \Schema::hasColumn('enrollments', 'enrolled_at')) {
+        if ($orderCol === 'enrollments.created_at' && Schema::hasColumn('enrollments', 'enrolled_at')) {
             $orderCol = 'enrollments.enrolled_at';
         }
         $query->orderBy($orderCol, $sortDirection);
 
         $perPage = $filters['per_page'] ?? 15;
-        
+
         return $query->paginate($perPage);
     }
 
-public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
+    public function paginateCourses(int $instructorId, array $filters): LengthAwarePaginator
     {
         return $this->instructorCourseRepository->paginateCourses($instructorId, $filters);
     }
 
-
     public function createDraftCourse(User $instructor, array $data): Course
     {
         return DB::transaction(function () use ($instructor, $data): Course {
@@ -1189,11 +996,10 @@ public function createDraftCourse(User $instructor, array $data): Course
                 'price' => $data['price'] ?? 0,
                 'status' => 'draft',
                 'is_featured' => false,
-                'total_duration_seconds' => 0,
                 'published_at' => null,
                 'admin_reject_reason' => null,
                 'language' => $data['language'] ?? 'vi',
-                'level' => $data['level'] ?? 'beginner',
+                'course_level' => $data['course_level'] ?? $data['level'] ?? 'beginner',
             ]);
 
             $course = $this->instructorCourseRepository->create($courseData);
@@ -1239,7 +1045,6 @@ public function updateCourseDraft(User $instructor, int $courseId, array $data):
         $course = Course::query()
             ->where('id', $courseId)
             ->where('instructor_id', (int) $instructor->id)
-            ->whereNull('deleted_at')
             ->first();
 
         if (!$course) {
@@ -1301,10 +1106,9 @@ private function makeUniqueCourseSlug(string $source, ?int $ignoreCourseId = nul
 
         while (
             Course::query()
-                ->where('slug', $slug)
-                ->whereNull('deleted_at')
-                ->when($ignoreCourseId !== null, fn ($query) => $query->where('id', '!=', $ignoreCourseId))
-                ->exists()
+            ->where('slug', $slug)
+            ->when($ignoreCourseId !== null, fn($query) => $query->where('id', '!=', $ignoreCourseId))
+            ->exists()
         ) {
             $counter++;
             $slug = $base . '-' . $counter;
diff --git a/BE/app/Services/Instructor/InstructorCreditOrderService.php b/BE/app/Services/Instructor/InstructorCreditOrderService.php
index d289420..ccee49b 100644
--- a/BE/app/Services/Instructor/InstructorCreditOrderService.php
+++ b/BE/app/Services/Instructor/InstructorCreditOrderService.php
@@ -14,8 +14,6 @@ public function createOrder(int $instructorId, int $packageId): Order
     {
         return DB::transaction(function () use ($instructorId, $packageId): Order {
             $user = DB::table('users')
-                ->where('id', $instructorId)
-                ->whereNull('deleted_at')
                 ->first();
 
             if (! $user) {
diff --git a/BE/app/Services/Instructor/InstructorUpgradeService.php b/BE/app/Services/Instructor/InstructorUpgradeService.php
index f6709d0..ce65fab 100644
--- a/BE/app/Services/Instructor/InstructorUpgradeService.php
+++ b/BE/app/Services/Instructor/InstructorUpgradeService.php
@@ -158,8 +158,7 @@ public function adminIndexReport(array $queryParams): array
             ->leftJoinSub($latestPayoutQuery, 'latest_pa', function ($join): void {
                 $join->on('latest_pa.user_id', '=', 'u.id');
             })
-            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id')
-            ->whereNull('u.deleted_at');
+            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id');
 
         $total = (clone $baseQuery)->count();
         $pending = (clone $baseQuery)->where('pa.status', 'pending_verification')->count();
@@ -178,8 +177,7 @@ public function adminIndexReport(array $queryParams): array
             ->leftJoinSub($latestPayoutQuery, 'latest_pa', function ($join): void {
                 $join->on('latest_pa.user_id', '=', 'u.id');
             })
-            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id')
-            ->whereNull('u.deleted_at');
+            ->leftJoin('payout_accounts as pa', 'pa.id', '=', 'latest_pa.payout_id');
 
         if (!empty($queryParams['search'])) {
             $search = trim((string) $queryParams['search']);
diff --git a/BE/app/Services/Interaction/InstructorQuestionService.php b/BE/app/Services/Interaction/InstructorQuestionService.php
index b619957..321e643 100644
--- a/BE/app/Services/Interaction/InstructorQuestionService.php
+++ b/BE/app/Services/Interaction/InstructorQuestionService.php
@@ -43,8 +43,6 @@ public function paginateQuestions(int $instructorId, array $filters): LengthAwar
             $lesson = DB::table('lessons')
                 ->join('courses', 'courses.id', '=', 'lessons.course_id')
                 ->where('lessons.id', $filters['lesson_id'])
-                ->where('courses.instructor_id', $instructorId)
-                ->whereNull('courses.deleted_at')
                 ->exists();
             if (!$lesson) {
                 throw new UnprocessableEntityHttpException('Bài học không hợp lệ.');
@@ -67,8 +65,7 @@ public function getQuestionSummary(int $instructorId, array $filters): array
             $lessonQuery = DB::table('lessons')
                 ->join('courses', 'courses.id', '=', 'lessons.course_id')
                 ->where('lessons.id', $lessonId)
-                ->where('courses.instructor_id', $instructorId)
-                ->whereNull('courses.deleted_at');
+                ->where('courses.instructor_id', $instructorId);
             if ($courseId) {
                 $lessonQuery->where('lessons.course_id', $courseId);
             }
@@ -82,7 +79,6 @@ public function getQuestionSummary(int $instructorId, array $filters): array
             ->join('courses', 'courses.id', '=', 'lessons.course_id')
             ->join('users as learner', 'learner.id', '=', 'q.user_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
             ->whereNull('q.parent_id')
             ->where('q.status', 'visible')
             ->where('learner.role', 'learner');
@@ -113,7 +109,6 @@ public function getQuestionSummary(int $instructorId, array $filters): array
             ->join('lessons', 'lessons.id', '=', 'c.lesson_id')
             ->join('courses', 'courses.id', '=', 'lessons.course_id')
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at')
             ->where('c.status', 'visible')
             ->whereDate('c.created_at', now()->toDateString())
             ->count();
@@ -148,10 +143,6 @@ public function getQuestionDetails(int $instructorId, int $commentId): Comment
             throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
         }
 
-        if ($lesson->course->deleted_at !== null) {
-            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
-        }
-
         return $comment;
     }
 
@@ -188,7 +179,7 @@ public function replyToQuestion(int $instructorId, int $commentId, array $data):
             throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
         }
 
-        if ($comment->status !== 'visible' || $comment->deleted_at !== null) {
+        if ($comment->status !== 'visible') {
             throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
         }
 
@@ -202,7 +193,7 @@ public function replyToQuestion(int $instructorId, int $commentId, array $data):
         }
 
         $lesson = $comment->lesson;
-        if (!$lesson || !$lesson->course || $lesson->course->deleted_at !== null) {
+        if (!$lesson || !$lesson->course) {
             throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
         }
 
@@ -389,10 +380,6 @@ private function getQuestionIncludingHidden(int $instructorId, int $commentId):
             throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
         }
 
-        if ($lesson->course->deleted_at !== null) {
-            throw new NotFoundHttpException('Không tìm thấy dữ liệu.');
-        }
-
         return $comment;
     }
 }
diff --git a/BE/app/Services/InteractionService.php b/BE/app/Services/InteractionService.php
deleted file mode 100644
index a6212bb..0000000
--- a/BE/app/Services/InteractionService.php
+++ /dev/null
@@ -1,158 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Exceptions\BusinessException;
-use App\Models\Comment;
-use App\Models\Lesson;
-use App\Models\Enrollment;
-use App\Models\Order;
-use App\Models\User;
-use Illuminate\Contracts\Pagination\LengthAwarePaginator;
-
-class InteractionService
-{
-    public function getLessonComments(int $lessonId, array $queryParams, User $user): LengthAwarePaginator
-    {
-        // 1. Tìm lesson và kiểm tra status
-        $lesson = Lesson::with('course')->find($lessonId);
-
-        if (!$lesson) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $course = $lesson->course;
-        if (!$course || $course->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        if ($lesson->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        // 2. Kiểm tra learner có enrollment active/completed
-        $enrollment = Enrollment::where('user_id', $user->id)
-            ->where('course_id', $lesson->course_id)
-            ->whereIn('status', ['active', 'completed'])
-            ->first();
-
-        if (!$enrollment) {
-            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
-        }
-
-        // 3. Query và phân trang comments
-        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
-        
-        return Comment::where('lesson_id', $lesson->id)
-            ->where('status', 'visible')
-            ->with('user')
-            ->orderBy('created_at', 'desc')
-            ->paginate($perPage);
-    }
-
-    public function createComment(int $lessonId, array $data, User $user): Comment
-    {
-        // 1. Tìm lesson và kiểm tra status
-        $lesson = Lesson::with('course')->find($lessonId);
-
-        if (!$lesson) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $course = $lesson->course;
-        if (!$course || $course->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        if ($lesson->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        // 2. Kiểm tra learner có enrollment active/completed
-        $enrollment = Enrollment::where('user_id', $user->id)
-            ->where('course_id', $lesson->course_id)
-            ->whereIn('status', ['active', 'completed'])
-            ->first();
-
-        if (!$enrollment) {
-            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
-        }
-
-        // 3. Kiểm tra parent_id nếu có
-        $parentId = $data['parent_id'] ?? null;
-        if ($parentId !== null) {
-            $parentComment = Comment::where('id', $parentId)
-                ->where('lesson_id', $lessonId)
-                ->where('status', 'visible')
-                ->first();
-
-            if (!$parentComment) {
-                throw new BusinessException('Dữ liệu không hợp lệ.', 422, [
-                    'parent_id' => ['Bình luận trả lời không hợp lệ hoặc đã bị ẩn.']
-                ]);
-            }
-        }
-
-        // 4. Tìm kiếm order paid liên quan đến khóa học
-        $order = Order::where('user_id', $user->id)
-            ->where('course_id', $lesson->course_id)
-            ->where('status', 'paid')
-            ->where('payment_status', 'paid')
-            ->first();
-
-        // 5. Thêm comment mới
-        $comment = Comment::create([
-            'parent_id' => $parentId,
-            'user_id' => $user->id,
-            'order_id' => $order ? $order->id : null,
-            'lesson_id' => $lessonId,
-            'content' => $data['content'],
-            'status' => 'visible',
-        ]);
-
-        return $comment->load('user');
-    }
-
-    public function replyToComment(int $commentId, array $data, User $user): Comment
-    {
-        // 1. Tìm comment gốc visible và lesson/course liên quan.
-        $parentComment = Comment::where('id', $commentId)
-            ->where('status', 'visible')
-            ->first();
-
-        if (!$parentComment) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $lesson = Lesson::with('course')->find($parentComment->lesson_id);
-        if (!$lesson) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $course = $lesson->course;
-        if (!$course || $course->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        if ($lesson->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        // 2. Kiểm tra instructor hiện tại là có phải là giảng viên của khóa học không
-        if ((int) $course->instructor_id !== (int) $user->id) {
-            throw new BusinessException('Bạn không được trả lời Q&A của khóa học này.', 403);
-        }
-
-        // 3. Tạo bình luận phản hồi
-        $reply = Comment::create([
-            'parent_id' => $parentComment->id,
-            'user_id' => $user->id,
-            'order_id' => null,
-            'lesson_id' => $parentComment->lesson_id,
-            'content' => $data['content'],
-            'status' => 'visible',
-        ]);
-
-        return $reply->load('user');
-    }
-}
diff --git a/BE/app/Services/MarketingService.php b/BE/app/Services/MarketingService.php
deleted file mode 100644
index b4f9b61..0000000
--- a/BE/app/Services/MarketingService.php
+++ /dev/null
@@ -1,65 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Exceptions\BusinessException;
-use App\Models\Banner;
-use Illuminate\Contracts\Pagination\LengthAwarePaginator;
-
-class MarketingService
-{
-    public function createCourseAnnouncement(array $data): array
-    {
-        return [
-            'banner_id' => 1,
-            'status' => 'active',
-        ];
-    }
-
-    public function getBanners(array $queryParams): LengthAwarePaginator
-    {
-        $perPage = min((int) ($queryParams['per_page'] ?? 10), 100);
-        return Banner::orderBy('sort_order')
-            ->orderByDesc('id')
-            ->paginate($perPage);
-    }
-
-    public function getBanner(int $id): Banner
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        return $banner;
-    }
-
-    public function createBanner(array $data): Banner
-    {
-        return Banner::create($data);
-    }
-
-    public function updateBanner(int $id, array $data): Banner
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $banner->update($data);
-        return $banner;
-    }
-
-    public function deleteBanner(int $id): void
-    {
-        $banner = Banner::find($id);
-
-        if (!$banner) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        $banner->delete();
-    }
-}
diff --git a/BE/app/Services/Moderation/ModerationService.php b/BE/app/Services/Moderation/ModerationService.php
index 94ba431..3f3df57 100644
--- a/BE/app/Services/Moderation/ModerationService.php
+++ b/BE/app/Services/Moderation/ModerationService.php
@@ -30,7 +30,7 @@ public function moderateItem(int $id, array $data): mixed
         }
 
         if ($targetType === 'review') {
-            $review = CourseReview::withTrashed()->find($id);
+            $review = CourseReview::find($id);
 
             if (!$review) {
                 throw new BusinessException('Không tìm thấy dữ liệu.', 404);
@@ -38,8 +38,6 @@ public function moderateItem(int $id, array $data): mixed
 
             if ($status === 'deleted') {
                 $review->delete();
-            } else {
-                $review->restore();
             }
 
             return $review;
@@ -66,8 +64,8 @@ public function getModerationItems(array $params): array
         $rating = $params['rating'] ?? 'all';
 
         // 1. Fetch comments and reviews with their relations
-        $comments = Comment::with(['user', 'lesson.course', 'order.course', 'parent'])->get();
-        $reviews = CourseReview::withTrashed()->with(['order.user', 'order.course'])->get();
+        $comments = Comment::with(['user', 'lesson.course', 'parent'])->get();
+        $reviews = CourseReview::with(['order.user', 'order.course'])->get();
 
         // 2. Helper warning evaluator
         $evaluateWarningType = function ($content) {
@@ -176,7 +174,6 @@ public function getModerationItems(array $params): array
                 'parent_id' => $c->parent_id ? (int) $c->parent_id : null,
                 'created_at' => $c->created_at ? $c->created_at->toISOString() : null,
                 'updated_at' => $c->updated_at ? $c->updated_at->toISOString() : null,
-                'deleted_at' => null,
                 'user' => $c->user ? [
                     'id' => (int) $c->user->id,
                     'full_name' => $c->user->full_name ?: $c->user->name,
@@ -224,7 +221,7 @@ public function getModerationItems(array $params): array
         $reviewsList = $reviews->map(function ($r) use ($evaluateWarningType, $now) {
             $userObj = $r->order ? $r->order->user : null;
             $courseObj = $r->order ? $r->order->course : null;
-            $statusVal = $r->deleted_at ? 'deleted' : 'visible';
+            $statusVal = 'visible';
 
             $createdAtMs = $r->created_at ? $r->created_at->timestamp * 1000 : 0;
             $nowMs = $now->timestamp * 1000;
@@ -270,7 +267,6 @@ public function getModerationItems(array $params): array
                 'parent_id' => null,
                 'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
                 'updated_at' => $r->updated_at ? $r->updated_at->toISOString() : null,
-                'deleted_at' => $r->deleted_at ? $r->deleted_at->toISOString() : null,
                 'user' => $userObj ? [
                     'id' => (int) $userObj->id,
                     'full_name' => $userObj->full_name ?: $userObj->name,
diff --git a/BE/app/Services/ModerationService.php b/BE/app/Services/ModerationService.php
deleted file mode 100644
index d5e0a64..0000000
--- a/BE/app/Services/ModerationService.php
+++ /dev/null
@@ -1,47 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Exceptions\BusinessException;
-use App\Models\Comment;
-use App\Models\CourseReview;
-
-class ModerationService
-{
-    public function moderateItem(int $id, array $data): mixed
-    {
-        $targetType = $data['target_type'];
-        $status = $data['status'];
-
-        if ($targetType === 'comment') {
-            $comment = Comment::find($id);
-
-            if (!$comment) {
-                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-            }
-
-            $comment->status = $status;
-            $comment->save();
-
-            return $comment;
-        }
-
-        if ($targetType === 'review') {
-            $review = CourseReview::withTrashed()->find($id);
-
-            if (!$review) {
-                throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-            }
-
-            if ($status === 'deleted') {
-                $review->delete();
-            } else {
-                $review->restore();
-            }
-
-            return $review;
-        }
-
-        throw new BusinessException('Loại dữ liệu kiểm duyệt không hợp lệ.', 422);
-    }
-}
diff --git a/BE/app/Services/QuizService.php b/BE/app/Services/QuizService.php
deleted file mode 100644
index a84a77a..0000000
--- a/BE/app/Services/QuizService.php
+++ /dev/null
@@ -1,147 +0,0 @@
-<?php
-
-namespace App\Services;
-
-use App\Exceptions\BusinessException;
-use App\Models\Quiz;
-use App\Models\QuizAttempt;
-use App\Models\QuizAnswer;
-use App\Models\QuizQuestion;
-use App\Models\Enrollment;
-use App\Models\User;
-use Illuminate\Support\Facades\DB;
-use Illuminate\Database\QueryException;
-
-class QuizService
-{
-    public function storeAttempt(int $quizId, array $data, User $user): QuizAttempt
-    {
-        // 1. Tìm quiz status=published và course/lesson liên quan.
-        $quiz = Quiz::with(['course', 'lesson'])->find($quizId);
-
-        if (!$quiz) {
-            throw new BusinessException('Không tìm thấy dữ liệu.', 404);
-        }
-
-        // Check quiz status
-        if ($quiz->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        // Check course status
-        $course = $quiz->course;
-        if (!$course || $course->status !== 'published') {
-            throw new BusinessException('Nội dung chưa khả dụng.', 403);
-        }
-
-        // Check lesson status if linked to a lesson
-        if ($quiz->lesson_id) {
-            $lesson = $quiz->lesson;
-            if (!$lesson || $lesson->status !== 'published') {
-                throw new BusinessException('Nội dung chưa khả dụng.', 403);
-            }
-        }
-
-        // 2. Kiểm tra learner có enrollment active/completed trong quiz.course_id.
-        $enrollment = Enrollment::where('user_id', $user->id)
-            ->where('course_id', $quiz->course_id)
-            ->whereIn('status', ['active', 'completed'])
-            ->first();
-
-        if (!$enrollment) {
-            throw new BusinessException('Bạn chưa có quyền truy cập nội dung này.', 403);
-        }
-
-        // 3. Validate options and questions:
-        // answers: array of { question_id, option_id }
-        $answers = $data['answers'];
-        
-        $questions = QuizQuestion::where('quiz_id', $quiz->id)->with('options')->get();
-        $questionsMap = $questions->keyBy('id');
-
-        $answeredQuestionIds = [];
-        foreach ($answers as $ans) {
-            $qId = $ans['question_id'];
-            $optId = $ans['option_id'];
-
-            if (in_array($qId, $answeredQuestionIds)) {
-                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
-            }
-            $answeredQuestionIds[] = $qId;
-
-            $question = $questionsMap->get($qId);
-            if (!$question) {
-                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
-            }
-
-            $option = $question->options->firstWhere('id', $optId);
-            if (!$option) {
-                throw new BusinessException('Đáp án không hợp lệ cho câu hỏi.', 422);
-            }
-        }
-
-        // 4. Create attempt inside a database transaction
-        return DB::transaction(function () use ($quiz, $user, $answers, $questionsMap) {
-            $maxAttemptNumber = QuizAttempt::where('quiz_id', $quiz->id)
-                ->where('user_id', $user->id)
-                ->max('attempt_number');
-
-            $attemptNumber = ($maxAttemptNumber ?: 0) + 1;
-
-            $totalScore = (float) $questionsMap->sum('score');
-            $scoreEarned = 0.0;
-
-            $answersToInsert = [];
-            foreach ($answers as $ans) {
-                $qId = $ans['question_id'];
-                $optId = $ans['option_id'];
-
-                $question = $questionsMap->get($qId);
-                $option = $question->options->firstWhere('id', $optId);
-
-                $isCorrect = (bool) $option->is_correct;
-                $questionScore = (float) $question->score;
-                $earned = $isCorrect ? $questionScore : 0.0;
-
-                if ($isCorrect) {
-                    $scoreEarned += $questionScore;
-                }
-
-                $answersToInsert[] = [
-                    'question_id' => $qId,
-                    'option_id' => $optId,
-                    'is_correct' => $isCorrect,
-                    'score_earned' => $earned,
-                ];
-            }
-
-            $passed = $scoreEarned >= (float) $quiz->passing_score;
-
-            try {
-                $attempt = QuizAttempt::create([
-                    'quiz_id' => $quiz->id,
-                    'user_id' => $user->id,
-                    'attempt_number' => $attemptNumber,
-                    'score' => $scoreEarned,
-                    'total_score' => $totalScore,
-                    'passed' => $passed,
-                    'status' => 'submitted',
-                    'started_at' => now(),
-                    'submitted_at' => now(),
-                ]);
-
-                foreach ($answersToInsert as $ansData) {
-                    $ansData['attempt_id'] = $attempt->id;
-                    QuizAnswer::create($ansData);
-                }
-
-                return $attempt;
-            } catch (QueryException $e) {
-                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
-                    throw new BusinessException('Bạn đã nộp attempt này.', 409);
-                }
-                throw $e;
-            }
-        });
-    }
-}
diff --git a/BE/app/Services/Report/ReportService.php b/BE/app/Services/Report/ReportService.php
index 925dc94..778fb1a 100644
--- a/BE/app/Services/Report/ReportService.php
+++ b/BE/app/Services/Report/ReportService.php
@@ -403,7 +403,7 @@ public function getInactiveLearnersReport(int $instructorId, array $filters)
                 DB::raw('COALESCE(lp.max_lesson_accessed_at, enrollments.last_accessed_at, enrollments.enrolled_at, enrollments.created_at) as last_activity_at')
             )
             ->where('courses.instructor_id', $instructorId)
-            ->whereNull('courses.deleted_at');
+            ;
 
         // Base condition for "bỏ dở": not completed
         $query->where(function ($q) {
```