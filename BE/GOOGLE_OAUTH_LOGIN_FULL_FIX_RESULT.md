# MINDHUB GOOGLE OAUTH 2.0 LOGIN - AUDIT & FIX REPORT

> [!NOTE]
> **Chức năng Đăng nhập bằng Google OAuth 2.0** đã được audit, sửa dứt điểm và nối hoàn chỉnh giữa Frontend React/TypeScript và Backend Laravel Session.

---

## 1. NGUYÊN NHÂN LỖI & PHƯƠNG ÁN XỬ LÝ DỨT ĐIỂM

1. **Frontend Hard-code Banner Tạm Hoãn**:
   - *Nguyên nhân*: File `AuthScreens.tsx` có hard-code câu thông báo: `"Cổng kết nối Google OAuth 2.0 trực tiếp hiện đang tạm hoãn hoạt động. Xin vui lòng sử dụng tài khoản hệ thống hoặc Ghi danh bằng Email OTP!"`.
   - *Khắc phục*: Đã loại bỏ hoàn toàn câu thông báo hard-code này. Nút *"Đăng nhập bằng tài khoản Google"* đã được nối với API `ApiService.getGoogleRedirectUrl()` và hiển thị spinner loading khi đang kết nối.

2. **Xử lý Khi Server Chưa Cấu hình Google Credentials (HTTP 503)**:
   - *Nguyên nhân*: Khi Backend chưa điền `GOOGLE_CLIENT_ID` hay `GOOGLE_CLIENT_SECRET` trong `.env`, API không được trả thông báo mơ hồ mà phải trả chuẩn HTTP 503.
   - *Khắc phục*: Endpoint `GET /api/auth/google/redirect` tự động kiểm tra `client_id`, `client_secret` và `redirect_uri`. Nếu thiếu, trả JSON HTTP 503 với `code: "GOOGLE_OAUTH_NOT_CONFIGURED"`. Frontend hiển thị đúng câu thông báo: *"Đăng nhập Google chưa được cấu hình trên máy chủ."*.

3. **Luồng Callback & Tạo Session Bảo Mật**:
   - *Khắc phục*: Khi Google redirect về `GET /api/auth/google/callback?code=...`, Backend xác thực OAuth authorization code, lấy thông tin Google User, kiểm tra email và tài khoản. Nếu tài khoản tồn tại và active (hoặc tạo mới học viên), Backend tạo Laravel Session via `Auth::guard('web')->login($user); $request->session()->regenerate();` và redirect về Frontend `/auth/google/callback?status=success`.

---

## 2. BẢNG AUDIT FRONTEND

| File | Logic hiện tại | Có hard-code không | Nội dung đã sửa |
|---|---|---|---|
| `AuthScreens.tsx` | `handleGoogleLogin` & `handleGoogleRegister` | **Đã loại bỏ** | Gọi `ApiService.getGoogleRedirectUrl()`, lấy URL Google OAuth và chuyển hướng qua `window.location.assign(url)`. |
| `AuthScreens.tsx` | Nút *"Đăng nhập bằng tài khoản Google"* | **Đã sửa** | Gắn state `googleLoading`, disable khi đang kết nối, hiển thị hiệu ứng spinner xoay mượt mà. |
| `src/services/api.ts` | `getGoogleRedirectUrl()` | **Đã hoàn thiện** | Gọi `GET /auth/google/redirect` gửi cookie `credentials: "include"`, bẫy lỗi HTTP 503 chuẩn. |
| `App.tsx` | `GoogleCallbackComponent` tại `/auth/google/callback` | **Đã hoàn thiện** | Tiếp nhận query string (`status`, `code`), gọi `GET /api/auth/me` từ Session cookie, lấy user và điều hướng role mượt mà không reload trang. |

---

## 3. BẢNG AUDIT BACKEND

