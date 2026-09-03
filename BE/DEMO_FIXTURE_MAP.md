# MINDHUB — DEMO FIXTURE MAP (ÁNH XẠ DỮ LIỆU KỸ THUẬT CHO SEEDER)

> **Tài liệu kỹ thuật nội bộ phục vụ cấu hình và đối soát Seeder**  
> *Lưu ý bảo mật: Tài liệu này không chứa bất kỳ mật khẩu hoặc secret key nào. Mật khẩu demo được đọc trực tiếp từ cấu hình môi trường local.*

---

## 1. Bảng 3 Khóa học Fixture Trọng tâm cho Kịch bản Demo Trực tiếp

| Tên Fixture | ID DB | Tên Khóa học Thống nhất | Slug Thống nhất | Giảng viên sở hữu | Trạng thái Seed | Nhóm Video Bunny | Dữ liệu Liên kết Cần có trước Demo | Persona được phép dùng |
| :--- | :---: | :--- | :--- | :---: | :--- | :--- | :--- |
| **`Course Purchase Live`** | **2** | **MySQL thực chiến: Thiết kế Database cho ứng dụng Web** | `mysql-thuc-chien-thiet-ke-database` | `INSTRUCTOR-HISTORY-01` *(Trần Minh Đức)* | `published` | `MySQL Database Design` (10 Video) | - Có 2 Section, 10 Video, 1 Text, 1 PDF Asset.<br>- `LEARNER-LIVE-01` **chưa từng mua khóa này** (0 Order / 0 Enrollment).<br>- Sẵn sàng cho Luồng A (Mua $\rightarrow$ Học $\rightarrow$ Comment $\rightarrow$ Review). | - `GUEST-01` (Xem public)<br>- `LEARNER-LIVE-01` (Mua & Học) |
| **`Course Revenue Fixture`** | **1** | **Laravel 12 thực chiến: Xây dựng REST API cho hệ thống bán khóa học** | `laravel-12-thuc-chien-rest-api` | `INSTRUCTOR-LIVE-01` *(ThS. Lê Hoàng Nam)* | `published` | `Laravel Rest API` (10 Video) | - Có 5 đơn hàng `paid` lịch sử từ các Học viên Seed Identity.<br>- Tạo ra doanh thu 70% ($2.446.500$đ) trong `revenues`.<br>- `INSTRUCTOR-LIVE-01` chưa có Payout Account và chưa có đơn rút pending trước demo.<br>- Sẵn sàng cho Luồng B (Rút tiền từng phần $1.000.000$đ qua OTP $\rightarrow$ Admin duyệt). | - `INSTRUCTOR-LIVE-01`<br>- `ADMIN-LIVE-01` |
| **`Course Draft Live`** | **6** | **Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker** | `kien-truc-microservices-chuyen-sau-laravel-12-docker` | `INSTRUCTOR-LIVE-01` *(ThS. Lê Hoàng Nam)* | `draft` | `Deploy VPS AAPanel` *(Subset B - 8 Video)* | - Đã nạp sẵn Category, Thumbnail, 2 Section, 8 Video Bunny, 1 Text, 1 PDF Asset.<br>- Đang ở `courses.status = 'draft'`.<br>- Sẵn sàng cho Luồng C (Gửi duyệt $\rightarrow$ Admin duyệt). | - `INSTRUCTOR-LIVE-01`<br>- `ADMIN-LIVE-01` |

---

## 2. Bảng Phân bổ 86 Video Bunny CDN Không Trùng lặp (10 Khóa học)

* **Tổng số Video Lesson:** Đúng **86 Video duy nhất** từ `bunny_videos.json` (Library `724015`).
* **Phân bổ 2 Subset cho nhóm `Deploy VPS AAPanel` (có tổng cộng 30 video):**
  * **Subset A (Course 5):** 8 video đầu tiên (Index $0 \rightarrow 7$).
  * **Subset B (Course 6):** 8 video tiếp theo (Index $8 \rightarrow 15$).
  * Hai subset hoàn toàn tách biệt, đảm bảo **100% không trùng `lessons.video_id`**.

