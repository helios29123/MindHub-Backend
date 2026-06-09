# Feedback: Kiểm tra Bảo mật & Logic — MindHub Backend

> Ngày kiểm tra: 2026-06-09  
> Scope: `MindHub-Backend/BE`  
> Phương pháp: Đọc code, grep tĩnh — không chạy ứng dụng  
> Không có sửa đổi nào được thực hiện trong quá trình review này.

---

## TÓM TẮT NHANH

| Mức độ | Số lượng |
|--------|----------|
| 🔴 CRITICAL | 5 |
| 🟠 HIGH | 4 |
| 🟡 MEDIUM | 5 |
| 🔵 LOW | 5 |

---

## 🔴 CRITICAL — Cần sửa ngay, hệ thống sẽ không hoạt động đúng

---

### C1. `AuthSession` model bị map nhầm bảng — runtime sẽ crash

**File:** `app/Models/AuthSession.php:10`

```php
protected $table = 'sessions';
```

Model `AuthSession` dùng bảng `sessions`, nhưng migration `0001_01_01_000000_create_users_table.php` tạo bảng `sessions` với schema của **Laravel Session Driver** (cột `id` string, `payload` longText, `last_activity` integer). Bảng này **không có** các cột mà `AuthSession` cần: `refresh_token_hash`, `device_name`, `expires_at`, `revoked_at`.

Kết quả: mọi thao tác liên quan đến đăng nhập, xác thực token đều sẽ fail vì query đến cột không tồn tại. Chưa có migration nào tạo bảng `auth_sessions` riêng.

---

### C2. `AuthController` thiếu method `logout` và `me`

**File:** `routes/api/auth.php:18-19`, `app/Http/Controllers/Api/AuthController.php`

```php
// routes/api/auth.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
```

Hai method `logout()` và `me()` **không tồn tại** trong `AuthController`. Gọi `POST /api/logout` hoặc `GET /api/me` (qua prefix này) sẽ throw `BadMethodCallException`.

---

### C3. `ResetPasswordRequest` dùng tên method sai — validation không bao giờ chạy

**File:** `app/Http/Requests/Auth/ResetPasswordRequest.php:7,15`

```php
public function rule() {  // ← sai, phải là rules()
    ...
}
public function message() {  // ← sai, phải là messages()
    ...
}
```

Laravel `FormRequest` gọi `rules()` và `messages()`, không phải `rule()` và `message()`. Do đó endpoint `POST /api/reset-password` **bỏ qua hoàn toàn validation**: nhận email rỗng, token rỗng, password ngắn hơn 8 ký tự, password không khớp confirm — đều không bị từ chối ở tầng validation.

---

### C4. `GoogleTokenVerifier` không xác thực thực sự với Google

**File:** `app/Services/GoogleTokenVerifier.php:11-12`

```php
$decodedJson = base64_decode($googleToken, true);
$payload = json_decode($decodedJson, true);
```

Toàn bộ "xác thực" chỉ là decode base64. Bất kỳ ai cũng có thể giả mạo đăng nhập Google bằng cách encode một JSON tùy ý:

```
base64_encode('{"provider_id":"any_id","email":"victim@example.com","full_name":"Hacker"}')
```

Route Google Login hiện đang bị comment, nhưng khi mở ra mà chưa sửa thì đây là lỗ hổng **Account Takeover**.

---

### C5. `APP_KEY` rỗng — token signing dùng empty string

**File:** `.env:3`

```
APP_KEY=
```

`AccessTokenService::getSigningKey()` lấy `config('app.key')`. Khi key rỗng, HMAC-SHA256 ký token bằng chuỗi rỗng. Kẻ tấn công có thể tự tạo access token hợp lệ cho bất kỳ `user_id` nào.

---

## 🟠 HIGH — Ảnh hưởng nghiêm trọng đến chức năng / bảo mật

---

### H1. `UserProfileController` thiếu `use` statements — PHP fatal error

**File:** `app/Http/Controllers/UserProfileController.php:28,40,52`

```php
public function updateMe(UpdateMeRequest $request): JsonResponse { ... }
public function changePassword(ChangePasswordRequest $request): JsonResponse { ... }
// ...
} catch (BusinessException $exception) { ... }
```

