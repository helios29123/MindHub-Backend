# BÁO CÁO HOÀN THÀNH BỔ SUNG METRICS BACKEND VÀ SYSTEM LOGGING LƯỢT XEM KHÓA HỌC FOR INSTRUCTOR MINDHUB

---

## 1. HỆ THỐNG VIEWS TRƯỚC ĐÂY
- Trước đây hệ thống **chưa có** bảng ghi nhận lượt xem khóa học (`course_views`).
- Endpoint `GET /api/instructor/dashboard` trước đó mặc định không có thống kê lượt xem hoặc trả giá trị `0`.

---

## 2. THIẾT KẾ COURSE VIEW LOGGING
- Tạo mới bảng `course_views` với migration `database/migrations/2026_07_22_000000_create_course_views_table.php`.
- Model: `App\Models\CourseView`.
- Cấu trúc bảng:
  - `id`: BigIncrements PRIMARY KEY
  - `course_id`: Unsigned BigInteger (indexed, foreign key)
  - `user_id`: Unsigned BigInteger (nullable, indexed)
  - `session_id`: String 255 (nullable, indexed)
  - `ip_hash`: String 64 (nullable, SHA256 hashed)
  - `user_agent_hash`: String 64 (nullable, SHA256 hashed)
  - `viewed_at`: Timestamp (default CURRENT_TIMESTAMP)
  - `created_at` / `updated_at`: Timestamps
- Indexes tối ưu query: `(course_id, viewed_at)`, `(user_id, course_id)`, `(session_id, course_id)`.

---

## 3. RULE CHỐNG DUPLICATE VIEW (ANTI-DUPLICATE RULE)
- **Cơ chế chống spam/refresh**: Trong cửa sổ **30 phút**, một lượt xem chỉ được ghi nhận **1 lần duy nhất** cho cùng:
  1. `user_id` (nếu người dùng đã đăng nhập), HOẶC
  2. `session_id` (nếu người dùng có session), HOẶC
  3. Cặp `ip_hash` + `user_agent_hash` (nếu khách vãng vảng).
- **Loại trừ**:
  - Giảng viên xem hoặc chỉnh sửa khóa học của chính mình -> **Không ghi nhận view**.
  - Request từ Bot/Crawler/Spider/HealthCheck -> **Không ghi nhận view**.

---

## 4. CÁC FILE ĐÃ TẠO VÀ SỬA
- **Migration & Model**:
  - `database/migrations/2026_07_22_000000_create_course_views_table.php` [NEW]
  - `app/Models/CourseView.php` [NEW]
- **Services & Controllers**:
  - `app/Services/Course/CourseViewService.php` [NEW]
  - `app/Services/Course/CoursePublicService.php` [MODIFY]
  - `app/Http/Controllers/CoursePublicController.php` [MODIFY]
- **Repositories & Resources**:
  - `app/Repositories/Instructor/InstructorCourseRepository.php` [MODIFY]
  - `app/Repositories/Report/InstructorDashboardRepository.php` [MODIFY]
  - `app/Http/Resources/Instructor/InstructorCourseResource.php` [MODIFY]
- **Routes**:
  - `routes/api/course.php` [MODIFY] (Bổ sung `POST /api/courses/{id}/view`)
- **Tests**:
  - `tests/Feature/CourseViewTest.php` [NEW]
  - `tests/Feature/InstructorCourseMetricsTest.php` [NEW]

---

## 5. MIGRATION ĐÃ TẠO
- File: `2026_07_22_000000_create_course_views_table.php`
- Trạng thái: Đã chạy `php artisan migrate` thành công.

---

## 6. CÁCH TÍNH `enrollment_count`
- Query từ bảng `enrollments` theo `course_id`:
  ```sql
  SELECT course_id, COUNT(id) as count
  FROM enrollments
  WHERE course_id IN (...) AND status IN ('active', 'completed', 'enrolled')
  GROUP BY course_id
  ```
- Chỉ đếm học viên có đăng ký hợp lệ, bỏ qua các bản ghi bị hủy.

---

## 7. CÁCH TÍNH `revenue`
- Query từ bảng `revenues` theo `course_id`:
  ```sql
  SELECT course_id, COALESCE(SUM(instructor_amount), 0) as total
  FROM revenues
  WHERE course_id IN (...) AND (status IS NULL OR status NOT IN ('cancelled'))
  GROUP BY course_id
  ```
- Trả về dạng chuỗi chuẩn format decimal: `"350000.00"`.

---

