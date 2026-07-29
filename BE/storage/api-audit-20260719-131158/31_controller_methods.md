# CONTROLLER METHODS SUMMARY


## app\Http\Controllers\AdminController.php

- L15: public function __construct(
- L20: public function orders(AdminOrderQueryRequest $request): JsonResponse
- L29: public function banners(Request $request, mixed $id = null): JsonResponse
- L120: public function categories(\App\Http\Requests\Admin\CategoryQueryRequest $request): JsonResponse
- L130: public function showCategory(int $id): JsonResponse
- L140: public function storeCategory(\App\Http\Requests\Admin\StoreCategoryRequest $request): JsonResponse
- L150: public function updateCategory(\App\Http\Requests\Admin\UpdateCategoryRequest $request, int $id): JsonResponse
- L160: public function deleteCategory(int $id): JsonResponse
- L170: public function courses(\App\Http\Requests\Admin\AdminCourseQueryRequest $request): JsonResponse
- L180: public function showCourse(int $id): JsonResponse
- L190: public function updateCourse(\App\Http\Requests\Admin\UpdateAdminCourseRequest $request, int $id): JsonResponse
- L200: public function users(\App\Http\Requests\Admin\UserQueryRequest $request): JsonResponse
- L210: public function showUser(int $id): JsonResponse
- L220: public function storeUser(\App\Http\Requests\Admin\StoreUserRequest $request): JsonResponse
- L230: public function updateUser(\App\Http\Requests\Admin\UpdateUserRequest $request, int $id): JsonResponse
- L240: public function deleteUser(Request $request, int $id): JsonResponse
- L250: public function roles(): JsonResponse

## app\Http\Controllers\AdminCourseApprovalController.php

- L11: public function __construct(
- L16: public function approve(int $courseId): JsonResponse
- L27: public function reject(RejectCourseRequest $request, int $courseId): JsonResponse

## app\Http\Controllers\AdminCreditPackageController.php

- L12: public function index(): JsonResponse
- L25: public function store(StoreCourseCreditPackageRequest $request): JsonResponse
- L43: public function update(UpdateCourseCreditPackageRequest $request, int $packageId): JsonResponse
- L58: public function destroy(int $packageId): JsonResponse

## app\Http\Controllers\AdminInstructorCreditController.php

- L13: public function __construct(
- L18: public function show(int $instructorId): JsonResponse
- L28: public function transactions(Request $request, int $instructorId): JsonResponse
- L41: public function adjust(AdjustInstructorCreditRequest $request, int $instructorId): JsonResponse

## app\Http\Controllers\AdminModerationController.php

- L20: public function __construct(
- L25: public function pendingCourses(PendingCourseQueryRequest $request): JsonResponse
- L39: public function approveCourse(ApproveCourseRequest $request, mixed $id): JsonResponse
- L61: public function rejectCourse(RejectcourseRequest $request, mixed $id): JsonResponse
- L84: public function moderateItem(ModerateItemRequest $request, mixed $id): JsonResponse

## app\Http\Controllers\AuthController.php

- L22: public function __construct(
- L27: public function register(RegisterLearnerRequest $request): JsonResponse
- L32: public function registerLearner(RegisterLearnerRequest $request): JsonResponse
- L46: public function registerInstructor(RegisterInstructorRequest $request): JsonResponse
- L61: public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
- L81: public function resendVerifyEmail(ResendVerifyEmailRequest $request): JsonResponse
- L91: public function login(LoginRequest $request): JsonResponse
- L104: public function googleLogin(GoogleLoginRequest $request): JsonResponse
- L117: public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
- L127: public function resetPassword(ResetPasswordRequest $request): JsonResponse
- L137: public function logout(Request $request): JsonResponse

## app\Http\Controllers\AuthSessionController.php

- L13: public function __construct(
- L17: public function index(ListSessionRequest $request): JsonResponse
- L29: public function revoke(RevokeSessionRequest $request): JsonResponse
- L45: public function logoutAll(LogoutAllRequest $request): JsonResponse

## app\Http\Controllers\CatalogController.php

- L20: public function __construct(
- L25: public function home(CatalogListRequest $request): JsonResponse
- L37: public function categories(CatalogListRequest $request): JsonResponse
- L49: public function searchCourses(CourseSearchRequest $request): JsonResponse
- L61: public function sortCourses(CourseSortRequest $request): JsonResponse
- L73: public function featuredCourses(CatalogListRequest $request): JsonResponse
- L85: public function latestCourses(CatalogListRequest $request): JsonResponse
- L97: public function featuredInstructors(CatalogListRequest $request): JsonResponse
- L109: public function searchSuggestions(SearchSuggestionRequest $request): JsonResponse