Ba class `UpdateMeRequest`, `ChangePasswordRequest`, `BusinessException` được dùng nhưng **không có dòng `use`** tương ứng ở đầu file. Gọi `PATCH /api/users/me` hoặc `PATCH /api/users/me/password` sẽ throw `Error: Class "App\Http\Controllers\UpdateMeRequest" not found`.

---

### H2. Không có rate limiting trên endpoint xác thực

**File:** `routes/api/auth.php`, `routes/api.php`

Các endpoint sau không áp dụng middleware `throttle`:

- `POST /api/login` — có thể brute-force mật khẩu
- `POST /api/forgot-password` — có thể spam email reset
- `POST /api/reset-password` — có thể brute-force token 64 ký tự

Laravel cung cấp sẵn `throttle:60,1` hoặc `RateLimiter` trong `RouteServiceProvider`, chưa được áp dụng.

---

### H3. Mismatch sort values giữa Request validation và Repository

**File:** `app/Http/Requests/Catalog/CourseSearchRequest.php:25`, `app/Repositories/Catalog/CatalogCourseRepository.php:151-158`

Request validation chấp nhận:
```
latest, price_asc, price_desc, rating_desc, best_selling, featured
```

Repository xử lý:
```
popular, featured, rating, price_asc, price_desc, newest (default)
```

Kết quả: `rating_desc` và `best_selling` đều rơi vào `default` (sort by `published_at`). `newest` không nằm trong whitelist nhưng lại là giá trị mặc định của repository.

---

### H4. Hai hệ thống auth song song, không nhất quán

**File:** `routes/api/auth.php:17`, `routes/api/user.php:6`, `routes/api/instructor.php:6`

- `auth.php` dùng `auth:sanctum` cho `logout` và `me`
- `user.php` dùng `auth.session` (custom middleware) cho `users/me`, `users/me/password`
- `instructor.php` dùng `auth.session` cho `instructor/courses`

`auth:sanctum` và `auth.session` là hai guard khác nhau, user được set khác nhau. Một request qua `auth:sanctum` không thể dùng session data từ `auth.session` và ngược lại.

---

## 🟡 MEDIUM — Vấn đề logic, có thể gây lỗi hoặc rủi ro bảo mật

---

### M1. `changePassword` không kiểm tra user OAuth (không có password)

**File:** `app/Services/User/UserProfileService.php:50`

```php
if (! Hash::check($validatedData['current_password'], $user->password_hash)) {
```

User đăng ký qua Google có `password_hash = null`. `Hash::check($any, null)` trong Laravel trả về `false`, nên sẽ luôn throw `BusinessException('Mật khẩu hiện tại không đúng.')`. Tuy không crash, nhưng message lỗi sai: đúng hơn phải là "Tài khoản này đăng nhập bằng OAuth, không có mật khẩu".

---

### M2. `forgotPassword` không kiểm tra trạng thái tài khoản

**File:** `app/Services/AuthService.php:126-150`

```php
$user = $this->userRepository->findByEmail($data['email']);
if (! $user) { return [...null...]; }
// ← không check $user->isActive()
$plainResetToken = Str::random(64);
```

Một tài khoản bị `locked` hoặc `inactive` vẫn nhận được token reset mật khẩu. Sau khi reset, tài khoản vẫn bị lock — nhưng đây là luồng không mong đợi và tạo ra confusion cho UX và security.

---

### M3. `reset_token` bị trả về trong response khi debug mode bật

**File:** `app/Services/AuthService.php:147-150`

```php
return [
    'reset_token' => config('app.debug') ? $plainResetToken : null,
    'expires_at'  => config('app.debug') ? $expiresAt->toISOString() : null,
];
```

Khi `APP_DEBUG=true` (đang bật trong `.env`), token reset mật khẩu plain text được trả về trong HTTP response. Bất kỳ ai thấy response này (qua network log, browser, middleman) đều có thể reset mật khẩu người khác mà không cần email.

---

### M4. `EnsureUserIsActive` middleware đăng ký nhưng không dùng ở đâu

**File:** `bootstrap/app.php:29`, toàn bộ `routes/`

```php
'active.user' => EnsureUserIsActive::class,
```

Middleware được alias là `active.user` nhưng không có route nào apply. `AuthenticateSessionToken` đã kiểm tra `$user->isActive()` nên chức năng không bị thiếu, nhưng đây là dead code gây confusion.

---

### M5. `CoursePublicService::showInstructor` query user 2 lần