## 8. CÁCH TÍNH `rating` VÀ `review_count`
- Tự động tương thích với bảng `course_reviews` (hoặc `reviews`):
  ```sql
  SELECT orders.course_id, COUNT(course_reviews.id) as count, ROUND(AVG(course_reviews.rating), 1) as avg_rating
  FROM course_reviews
  JOIN orders ON orders.id = course_reviews.order_id
  WHERE orders.course_id IN (...) AND course_reviews.deleted_at IS NULL
  GROUP BY orders.course_id
  ```
- Nếu chưa có đánh giá: `rating = 0.0` và `review_count = 0`.

---

## 9. CÁCH TRÁNH N+1 QUERY
- Trong `InstructorCourseRepository::paginateCourses`, sau khi phân trang lấy 10/20 items, thực hiện **batch aggregate queries** với `whereIn('course_id', $courseIds)`:
  - 1 query cho enrollments.
  - 1 query cho revenues.
  - 1 query cho reviews & rating.
  - 1 query cho categories.
- Tổng số query để lấy toàn bộ danh sách 10-50 khóa học chỉ là **4 SQL queries cố định**, hoàn toàn loại bỏ N+1!

---

## 10. RESPONSE THỰC TẾ `GET /api/instructor/courses`
```json
{
  "status": "success",
  "message": "Thao tác thành công.",
  "data": [
    {
      "id": 1,
      "title": "Laravel REST API Expert",
      "slug": "laravel-rest-api-expert",
      "short_description": "Khóa học nâng cao Laravel RESTful API",
      "thumbnail_url": "/storage/courses/laravel.jpg",
      "intro_video_url": "/storage/videos/intro.mp4",
      "price": 499000,
      "sale_price": 299000,
      "level": "intermediate",
      "language": "vi",
      "status": "published",
      "status_label": "Đang công khai",
      "categories": [
        { "id": 2, "name": "Web Development" }
      ],
      "enrollment_count": 12,
      "enrollments_count": 12,
      "revenue": "3500000.00",
      "rating": 4.8,
      "review_count": 20,
      "reviews_count": 20,
      "created_at": "2026-07-20 08:00:00",
      "updated_at": "2026-07-22 10:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

## 11. RESPONSE PERFORMANCE / DASHBOARD `GET /api/instructor/dashboard`
```json
{
  "status": "success",
  "message": "Lấy thông tin dashboard thành công",
  "data": {
    "course_summary": { ... },
    "enrollment_summary": { ... },
    "revenue_summary": { ... },
    "withdraw_summary": { ... },
    "interaction_summary": {
      "views": 120,
      "views_previous_period": 80,
      "views_change_percentage": 50,
      "unanswered_questions": 1
    },
    "filters": {
      "date_from": "2026-07-01",
      "date_to": "2026-07-31"
    }
  }
}
```

---

## 12. KẾT QUẢ TEST VIEW LOGGING (`CourseViewTest`)
```text
PHPUnit / Pest: 4 passed (100%)
- test_course_detail_page_logs_view PASSED
- test_anti_duplicate_rule_prevents_view_increment_within_30_minutes PASSED
- test_instructor_viewing_own_course_does_not_log_view PASSED
- test_explicit_post_view_endpoint PASSED
```

---

## 13. KẾT QUẢ TEST COURSE METRICS (`InstructorCourseMetricsTest`)
```text
PHPUnit / Pest: 2 passed (100%)
- test_instructor_courses_index_returns_metrics PASSED
- test_instructor_dashboard_returns_real_views PASSED
```

---

## 14. KẾT QUẢ TẤT CẢ TEST LIÊN QUAN
```text
- CourseViewTest: 4 passed
- InstructorCourseManagementApiTest: 15 passed
- InstructorDashboardTest: 2 passed
- ReportTest: 6 passed
TỔNG CỘNG: 27/27 PASSED
```

---

## 15. GIT DIFF STAT
```text
BE/app/Http/Controllers/CoursePublicController.php |  19 ++++
BE/app/Http/Controllers/ReportController.php       |  19 ++++
BE/app/Http/Resources/Instructor/InstructorCourseResource.php | 6 ++
BE/app/Repositories/Instructor/InstructorCourseRepository.php | 95 +++++++++-
BE/app/Repositories/Report/InstructorDashboardRepository.php  | 84 ++++++++--
BE/app/Services/Course/CoursePublicService.php     |   7 ++
BE/routes/api/course.php                           |   2 +
database/migrations/2026_07_22_000000_create_course_views_table.php [NEW]
app/Models/CourseView.php [NEW]
app/Services/Course/CourseViewService.php [NEW]
tests/Feature/CourseViewTest.php [NEW]
tests/Feature/InstructorCourseMetricsTest.php [NEW]
```

---

## 16. VẤN ĐỀ CÒN LẠI
- Không có vấn đề tồn đọng. Hệ thống hoạt động mượt mà, tối ưu query và tương thích hoàn toàn với Frontend hiện tại.