| ID | Tên Khóa học Thống nhất | Slug Thống nhất | Vai trò Fixture | Trạng thái | Giảng viên | Nhóm Bunny trong JSON | Video IDs | Text | File Asset PDF (Storage Verified) |
| :---: | :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: | :--- |
| **1** | **Laravel 12 thực chiến: Xây dựng REST API cho hệ thống bán khóa học** | `laravel-12-thuc-chien-rest-api` | `Course Revenue Fixture` | `published` | `INSTRUCTOR-LIVE-01` | `Laravel Rest API` | **10** (Bài 1 preview) | 1 Text | `mindhub-laravel-api-cheatsheet.pdf` |
| **2** | **MySQL thực chiến: Thiết kế Database cho ứng dụng Web** | `mysql-thuc-chien-thiet-ke-database` | `Course Purchase Live` | `published` | `INSTRUCTOR-HISTORY-01` | `MySQL Database Design` | **10** (Bài 1 preview) | 1 Text | `mindhub-mysql-optimization-guide.pdf` |
| **3** | **React 19 & TypeScript: Xây dựng Admin Dashboard từ đầu** | `react-typescript-admin-dashboard` | Background Learning | `published` | `INSTRUCTOR-HISTORY-01` | `React E-Learning` | **10** (Bài 1 preview) | 1 Text | `mindhub-react-typescript-handbook.pdf` |
| **4** | **Kiểm thử & Tự động hóa API Toàn diện với Postman** | `kiem-thu-tu-dong-hoa-api-postman` | Background Trial Active | `published` | Giảng viên Seed 2 | `Postman API Testing` | **8** (Bài 1 preview) | 1 Text | `mindhub-postman-testing-cheatsheet.pdf` |
| **5** | **Triển khai & Vận hành VPS Linux với AAPanel & Docker** | `trien-khai-vps-linux-aapanel` | Background Review | `published` | Giảng viên Seed 3 | `Deploy VPS AAPanel` *(Subset A: Index 0-7)* | **8** (Bài 1 preview) | 1 Text | `mindhub-vps-deployment-checklist.pdf` |
| **6** | **Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker** | `kien-truc-microservices-chuyen-sau-laravel-12-docker` | `Course Draft Live` | `draft` | `INSTRUCTOR-LIVE-01` | `Deploy VPS AAPanel` *(Subset B: Index 8-15)* | **8** (Bài 1 preview) | 1 Text | `mindhub-microservices-docker-guide.pdf` |
| **7** | **Xây dựng Sản phẩm Web MVP: Từ Ý tưởng đến Ra mắt Thực tế** | `xay-dung-san-pham-web-mvp` | Background Catalog | `published` | Giảng viên Seed 4 | `MVP Web Product` | **8** (Bài 1 preview) | 1 Text | `mindhub-mvp-product-framework.pdf` |
| **8** | **Quản lý Dự án Web Thực chiến cho Team nhỏ** | `quan-ly-du-an-web-thuc-chien` | Background Catalog | `published` | Giảng viên Seed 2 | `Web Project Management` | **8** (Bài 1 preview) | 1 Text | `mindhub-project-management-template.pdf` |
| **9** | **Web Analytics & A/B Testing Tối ưu Tỷ lệ Chuyển đổi** | `web-analytics-ab-testing` | Background Catalog | `published` | Giảng viên Seed 3 | `Web Analytics A/B Testing` | **8** (Bài 1 preview) | 1 Text | `mindhub-abtesting-conversion-guide.pdf` |
| **10** | **Lộ trình Phát triển Sự nghiệp Web Developer Toàn diện** | `lo-trinh-su-nghiep-web-developer` | Background Trial Expired | `published` | Giảng viên Seed 4 | `Career Webdev` | **8** (Bài 1 preview) | 1 Text | `mindhub-webdev-career-roadmap.pdf` |
| **Tổng** | **10 Khóa học hoàn chỉnh** | — | — | **9 Published + 1 Draft** | **5 Giảng viên** | **10 Nhóm/Subset** | **86 Video ID DUY NHẤT** | **10 Text** | **10 File PDF Assets (Tồn tại thật)** |

