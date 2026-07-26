# BÁO CÁO NGHIỆM THU KIỂM TRA & XÁC NHẬN GOOGLE OAUTH REDIRECT URI

> [!NOTE]
> URL đăng nhập Google OAuth 2.0 thực tế do Backend khởi tạo đã được truy xuất trực tiếp từ mã nguồn runtime và xác nhận chứa chính xác **`redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fauth%2Fgoogle%2Fcallback`**. Route `GET /auth/google/callback` đã được khai báo và kiểm chứng tồn tại 100% trên hệ thống Backend Laravel.

---

## 1. DỮ LIỆU KẾT XUẤT THỰC TẾ TỪ RUNTIME BACKEND

Khi thực thi `GET /api/auth/google/redirect` hoặc `/auth/google/redirect`, Backend trả về URL đăng nhập Google chính xác như sau:

```
https://accounts.google.com/o/oauth2/v2/auth?client_id=1014575880179-5vi6ls48psmaon9hqapicpj38ms6q8du.apps.googleusercontent.com&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fauth%2Fgoogle%2Fcallback&response_type=code&scope=openid+email+profile&prompt=select_account
```

### 🔍 Giải Mã Tham Số Thực Tế:
- **`client_id` Đang Được Dùng**: `10145758...rcontent.com` *(Tối đa 8 ký tự đầu & 8 ký tự cuối: `1014575880179-5vi6ls48psmaon9hqapicpj38ms6q8du.apps.googleusercontent.com`)*.
- **`redirect_uri` Encoded Thực Tế**: `http%3A%2F%2Flocalhost%3A8000%2Fauth%2Fgoogle%2Fcallback`
- **`redirect_uri` Decoded Thực Tế**: `http://localhost:8000/auth/google/callback`
- **Callback Route Thật Trên Backend**: `GET /auth/google/callback` (`App\Http\Controllers\AuthController@googleCallback`).

---

## 2. KẾT QUẢ TÌM KIẾM VÀ CHUẨN HÓA MÃ NGUỒN (AUDIT CODE)

1. **Nguồn Nạp Cấu Hình Duy Nhất**:
   - `config/services.php`: Đọc trực tiếp từ `env('GOOGLE_REDIRECT_URI')`.
   - `app/Http/Controllers/AuthController.php`: Đọc duy nhất qua `config('services.google.redirect', 'http://localhost:8000/auth/google/callback')`.
   - Loại bỏ hoàn toàn mọi hard-code hoặc fallback chứa `127.0.0.1` hay `/api/auth/google/callback`.

2. **Khai Báo Web Route Thật**:
   - Đã thêm route trực tiếp trong `routes/web.php`:
     ```php
     Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
     Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
     ```
   - Xác nhận bằng `php artisan route:list`:
     ```
     GET|HEAD auth/google/callback .. AuthController@googleCallback
     GET|HEAD auth/google/redirect .. AuthController@googleRedirect
     ```

3. **Làm Sạch Cache**:
   - Đã chạy `php artisan optimize:clear` thành công.

---

## 3. THÔNG TIN CẤU HÌNH CẦN THAO TÁC TRÊN GOOGLE CLOUD CONSOLE

> [!WARNING]
> **CẢNH BÁO QUAN TRỌNG VỀ OAUTH CLIENT ID**:
> Bạn phải cấu hình **chính xác OAuth Client ID** tương ứng với `client_id` đang gửi (`10145758...rcontent.com`). Nếu thêm Redirect URI vào một OAuth Client ID khác (ví dụ: Client ID dành cho Web Client cũ hoặc Client ID ứng dụng di động), Google vẫn sẽ báo lỗi `400: redirect_uri_mismatch`.

### 📍 Các Bước Thao Tác:
1. Đăng nhập vào [Google Cloud Console Credentials](https://console.cloud.google.com/apis/credentials).
2. Tìm đúng **OAuth 2.0 Client ID** có Client ID là `1014575880179-5vi6ls48psmaon9hqapicpj38ms6q8du.apps.googleusercontent.com`.
3. Trong mục **Authorized JavaScript origins**, thêm:
   - `http://localhost:3000`
   - `http://localhost:8000`
4. Trong mục **Authorized redirect URIs**, thêm chính xác URL:
   - `http://localhost:8000/auth/google/callback`

---

## 4. KẾT QUẢ AUTOMATED TESTS

```bash
php artisan test --filter=AuthTest
# Tests: 9 passed (28 assertions) - 100% PASS

npx tsc --noEmit
# Exit code: 0 (0 errors)
```
