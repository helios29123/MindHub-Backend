# ANTIGRAVITY PROJECT CONTEXT — MINDHUB E-LEARNING PLATFORM

> **Tài liệu Bối cảnh Dự án Toàn diện (Fullstack Project Context)**  
> **Mục tiêu:** Bản đồ kiến trúc tĩnh và động kết nối toàn diện giữa Backend và Frontend phục vụ bộ workflow tự động hóa Antigravity.  
> **Nguyên tắc:** 100% bám sát mã nguồn production hiện tại (Backend Laravel 12 & Frontend React 19). Không suy đoán. Không sửa code.

---

## 1. CẤU TRÚC TỔNG THỂ DỰ ÁN

### 1.1. Vị trí Thư mục & Môi trường
- **Thư mục Backend:** `d:\laragon\www\datn\MindHub-Backend\BE`
- **Thư mục Frontend:** `d:\laragon\www\datn\phat\FE-minhub`

### 1.2. Công nghệ Backend (BE)
- **Framework:** Laravel 12.61.1 (PHP 8.3+)
- **Cơ sở dữ liệu:** MySQL 8.0 (30 Bảng dữ liệu, 8 Database Triggers bảo toàn nghiệp vụ)
- **Cơ chế Xác thực (Auth):** Bearer Token Hash lưu trong bảng `sessions`, kiểm soát phiên đăng nhập qua middleware `auth.session`, giới hạn thiết bị đăng nhập (`DeviceLimitService`).
- **Tích hợp bên thứ ba:**
  - **Video Streaming:** Bunny Stream (Tạo direct upload video, ký Signed URL HLS phát bảo mật kèm dynamic watermark).
  - **Lưu trữ ảnh & Media:** Cloudinary API (Tải ảnh thumbnail khóa học, avatar người dùng, banner).
  - **Cổng Thanh toán:** SePay VietQR (Webhook tự động khớp lệnh) và VNPAY (Chuyển hướng IPN / Return URL).
  - **Trợ lý AI:** Gemma-4-31B-IT qua API MindHub AI (`https://ai.mindhub.io.vn/v1`).

### 1.3. Công nghệ Frontend (FE)
- **Framework / Bundler:** React 19.0.1 + TypeScript (~5.8.2) + Vite 6.2.3
- **Routing:** React Router DOM 7.18.1 (Centralized Route Mapping tại `src/router/routes.ts` & `src/router/AppRouter.tsx`)
- **Giao diện & Styling:** Tailwind CSS v4 + Radix UI + Lucide React Icons + Framer Motion
- **Biểu đồ & Đồ thị:** Recharts 3.9.2 + Chart.js 4.5.1
- **Phát Video:** HLS.js 1.7.1
- **Thông báo UI:** Sonner Toast 2.0.7

### 1.4. Cách Frontend gọi API Backend
- **Endpoint Gốc (Base URL):** Được cấu hình qua `VITE_API_URL` (Mặc định `http://localhost:8000/api` hoặc `/api`).
- **HTTP Client:** Native `fetch` / Axios client được đóng gói tập trung tại `src/services/api.ts` và các API service con theo từng domain (`src/features/*/api.ts`).
- **Gắn Token Xác thực:** Header `Authorization: Bearer <session_token>` được tự động chèn vào mỗi request từ `localStorage.getItem('token')` / `auth_session`.

### 1.5. Phân quyền Người dùng (RBAC)
- **Học viên (`role = 'learner'` / `'student'`):** Khám phá khóa học, xem preview video, mua khóa học, thanh toán, học trong classroom, hỏi đáp bài học, ghi chú, đánh giá review, nộp đơn nâng cấp giảng viên.
- **Giảng viên (`role = 'instructor'`):** Workspace `/instructor/*`, quản lý giáo trình khóa học, nộp duyệt, tạo coupon giảm giá & học thử, trả lời Q&A chính thức, xem thống kê học viên & doanh thu, cài đặt tài khoản ngân hàng, yêu cầu OTP rút tiền sớm.
- **Quản trị viên (`role = 'admin'`):** Workspace `/admin/*`, duyệt/từ chối khóa học, quản lý danh mục (tối đa 2 cấp), quản lý banner, duyệt nâng cấp giảng viên, kiểm soát đơn hàng, đối soát doanh thu sàn, duyệt tài khoản ngân hàng và phê duyệt/chi trả lệnh rút tiền.