---

## 3. Bảng Ánh xạ Chi tiết Persona $\rightarrow$ Tài khoản $\rightarrow$ Quyền hạn & Mục đích Test

| Mã Persona | Họ và Tên | Email | Role | Khóa học liên kết | Trạng thái Dữ liệu Seed sẵn | Mục đích kiểm thử trong Runbook |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **`GUEST-01`** | *Khách ẩn danh* | *Không có* | `guest` | `Course Purchase Live` (ID: 2) | Không có token / Guest Session | Chạy Test Case **A1** (Xem public, video preview, bị chặn mua/wishlist). |
| **`LEARNER-LIVE-01`** | Nguyễn Tuấn Anh | `[Email Học viên Live]` | `learner` | `Course Purchase Live` (ID: 2) | - `orders`: 0 đơn<br>- `enrollments`: 0 quyền học | Chạy Test Cases **A2 $\rightarrow$ A8** (Mua khóa $\rightarrow$ Nhận biên lai $\rightarrow$ Học video, text, PDF $\rightarrow$ Comment `TC-COMMENT-PAID` $\rightarrow$ Review). |
| **`LEARNER-TRIAL-VALID-01`** | Trần Thanh Sơn | `tran.thanhson@mindhub.local` | `learner` | Course 4 (ID: 4): *Postman API* | `enrollments.status = 'active'`, `expires_at = NOW() + 5 days` | Chạy Test Case **`TC-COMMENT-TRIAL`** (Gửi bình luận trong bài học thử còn hạn). |
| **`LEARNER-TRIAL-EXPIRED-01`** | Hoàng Đức Thắng | `hoang.ducthang@mindhub.local` | `learner` | Course 10 (ID: 10): *Career Webdev* | `enrollments.status = 'inactive'`, `expires_at = NOW() - 3 days` | Chạy Test Case **`TC-COMMENT-EXPIRED`** (Bị chặn quyền học và chặn gửi bình luận). |
| **`INSTRUCTOR-LIVE-01`** | ThS. Lê Hoàng Nam | `[Email Giảng viên Live]` | `instructor` | `Course Revenue Fixture` (ID: 1) & `Course Draft Live` (ID: 6) | - `Course Revenue Fixture`: Có sẵn đúng $2.446.500$đ doanh thu trong `revenues`.<br>- `payout_accounts`: **0 tài khoản**.<br>- `withdraw_requests`: **0 đơn pending**.<br>- `Course Draft Live`: Trạng thái `draft`. | Chạy Test Cases **B1 $\rightarrow$ B3** (Rút tiền từng phần $1.000.000$đ qua OTP email) và **C1** (Gửi kiểm duyệt khóa Draft). |
| **`ADMIN-LIVE-01`** | Quản trị viên MindHub | `[Email Admin Live]` | `admin` | Toàn hệ thống | Có quyền Admin tối cao | Chạy Test Cases **B4** (Duyệt rút tiền từng phần $1.000.000$đ) và **C2** (Duyệt khóa học Draft). |
| **`INSTRUCTOR-HISTORY-01`** | Trần Minh Đức | `tran.minhduc@mindhub.local` | `instructor` | `Course Purchase Live` (ID: 2) & Course 3 (ID: 3) | - `instructor_rank = 'diamond'`<br>- Có lịch sử doanh thu và rút tiền | Hiển thị Giảng viên nổi bật trên Trang chủ và nhận 70% doanh thu khi `LEARNER-LIVE-01` mua khóa học. |