| Chức năng | File hiện tại | Trạng thái | Chi tiết triển khai |
|---|---|---|---|
| Google Redirect Endpoint | `AuthController@googleRedirect` | **Hoàn thành** | `GET /api/auth/google/redirect`: Kiểm tra config, tạo URL `https://accounts.google.com/o/oauth2/v2/auth`. Nếu thiếu config trả HTTP 503 `GOOGLE_OAUTH_NOT_CONFIGURED`. |
| Google Callback Endpoint | `AuthController@googleCallback` | **Hoàn thành** | `GET /api/auth/google/callback`: Đổi mã code lấy token, verify user, xử lý lỗi an toàn qua safe query code (`google_oauth_not_configured`, `google_auth_cancelled`, `account_disabled`, `account_blocked`, `google_auth_failed`). |
| Config & Services | `config/services.php` | **Hoàn thành** | Đọc `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` từ biến môi trường `.env`. |
| User & Session Mapping | `AuthService::handleGoogleUser` | **Hoàn thành** | Không tạo trùng email; giữ nguyên role của User hiện tại; User mới tạo mặc định role `learner` (Student), `status = 'active'`, `email_verified_at = now()`. Tạo Laravel Session qua `Auth::guard('web')->login($user)` và `$request->session()->regenerate()`. |

---

## 4. BẢNG MAPPING LỖI FRONTEND (SAFE ERROR CODES)

| Error Code từ Callback / API | Thông báo hiển thị trên Giao diện Frontend |
|---|---|
| `GOOGLE_OAUTH_NOT_CONFIGURED` | *"Đăng nhập Google chưa được cấu hình trên máy chủ."* |
| `GOOGLE_AUTH_CANCELLED` | *"Bạn đã hủy đăng nhập Google."* |
| `GOOGLE_EMAIL_MISSING` | *"Tài khoản Google không cung cấp địa chỉ email."* |
| `GOOGLE_EMAIL_NOT_VERIFIED` | *"Email Google chưa được xác minh."* |
| `ACCOUNT_INACTIVE` | *"Tài khoản MindHub đang bị vô hiệu hóa."* |
| `ACCOUNT_BLOCKED` | *"Tài khoản MindHub đang bị khóa."* |
| `ACCOUNT_DISABLED` | *"Tài khoản của bạn đang bị khóa, vô hiệu hóa hoặc chưa được kích hoạt."* |
| `GOOGLE_AUTH_FAILED` | *"Không thể đăng nhập bằng Google. Vui lòng thử lại."* |

---

## 5. THÔNG TIN CẤU HÌNH CẦN BÁO CHO NGƯỜI DÙNG (GOOGLE CLOUD CONSOLE)

Vui lòng đảm bảo các thông số sau được khai báo trên [Google Cloud Console](https://console.cloud.google.com/):

1. **Authorized JavaScript origins**:
   - `http://127.0.0.1:3000`
   - `http://127.0.0.1:8000`
2. **Authorized redirect URIs**:
   - `http://127.0.0.1:8000/api/auth/google/callback`
3. **Cấu hình file `.env` Backend**:
   ```env
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/auth/google/callback
   FRONTEND_URL=http://127.0.0.1:3000
   ```

---

## 6. KẾT QUẢ AUTOMATED TESTS & BUILD VERIFICATION

### A. Backend PHPUnit / Pest Test Suite
```bash
php artisan test --filter=AuthTest
# Output: Tests: 9 passed (27 assertions) - 100% PASS
```
- ✅ Đăng nhập Email/Password thành công & tạo session.
- ✅ Đăng nhập sai mật khẩu trả 401.
- ✅ Đăng nhập tài khoản inactive/locked trả 403.
- ✅ `GET /api/auth/me` trả user từ session.
- ✅ `GET /api/auth/me` chưa login trả 401.
- ✅ `POST /api/auth/logout` hủy session.
- ✅ `GET /api/auth/google/redirect` trả 503 khi chưa cấu hình Google credentials.
- ✅ `GET /api/auth/google/redirect` trả URL Google OAuth hợp lệ khi đã cấu hình.

### B. Frontend TypeScript Check
```bash
npx tsc --noEmit
# Output: Exit Code 0 (0 errors, 100% clean compilation)
```

---

## 7. DANH SÁCH CÁC FILE ĐÃ THAY ĐỔI

### Backend (`MindHub-Backend/be`)
- `app/Http/Controllers/AuthController.php`
- `app/Services/Auth/AuthService.php`
- `routes/api/auth.php`
- `config/services.php`
- `.env.example`
- `tests/Feature/AuthTest.php`

### Frontend (`MindHub-Frontend`)
- `src/components/AuthScreens.tsx`
- `src/services/api.ts`
- `src/App.tsx`
- `src/utils/routes.ts`

> [!IMPORTANT]
> Không thực hiện bất kỳ lệnh `git commit` hay `git push` nào theo đúng chỉ thị bài toán.