**File:** `app/Services/CoursePublicService.php:211-240`

```php
$user = User::find($id);          // query 1
// ... check role/status
$instructor = User::query()...->first(); // query 2 — full eager load
```

Query đầu chỉ dùng để check `role` và `status`, rồi query lại toàn bộ. Có thể gộp vào một query duy nhất để tiết kiệm 1 DB hit mỗi request.

---

## 🔵 LOW — Code smell, inconsistency, nên cải thiện

---

### L1. Hai class `ApiResponse` song song với signature khác nhau

**Files:**
- `app/Helpers/ApiResponse.php` — `success(string $message, mixed $data, int $statusCode)`
- `app/Support/ApiResponse.php` — `success(mixed $data, string $message, int $status)`

Cùng tên, cùng namespace pattern nhưng **thứ tự tham số ngược nhau**. Các controller dùng lẫn lộn (`AuthController` dùng `Helpers`, `CatalogController` dùng `Support`, `UserProfileController` dùng `Support`). Nếu nhầm import sẽ gây response sai cấu trúc mà không throw exception.

---

### L2. `UserRepository::findByOAuthProviderId` dùng LIKE trên JSON string

**File:** `app/Repositories/UserRepository.php:31-36`

```php
$providerPattern = '%"provider":"' . addcslashes($provider, '%_\\') . '"%';
```

Tìm kiếm JSON bằng `LIKE` có thể trả false positive nếu giá trị `provider_id` chứa chuỗi giống JSON. Nên dùng MySQL `JSON_EXTRACT` hoặc lưu `oauth_provider`/`oauth_provider_id` thành 2 cột riêng.

---

### L3. Migration `cat_users` tạo bảng rỗng vô nghĩa

**File:** `database/migrations/2026_06_09_053637_create_cat_users_table.php`

```php
Schema::create('cat_users', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
});
```

Bảng `cat_users` chỉ có `id` và timestamps, không được dùng ở bất kỳ đâu trong code. Có vẻ là migration placeholder chưa hoàn thiện hoặc đã lỗi thời.

---

### L4. `previewLesson` trả về 403 thay vì 404 cho lesson `draft`

**File:** `app/Services/CoursePublicService.php:157-163`

```php
if ($lesson->status === 'hidden') {
    throw new BusinessException('...', 404); // 404 cho hidden
}
if (!$lesson->is_preview || $lesson->status !== 'published') {
    throw new BusinessException('Bài học này không được xem trước.', 403); // 403 cho draft
}
```

Lesson có `status = 'draft'` được check `hidden` trước (không match) rồi rơi vào check `!= published` → trả 403 với message "không được xem trước". Nên trả 404 để không tiết lộ sự tồn tại của resource.

---

### L5. `ChangePasswordRequest` không extend `BaseApiRequest`

**File:** `app/Http/Requests/User/ChangePasswordRequest.php:11`

```php
final class ChangePasswordRequest extends FormRequest
```

Tất cả các Request khác extend `BaseApiRequest` (để tự động có `failedValidation` chuẩn format), nhưng `ChangePasswordRequest` extend thẳng `FormRequest` và tự implement `failedValidation`. Không sai về chức năng nhưng inconsistent với pattern chung của project.

---

## Checklist ưu tiên xử lý

- [ ] **C5** — Tạo `APP_KEY` (`php artisan key:generate`)
- [ ] **C1** — Tạo migration `auth_sessions` riêng, đổi `$table` trong `AuthSession`
- [ ] **C3** — Đổi `rule()` → `rules()`, `message()` → `messages()` trong `ResetPasswordRequest`
- [ ] **C2** — Thêm method `logout()` và `me()` vào `AuthController`, hoặc đổi route sang controller đúng
- [ ] **H1** — Thêm `use` statements còn thiếu trong `UserProfileController`
- [ ] **H2** — Thêm `throttle` middleware cho auth routes
- [ ] **H3** — Đồng bộ sort values giữa `CourseSearchRequest` và `CatalogCourseRepository`
- [ ] **H4** — Thống nhất một hệ thống auth (nên giữ `auth.session`, xóa `auth:sanctum`)
- [ ] **M3** — Xem xét việc trả `reset_token` trong response dù chỉ ở debug
- [ ] **C4** — Thay `GoogleTokenVerifier` bằng xác thực thực sự qua Google OAuth2 API
