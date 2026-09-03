# TEST REPORT: [RPT-07] Danh sách học viên bỏ dở

## 1. Scope
- **Feature**: Báo cáo danh sách học viên bỏ dở (Inactive Learners Report).
- **Endpoint**: `GET /api/instructor/reports/inactive-learners`
- **Role**: Instructor
- **Middleware**: `auth.session`, `role:instructor`

## 2. Test Environment
- **Database**: SQLite (In-memory) for testing.
- **Framework**: PHPUnit
- **Schema Mocks**: Mapped and dynamically created `lesson_progress`, `lessons`, and `orders` tables in the test environment to bypass the missing schema issues in the current test database setup.

## 3. Test Cases Covered

### 3.1 Authentication & Authorization
- **test_instructor_can_access_inactive_learners**: Mạo danh (mock) instructor session và truy cập endpoint. -> **PASS**
- **test_unauthenticated_cannot_access**: Người dùng chưa đăng nhập không thể truy cập (401). -> **PASS**
- **test_non_instructor_cannot_access**: Người dùng không có role instructor (e.g. learner) sẽ bị từ chối (403). -> **PASS**
- **test_course_id_ownership**: Đảm bảo instructor không thể truy cập báo cáo cho `course_id` không thuộc sở hữu của mình (403). Kiểm tra course không tồn tại trả về 404. -> **PASS**

### 3.2 Business Logic & Filtering
- **test_business_logic_inactive_learners**: 
  - Tạo 1 inactive learner (last activity 20 days ago) và 1 active learner (last activity 2 days ago). 
  - Đảm bảo endpoint chỉ trả về inactive learner khi `inactive_days=14`.
  - Đảm bảo learner có trạng thái `completed` bị loại khỏi báo cáo dù last activity đã cũ. -> **PASS**
- **test_filter_by_status**: Lọc theo `enrollment_status` (e.g., active). -> **PASS**
- **test_filter_by_course_id**: Lọc báo cáo cho một khoá học cụ thể mà instructor sở hữu. -> **PASS**
- **test_filter_by_date_range**: Lọc học viên dựa trên thời điểm đăng ký (`enrolled_at`). -> **PASS**
- **test_filter_by_month_year**: Lọc dựa trên tháng và năm đăng ký. -> **PASS**

### 3.3 Data Formatting & Security
- **test_response_does_not_contain_sensitive_info**: Đảm bảo các field nhạy cảm như `password`, `remember_token`, hoặc nội dung dư thừa của user record không bị leak qua Resource response. -> **PASS**

### 3.4 Pagination & Sorting
- **test_pagination_and_per_page**: Xác thực meta phân trang và số lượng item trên mỗi trang (per_page). -> **PASS**
- **test_status_and_sorting**: Kiểm tra tính năng sắp xếp dựa trên các cột hợp lệ như `last_activity_at`, `inactive_days`. -> **PASS**

## 4. Test Results
- **Total Tests**: 12
- **Passed**: 12
- **Failed**: 0
- **Assertions**: 39

All test cases for `[RPT-07]` successfully execute and pass as required. The schema dependency issues related to `lesson_progress` and `orders` in testing have been addressed through dynamic schema and data mocks.