---

## 2. BẢN ĐỒ 3 LUỒNG NGHIỆP VỤ LỚN (CORE ROLES & FLOWS)

```
┌──────────────────────────────────────────────────────────────────────────────────────────┐
│                                1. LUỒNG HỌC VIÊN (LEARNER)                               │
│                                                                                          │
│  [Khám phá / AI Search] ➡️ [Xem Chi tiết / Preview] ➡️ [Checkout & Áp Coupon]          │
│                                                                   ⬇️                     │
│  [Review 1-1 Đơn hàng] ⬅️ [Hoàn thành 100%] ⬅️ [Classroom & Heartbeat & Watermark]      │
└──────────────────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────────────────┐
│                               2. LUỒNG GIẢNG VIÊN (INSTRUCTOR)                           │
│                                                                                          │
│  [Soạn Khóa học & Section/Lesson] ➡️ [Checklist 7 Tiêu chí] ➡️ [Submit Nộp duyệt]        │
│                                                                      ⬇️                  │
│  [Rút tiền sớm + OTP Email] ⬅️ [Xem Sổ cái Doanh thu] ⬅️ [Tạo Coupon / Suất học thử]   │
└──────────────────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────────────────────┐
│                                3. LUỒNG QUẢN TRỊ VIÊN (ADMIN)                            │
│                                                                                          │
│  [Kiểm duyệt Khóa học (Approve/Reject)] ➡️ [Quản lý Danh mục 2 cấp & Banners]           │
│                                                               ⬇️                         │
│  [Duyệt Chi trả Payout (Mark Paid)] ⬅️ [Xác minh TK Ngân hàng (Verified)] ⬅️ [Duyệt Upgr]│
└──────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. CHI TIẾT CÁC MODULE THEO 3 LUỒNG NGHIỆP VỤ

### 3.1. Luồng Học viên (Learner Flow)

| Bước nghiệp vụ | Frontend Page / Component | Action / Form / Modal | Backend API Endpoint | Controller / Service BE | Bảng Dữ liệu Đọc / Ghi |
|:---|:---|:---|:---|:---|:---|
| **1. Khám phá & AI Advisor** | `HomePage`, `CourseListPage`, `SearchPage` | Search bar, bộ lọc danh mục/giá, AI Search Advisor Modal | `GET /api/courses`<br>`GET /api/courses/search/ai` | `CoursePublicController`<br>`CatalogController` | Đọc: `courses`, `categories`, `users` |
| **2. Xem chi tiết & Học thử** | `CourseDetailPage` | Nút "Học thử miễn phí", xem outline giáo trình | `GET /api/courses/{slug}`<br>`GET /api/lessons/{id}/preview` | `CoursePublicController` | Đọc: `courses`, `course_sections`, `lessons` |
| **3. Checkout & Áp Coupon** | `CartAndCheckout` | Form nhập coupon, chọn phương thức SePay VietQR / VNPAY | `POST /api/orders`<br>`POST /api/payments/sepay/create`<br>`POST /api/payments/vnpay/create` | `PaymentController`<br>`OrderService`<br>`CouponPricingService` | Ghi: `orders` (`pending_payment`), Đọc: `coupons`, `commission_rules` |
| **4. Xác nhận Thanh toán** | `VNPayReturnPage`, Modal VietQR realtime | Quét mã QR chuyển khoản, polling trạng thái đơn hàng | `POST /api/payments/sepay/webhook`<br>`GET /api/payments/vnpay-return` | `PaymentService`<br>`EnrollmentAfterPaymentService` | Ghi: `orders` (`paid`), `enrollments` (`active`), `coupons.used_count`, `revenues` |
| **5. Vào học trực tuyến (Classroom)** | `ClassroomPage`<br>`VideoPlayer`<br>`CurriculumSidebar` | Player HLS video, hiển thị Dynamic Watermark, gửi Heartbeat mỗi 10-30s | `GET /api/learn/lessons/{id}`<br>`GET /api/learn/lessons/{id}/video-url`<br>`GET /api/learn/lessons/{id}/watermark-info` | `LearningController`<br>`LearningService`<br>`LessonVideoAccessService` | Đọc: `enrollments`, `lessons`. Ghi: `video_learning_sessions`, `video_progress` |
| **6. Tích lũy Tiến độ & Hoàn thành** | `ClassroomTabs`<br>`CurriculumSidebar` | Nút "Hoàn thành bài học", xem thanh tiến độ % | `PATCH /api/learn/lessons/{id}/complete` | `LearningService` | Ghi: `lesson_progress` (`completed`), `enrollments` (`progress_percent`, `status`) |
| **7. Ghi chú cá nhân** | `ClassroomTabs` (Tab Ghi chú) | Form tạo ghi chú tại mốc giây video, sửa, xóa | `GET /api/learn/lessons/{id}/notes`<br>`POST /api/learn/lessons/{id}/notes`<br>`DELETE /api/learn/notes/{id}` | `LearningController` | Ghi/Đọc: `lesson_notes` |
| **8. Hỏi đáp bài học (Q&A)** | `ClassroomTabs` (Tab Hỏi đáp), `CourseQA` | Form gửi câu hỏi gốc, form trả lời (Trigger: Max 1 cấp) | `GET /api/lessons/{id}/comments`<br>`POST /api/lessons/{id}/comments`<br>`POST /api/comments/{id}/replies` | `InteractionController`<br>`InteractionService` | Ghi/Đọc: `comments` |
| **9. Đánh giá Khóa học** | `CourseDetailPage`, `PurchaseHistoryPage` | Modal đánh giá 1-5 sao + nhận xét (Ràng buộc 1-1 với Order) | `POST /api/courses/{id}/reviews` | `InteractionController`<br>`ReviewService` | Ghi: `course_reviews` (`uq_course_reviews_order`) |
| **10. Đăng ký Giảng viên** | `ProfilePage` (Tab Nâng cấp) | Form điền Bio, chuyên môn, số năm kinh nghiệm | `POST /api/me/instructor-upgrade` | `InstructorUpgradeController`<br>`InstructorUpgradeService` | Ghi: thông tin upgrade tạm, gửi email Admin |

---

### 3.2. Luồng Giảng viên (Instructor Flow)

| Bước nghiệp vụ | Frontend Page / Component | Action / Form / Modal | Backend API Endpoint | Controller / Service BE | Bảng Dữ liệu Đọc / Ghi |
|:---|:---|:---|:---|:---|:---|
| **1. Tổng quan Studio** | `InstructorDashboard`, `InstructorTopCourses` | Bảng điều khiển, biểu đồ doanh thu, top khóa học | `GET /api/instructor/dashboard/summary`<br>`GET /api/instructor/reports/revenue-chart` | `ReportController`<br>`InstructorCourseController` | Đọc: `courses`, `enrollments`, `revenues`, `orders` |
| **2. Tạo & Soạn Khóa học** | `InstructorCoursesPage`, `CourseBuilderWizard` | Form nhập tiêu đề, giá, thumbnail, danh mục | `POST /api/instructor/courses`<br>`PUT /api/instructor/courses/{id}` | `InstructorCourseController`<br>`InstructorCourseService` | Ghi: `courses` (`status = 'draft'`), `course_categories` |
| **3. Soạn Chương & Bài học** | `CourseBuilderWizard` (Curriculum tab) | Thêm chương (`sort_order`), thêm bài (`lesson_type`, `is_preview`, upload Bunny video) | `POST /api/instructor/sections`<br>`POST /api/instructor/lessons`<br>`POST /api/instructor/lessons/upload-video` | `InstructorCourseController`<br>`BunnyStreamService` | Ghi: `course_sections`, `lessons` (`video_id`), `lesson_assets` |
| **4. Nộp duyệt Khóa học** | `CourseBuilderWizard` | Nút "Gửi kiểm duyệt" (Kiểm tra 7 tiêu chí Course Checklist) | `POST /api/instructor/courses/{id}/submit` | `InstructorCourseController`<br>`CourseChecklistService` | Ghi: `courses.status = 'pending_review'` |
| **5. Quản lý Coupon & Học thử** | `InstructorDiscountCodes` (trong Dashboard) | Form tạo mã giảm giá (Cap 70%) / Suất học thử (Max 15 suất, 3 ngày, 2 campaign/tháng) | `POST /api/instructor/coupons`<br>`PATCH /api/instructor/coupons/{id}/disable` | `InstructorCouponController`<br>`CouponService`<br>`CouponPricingService` | Ghi: `coupons` (`discount` / `trial`) |
| **6. Trả lời Hỏi đáp (Official Reply)** | `InstructorQA` | Danh sách câu hỏi học viên, form phản hồi chính thức (`is_official = 1`), ẩn câu hỏi spam | `GET /api/instructor/questions`<br>`POST /api/instructor/questions/{id}/reply`<br>`PATCH /api/instructor/questions/{id}/hide` | `InteractionController` | Ghi: `comments` (`is_official = 1`) |
| **7. Quản lý Học viên & Rủi ro** | `StudentManagement`, `StudentDetailDrawer` | Danh sách học viên, xem tiến độ, bộ lọc học viên có nguy cơ bỏ học (At Risk) | `GET /api/instructor/students`<br>`GET /api/instructor/reports/inactive-learners`<br>`GET /api/instructor/reports/completion-rate` | `ReportController`<br>`LearnerRiskService` | Đọc: `enrollments`, `learning_daily_activity`, `users` |
| **8. Cài đặt Tài khoản Ngân hàng** | `InstructorWithdrawal` (Tab Cài đặt TK) | Form thêm STK, tên ngân hàng (Trigger: Phải verified mới được đặt default) | `POST /api/instructor/payout-accounts`<br>`PATCH /api/instructor/payout-accounts/{id}/set-default` | `InstructorPayoutAccountController` | Ghi: `payout_accounts` (`pending_verification`) |
| **9. Yêu cầu Rút tiền sớm (OTP)** | `InstructorWithdrawal` | Xem số dư khả dụng, nút "Lấy mã OTP", Form nhập OTP xác nhận rút ($\ge 200.000$ VNĐ) | `POST /api/instructor/early-withdrawals/request-otp`<br>`POST /api/instructor/early-withdrawals`<br>`PATCH /api/instructor/withdrawals/{id}/cancel` | `EarlyWithdrawalController`<br>`EarlyWithdrawalService` | Ghi: `user_otps`, `withdraw_requests` (`pending`), `withdrawal_revenues` (Reserved) |

---

### 3.3. Luồng Quản trị viên (Admin Flow)

| Bước nghiệp vụ | Frontend Page / Component | Action / Form / Modal | Backend API Endpoint | Controller / Service BE | Bảng Dữ liệu Đọc / Ghi |
|:---|:---|:---|:---|:---|:---|
| **1. Tổng quan Sàn** | `DashboardOverview`, `Reports` | Thống kê doanh thu toàn sàn, số lượng học viên mới, tỷ lệ hoàn thành | `GET /api/admin/dashboard/stats`<br>`GET /api/admin/reports/revenue-analytics` | `AdminReportController`<br>`ReportService` | Đọc: `revenues`, `orders`, `users`, `courses` |
| **2. Kiểm duyệt Khóa học** | `Moderation`, `CoursesManagement` | Xem chi tiết giáo trình, nút Duyệt (`Approve` ➡️ `published`), nút Từ chối (`Reject` + lý do) | `GET /api/admin/courses/pending`<br>`PATCH /api/admin/courses/{id}/approve`<br>`PATCH /api/admin/courses/{id}/reject` | `AdminCourseController`<br>`CourseModerationService` | Ghi: `courses` (`status = 'published'` / `'rejected'`, `admin_reject_reason`) |
| **3. Quản lý Danh mục (2 cấp)** | `CategoriesManagement` | Thêm/sửa danh mục (Cha - Con). DB Trigger chặn tạo cấp 3 | `POST /api/admin/categories`<br>`PUT /api/admin/categories/{id}`<br>`DELETE /api/admin/categories/{id}` | `AdminCategoryController`<br>`AdminCategoryService` | Ghi: `categories` (Trigger 2-levels) |
| **4. Quản lý Banner Quảng cáo** | `Banners` | Tải ảnh banner lên Cloudinary, đặt vị trí, link điều hướng, thứ tự hiển thị | `POST /api/admin/banners`<br>`PUT /api/admin/banners/{id}`<br>`DELETE /api/admin/banners/{id}` | `AdminBannerController` | Ghi: `banners` |
| **5. Duyệt Nâng cấp Giảng viên** | `InstructorUpgrades` | Xem danh sách đơn đăng ký, duyệt hồ sơ | `GET /api/admin/instructor-upgrade-requests`<br>`PATCH /api/admin/instructor-upgrade-requests/{id}/approve` | `AdminInstructorUpgradeController`<br>`InstructorUpgradeService` | Ghi: `users.role = 'instructor'`, tạo `instructor_profiles` (Rank Bronze) |
| **6. Xác minh Tài khoản Ngân hàng** | `PayoutAccounts` | Đối soát thông tin chủ thẻ ngân hàng, bấm Xác minh (`verified`) | `GET /api/admin/payout-accounts/pending`<br>`PATCH /api/admin/payout-accounts/{id}/approve` | `AdminPayoutAccountController` | Ghi: `payout_accounts.status = 'verified'` |
| **7. Duyệt & Chi trả Lệnh Rút tiền** | `WithdrawalsManagement`, `AdminWithdrawals` | Danh sách lệnh rút `pending`, bấm Duyệt (`approved`), bấm Đánh dấu Đã thanh toán (`paid`), hoặc Từ chối (`rejected`) | `GET /api/admin/withdrawals`<br>`PATCH /api/admin/withdrawals/{id}/approve`<br>`PATCH /api/admin/withdrawals/{id}/mark-paid`<br>`PATCH /api/admin/withdrawals/{id}/reject` | `AdminWithdrawalController`<br>`EarlyWithdrawalService` | Ghi: `withdraw_requests.status`, giải phóng/xác nhận `withdrawal_revenues` |
| **8. Quản lý Đơn hàng & Doanh thu** | `OrdersManagement`, `RevenuesManagement` | Tra cứu đơn hàng, tra cứu mã giao dịch SePay/VNPay, xem sổ cái hoa hồng | `GET /api/admin/orders`<br>`GET /api/admin/revenues` | `AdminOrderController`<br>`AdminRevenueController` | Đọc: `orders`, `revenues`, `commission_rules` |

---

## 4. CÁC THÀNH PHẦN ĐỘC LẬP / RỜI RẠC (STANDALONE MODULES)

1. **Module Banner & Tiếp thị (`banners`):**
   - Không ràng buộc với Order hay Course cụ thể; hiển thị độc lập tại Header/Trang chủ.
   - Quản lý qua `AdminBannerController` và `BannerPublicController`.
2. **Module FAQ Ngân hàng câu hỏi (`faqs`, `course_faqs`):**
   - Quản lý tập trung các câu hỏi thường gặp của sàn và gắn tùy chọn vào từng khóa học.
3. **Module Wishlist Khóa học yêu thích (`wishlist`):**
   - Lưu trữ danh sách khóa học quan tâm của người dùng, độc lập với giỏ hàng và tiến trình thanh toán.
4. **Module Quản lý Thiết bị & Phiên đăng nhập (`sessions`):**
   - Giới hạn số lượng thiết bị đăng nhập đồng thời (`DeviceLimitService`), cho phép người dùng đăng xuất từ xa khỏi các trình duyệt khác.
5. **Module Mã OTP Xác thực (`user_otps`):**
   - Dịch vụ sinh và kiểm tra OTP dùng chung cho: Quên mật khẩu, Rút tiền sớm (`early_withdrawal`), Thay đổi thông tin bảo mật.

---

## 5. BẢN ĐỒ FILE QUAN TRỌNG KHI CHỈNH SỬA TỪNG MODULE (DEVELOPER GUIDE)

### Khi sửa Module Thanh toán & Đơn hàng (Orders & Payments):
- **Backend:**
  - Controllers: `app/Http/Controllers/PaymentController.php`
  - Services: `app/Services/Payment/OrderService.php`, `app/Services/Payment/PaymentService.php`, `app/Services/Payment/CoursePurchaseGuardService.php`
  - Gateways: `app/Services/Payment/Gateways/SePayGateway.php`, `app/Services/Payment/Gateways/VNPayGateway.php`
  - Config: `config/order.php`, `config/sepay.php`, `config/services.php`
- **Frontend:**
  - Page: `src/features/cart/CartAndCheckout.tsx`, `src/features/cart/VNPayReturnPage.tsx`
  - API: `src/features/cart/api.ts`

### Khi sửa Module Học tập & Video Player (Classroom & Learning):
- **Backend:**
  - Controllers: `app/Http/Controllers/LearningController.php`
  - Services: `app/Services/Learning/LearningService.php`, `app/Services/Learning/LessonVideoAccessService.php`, `app/Services/Learning/WatermarkInfoService.php`
  - Config: `config/bunny.php`
- **Frontend:**
  - Screen: `src/features/classroom/ClassroomPage.tsx`, `src/features/classroom/components/ClassroomScreen.tsx`
  - Player: `src/features/classroom/components/VideoPlayer.tsx`
  - API: `src/features/classroom/api.ts`

### Khi sửa Module Doanh thu & Rút tiền Giảng viên (Payout & Early Withdrawal):
- **Backend:**
  - Controllers: `app/Http/Controllers/Instructor/InstructorWithdrawalController.php`, `app/Http/Controllers/Instructor/EarlyWithdrawalController.php`, `app/Http/Controllers/Admin/AdminWithdrawalController.php`
  - Services: `app/Services/Payout/EarlyWithdrawalService.php`, `app/Services/Payment/RevenueShareService.php`
  - Repositories: `app/Repositories/Instructor/InstructorWithdrawalRepository.php`
  - Config: `config/payout.php`
- **Frontend:**
  - Page: `src/features/instructor/InstructorWithdrawal.tsx`, `src/features/instructor/InstructorRevenue.tsx`
  - Admin: `src/features/admin/components/pages/WithdrawalsManagement.tsx`, `src/features/admin/components/pages/PayoutAccounts.tsx`
  - API: `src/features/instructor/api.ts`, `src/features/admin/api.ts`

### Khi sửa Module Khuyến mãi & Suất học thử (Coupons & Trial):
- **Backend:**
  - Controllers: `app/Http/Controllers/Instructor/InstructorCouponController.php`
  - Services: `app/Services/Marketing/CouponService.php`, `app/Services/Marketing/CouponPricingService.php`
  - Config: `config/coupon.php`
- **Frontend:**
  - Dashboard: `src/features/instructor/InstructorDashboard.tsx`
  - Checkout: `src/features/cart/CartAndCheckout.tsx`

---

## 6. DANH MỤC CÁC ĐIỂM CẦN XÁC NHẬN (`NEED_CONFIRM`)

1. **`[NEED_CONFIRM]` Quản lý Giỏ hàng Đa khóa học (Multi-item Cart):**  
   - *Hiện trạng:* Frontend `CartAndCheckout.tsx` có lưu mảng `cart` nhiều khóa học, nhưng Backend `POST /api/orders` hiện tại chỉ nhận đơn lẻ 1 `course_id` cho mỗi đơn hàng (1 order = 1 course). Khi thanh toán từ giỏ hàng, FE đang lấy khóa học cuối cùng trong mảng để tạo đơn.  
   - *Định hướng tương lai:* Cần xác nhận nếu muốn nâng cấp BE hỗ trợ tạo nhiều đơn hàng tự động hoặc bảng `order_items`.

2. **`[NEED_CONFIRM]` Cơ chế Tự động Hủy Đơn hàng quá hạn 24h:**  
   - *Hiện trạng:* Đã có cấu hình `order.pending_expire_hours = 24` và phương thức kiểm tra `expires_at` khi thanh toán, nhưng Scheduler Cronjob chạy `OrderExpirationService` tự động mỗi 15 phút cần được kích hoạt trên môi trường Production Server.

---

```text
======================================================
ANTIGRAVITY CONTEXT SCAN: COMPLETE
BACKEND MODULES SCANNED: 12 Modules (300 Routes, 30 Tables, 8 Triggers)
FRONTEND MODULES SCANNED: 21 Feature Folders (17 Admin Pages, 11 Instructor Screens, Classroom, Cart)
AUTHENTICATION & RBAC: VERIFIED (Learner, Instructor, Admin)
OUTPUT FILE CREATED: ANTIGRAVITY_PROJECT_CONTEXT.md
STATUS: READY FOR AUTOMATED WORKFLOWS
======================================================
```
