# MINDHUB — DEMO RUNBOOK (KỊCH BẢN THAO TÁC DEMO THEO 3 LUỒNG CHÍNH)

> **Tài liệu hướng dẫn trực tiếp cho Người Demo (Presenter / Tester)**  
> *Lưu ý phạm vi: Hệ thống hiện tại không chứa Quiz (`Quiz: chưa hỗ trợ trong scope hiện tại`). Trong phần học Online chỉ kiểm thử Video Bunny, Text Lesson, PDF Asset, Cập nhật Progress, và Lesson Comment.*  
> *Lưu ý bảo mật: Tài liệu này không chứa bất kỳ mật khẩu hoặc secret key nào. Mật khẩu demo được đọc trực tiếp từ cấu hình môi trường local.*

---

## 1. Danh sách Persona và Mục đích Sử dụng

| Mã Persona | Vai trò | Email / Định danh | Trạng thái trước Test | Khóa học liên kết | Mục đích kiểm thử |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`GUEST-01`** | Guest (Khách) | *Chưa đăng nhập* | Khách truy cập ẩn danh | `Course Purchase Live` (ID: 2)<br>*(MySQL thực chiến: Thiết kế Database cho ứng dụng Web)* | Xem trang chủ, chi tiết khóa học, phát video xem trước bài 1; bị chặn khi bấm Mua/Yêu thích/Đánh giá. |
| **`LEARNER-LIVE-01`** | Học viên (Inbox thật) | `[Email Học viên Live]` | Chưa mua khóa học của Live (0 Order / 0 Enrollment) | `Course Purchase Live` (ID: 2)<br>*(MySQL thực chiến: Thiết kế Database cho ứng dụng Web)* | Đăng nhập $\rightarrow$ Mua khóa học Live $\rightarrow$ Nhận biên lai $\rightarrow$ Học video, text, tải PDF $\rightarrow$ Tạo comment $\rightarrow$ Viết review. |
| **`LEARNER-TRIAL-VALID-01`** | Học viên (Seed Identity) | `tran.thanhson@mindhub.local` | `status = 'active'`, `expires_at > NOW()` | Course 4 (ID: 4): *Kiểm thử & Tự động hóa API Toàn diện với Postman* | Kiểm tra quyền học thử còn hạn $\rightarrow$ Xem video $\rightarrow$ Gửi comment bài học thành công (`TC-COMMENT-TRIAL`). |
| **`LEARNER-TRIAL-EXPIRED-01`** | Học viên (Seed Identity) | `hoang.ducthang@mindhub.local` | `status = 'inactive'`, `expires_at < NOW()` | Course 10 (ID: 10): *Lộ trình Phát triển Sự nghiệp Web Developer Toàn diện* | Kiểm tra khóa học thử hết hạn $\rightarrow$ Bị chặn quyền học và chặn gửi comment bài học (`TC-COMMENT-EXPIRED`). |
| **`INSTRUCTOR-LIVE-01`** | Giảng viên (Inbox thật) | `[Email Giảng viên Live]` | - Có `Course Revenue Fixture` (`published`) tạo số dư khả dụng $2.446.500$đ.<br>- Có `Course Draft Live` (`draft`).<br>- Chưa có Payout Account và chưa có đơn rút pending. | `Course Revenue Fixture` (ID: 1) & `Course Draft Live` (ID: 6) | 1. Rút tiền từng phần ($1.000.000$đ): Nhận OTP email lưu Payout $\rightarrow$ Nhận OTP email gửi yêu cầu rút tiền.<br>2. Xuất bản khóa: Gửi kiểm duyệt khóa Draft. |
| **`ADMIN-LIVE-01`** | Quản trị viên (Inbox thật) | `[Email Admin Live]` | Quyền Admin tối cao | Toàn hệ thống | 1. Phê duyệt chi trả yêu cầu rút tiền từng phần.<br>2. Phê duyệt khóa học Draft lên sàn. |
| **`INSTRUCTOR-HISTORY-01`** | Giảng viên (Seed Identity) | `tran.minhduc@mindhub.local` | Rank Diamond, sở hữu nhiều khóa học | `Course Purchase Live` (ID: 2) & Course 3 (ID: 3) | Hiển thị xếp hạng giảng viên nổi bật trên Trang chủ và nhận 70% doanh thu khi `LEARNER-LIVE-01` mua khóa học. |

---

## 2. Danh sách 3 Khóa học Fixture Trọng tâm

| Khóa học Fixture | ID DB | Tên Khóa học Thống nhất | Slug Thống nhất | Giảng viên sở hữu | Trạng thái Seed | Mục đích trong Kịch bản Demo |
| :--- | :---: | :--- | :--- | :--- | :---: | :--- |
| **`Course Purchase Live`** | **2** | **MySQL thực chiến: Thiết kế Database cho ứng dụng Web** | `mysql-thuc-chien-thiet-ke-database` | `INSTRUCTOR-HISTORY-01` *(Trần Minh Đức)* | `published` | Dùng cho **Luồng A**: `LEARNER-LIVE-01` chọn mua từ Trang chủ $\rightarrow$ Thanh toán SePay VietQR $\rightarrow$ Vào học $\rightarrow$ Comment $\rightarrow$ Review. |
| **`Course Revenue Fixture`** | **1** | **Laravel 12 thực chiến: Xây dựng REST API cho hệ thống bán khóa học** | `laravel-12-thuc-chien-rest-api` | `INSTRUCTOR-LIVE-01` *(ThS. Lê Hoàng Nam)* | `published` | Dùng cho **Luồng B**: Khóa học đã có sẵn 5 đơn hàng lịch sử tạo ra số dư khả dụng $2.446.500$đ ($\ge 200.000$đ) để `INSTRUCTOR-LIVE-01` thực hiện rút tiền từng phần ($1.000.000$đ) qua OTP. |
| **`Course Draft Live`** | **6** | **Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker** | `kien-truc-microservices-chuyen-sau-laravel-12-docker` | `INSTRUCTOR-LIVE-01` *(ThS. Lê Hoàng Nam)* | `draft` | Dùng cho **Luồng C**: Khóa học có sẵn Section, Video Bunny, Text, PDF để `INSTRUCTOR-LIVE-01` bấm gửi duyệt $\rightarrow$ `ADMIN-LIVE-01` duyệt lên sàn. |

---

## 3. Kịch bản Demo Chi tiết theo 3 Luồng Nghiệp vụ Chính

### LUỒNG A — HỌC VIÊN MUA KHÓA HỌC & THAM GIA HỌC TRỰC TUYẾN

| Bước | Test ID | Persona | Màn hình | Thao tác | Expected UI / API Output | Dữ liệu thay đổi |
| :---: | :---: | :--- | :--- | :--- | :--- | :--- |
| **A1** | `TC-GUEST-01` | `GUEST-01` *(Ẩn danh)* | Trang chủ `/` $\rightarrow$ Chi tiết `/courses/mysql-thuc-chien-thiet-ke-database` | 1. Mở xem chi tiết khóa học `Course Purchase Live` (ID: 2).<br>2. Bấm xem video xem trước (Preview) ở bài 1.<br>3. Bấm "Mua ngay" hoặc "Yêu thích". | - Video preview phát bình thường.<br>- Nút Mua ngay chặn và chuyển hướng sang `/login` kèm state URL quay lại. | Không thay đổi DB. |
| **A2** | `TC-AUTH-LOGIN` | `LEARNER-LIVE-01` | Đăng nhập `/login` | Nhập Email Học viên Live và Mật khẩu $\rightarrow$ Bấm Đăng nhập. | Đăng nhập thành công, điều hướng quay lại trang khóa học `Course Purchase Live`. | Khởi tạo Session / Token. |
| **A3** | `TC-ORDER-BUY` | `LEARNER-LIVE-01` | Chi tiết khóa học $\rightarrow$ Checkout `/checkout` | 1. Bấm "Mua ngay" $\rightarrow$ Chuyển sang Checkout.<br>2. Chọn phương thức SePay VietQR $\rightarrow$ Bấm "Tiến hành thanh toán". | Hệ thống hiển thị mã QR VietQR chuẩn kèm số tiền $599.000$đ, ngân hàng nhận và nội dung chuyển khoản `MH-XXXX`. | Tạo mới `orders` (`status = 'pending_payment'`). |
| **A4** | `TC-PAYMENT-PAID` | `LEARNER-LIVE-01` | Checkout `/checkout` | Thực hiện xác nhận thanh toán (bằng webhook test SePay hoặc quét mã). | - Giao diện tự động chuyển sang thông báo "Thanh toán thành công!".<br>- Học viên nhận email biên lai đơn hàng.<br>- Nút "Bắt đầu học ngay" xuất hiện. | `orders.status = 'paid'`, tạo mới `enrollments` (`active`), tạo `revenues` (70% cho giảng viên). |
| **A5** | `TC-LEARN-VIDEO` | `LEARNER-LIVE-01` | Phòng học `/learn/2` | Mở bài học Video số 1, xem video trong 20 giây. | Video phát mượt mà từ Bunny Stream. Thanh tiến độ học tập cập nhật thời gian xem thực tế. | Ghi nhận `video_progress`, `learning_daily_activity` và `enrollments.progress_percent`. |
| **A6** | `TC-LEARN-TEXT-ASSET` | `LEARNER-LIVE-01` | Phòng học `/learn/2` | 1. Chuyển sang Bài học Text lý thuyết.<br>2. Bấm nút "Tải tài liệu PDF" đính kèm. | - Nội dung Text hiển thị đầy đủ formatting.<br>- Trình duyệt tải về file `mindhub-mysql-optimization-guide.pdf` (HTTP 200). | Ghi nhận `lesson_progress` (`status = 'completed'`). |
| **A7** | `TC-COMMENT-PAID` | `LEARNER-LIVE-01` | Tab Thảo luận trong phòng học `/learn/2` | Nhập câu hỏi: *"Thầy cho em hỏi cách tối ưu Composite Index khi bảng có hơn 1 triệu bản ghi?"* $\rightarrow$ Bấm Gửi (`POST /api/interactions/lessons/{id}/comments`). | Bình luận xuất hiện ngay lập tức trong danh sách thảo luận bài học, gắn đúng tên học viên và bài học. | Tạo mới 1 bản ghi trong bảng `comments` (`status = 'visible'`). |
| **A8** | `TC-COURSE-REVIEW` | `LEARNER-LIVE-01` | Chi tiết khóa học `/courses/mysql-thuc-chien-thiet-ke-database` | Mở form đánh giá, chọn 5 sao, nhập nhận xét: *"Khóa học rất chi tiết và thực chiến!"* $\rightarrow$ Bấm Gửi (`POST /api/reviews`). | Thông báo đánh giá thành công. Đánh giá xuất hiện trong tab Đánh giá; điểm sao trung bình của khóa học cập nhật realtime. | Tạo mới 1 bản ghi trong `course_reviews`. |

---

### LUỒNG B — GIẢNG VIÊN THIẾT LẬP PAYOUT, RÚT TIỀN TỪNG PHẦN & ADMIN PHÊ DUYỆT

| Bước | Test ID | Persona | Màn hình | Thao tác | Expected UI / API Output | Dữ liệu thay đổi |
| :---: | :---: | :--- | :--- | :--- | :--- | :--- |
| **B1** | `TC-INST-DASHBOARD` | `INSTRUCTOR-LIVE-01` | Dashboard `/instructor` $\rightarrow$ Tài chính | Đăng nhập Giảng viên $\rightarrow$ Mở Dashboard Doanh thu. | Số dư khả dụng (`available_balance`) hiển thị chính xác $2.446.500$đ (tính từ các đơn hàng lịch sử của `Course Revenue Fixture` ID: 1). | Không đổi DB. |
| **B2** | `TC-PAYOUT-OTP` | `INSTRUCTOR-LIVE-01` | Cấu hình Payout `/instructor` $\rightarrow$ Tài khoản nhận tiền | 1. Nhập Số tài khoản, Ngân hàng nhận tiền.<br>2. Bấm "Nhận mã OTP qua Email".<br>3. Mở Inbox Giảng viên lấy OTP 6 số $\rightarrow$ Nhập OTP và bấm "Lưu tài khoản". | - Email gửi mã OTP thành công.<br>- Thông báo "Lưu tài khoản nhận tiền thành công".<br>- Tài khoản nhận tiền chuyển sang trạng thái đã xác minh (`verified`). | Tạo mới 1 bản ghi trong bảng `payout_accounts` (`status = 'verified'`, `is_default = 1`). |
| **B3** | `TC-WITHDRAW-PARTIAL` | `INSTRUCTOR-LIVE-01` | Yêu cầu rút tiền `/instructor` $\rightarrow$ Rút tiền | 1. Nhập số tiền rút từng phần: **$1.000.000$đ** (trong tổng số $2.446.500$đ).<br>2. Bấm "Nhận OTP rút tiền".<br>3. Mở Inbox Giảng viên lấy OTP $\rightarrow$ Nhập OTP $\rightarrow$ Bấm "Gửi yêu cầu". | - Email gửi OTP rút tiền sớm thành công.<br>- Thông báo "Tạo yêu cầu rút tiền thành công".<br>- Hệ thống phân bổ từng phần (Partial Allocation) vào `withdrawal_revenues` đúng tổng $1.000.000$đ.<br>- Yêu cầu xuất hiện ở trạng thái `Chờ duyệt` (`pending`). | Tạo mới `withdraw_requests` (`amount = 1000000`, `status = 'pending'`). `reservedBalance` tăng đúng $1.000.000$đ, số dư khả dụng tạm tính giảm còn $1.446.500$đ. |
| **B4** | `TC-ADMIN-WITHDRAW` | `ADMIN-LIVE-01` | Quản lý rút tiền `/admin/withdrawals` | 1. Đăng nhập Admin $\rightarrow$ Mở danh sách Rút tiền.<br>2. Mở chi tiết yêu cầu rút tiền $1.000.000$đ của `INSTRUCTOR-LIVE-01`.<br>3. Kiểm tra bảng kê Allocations (tổng allocation đúng $1.000.000$đ).<br>4. Bấm "Phê duyệt chi trả" (`PATCH /api/admin/withdrawals/{id}/approve`). | - Bảng kê Allocations hiển thị chính xác các khoản doanh thu được trích.<br>- Thông báo "Duyệt yêu cầu rút tiền thành công".<br>- Trạng thái yêu cầu chuyển sang `Đã thanh toán` (`paid`).<br>- Số dư khả dụng của Giảng viên Live chính thức còn lại đúng $1.446.500$đ. | `withdraw_requests.status = 'paid'`, `paid_at = NOW()`. |

---

### LUỒNG C — GIẢNG VIÊN TẠO/GỬI DUYỆT KHÓA HỌC & ADMIN DUYỆT LÊN SÀN

| Bước | Test ID | Persona | Màn hình | Thao tác | Expected UI / API Output | Dữ liệu thay đổi |
| :---: | :---: | :--- | :--- | :--- | :--- | :--- |
| **C1** | `TC-INST-SUBMIT` | `INSTRUCTOR-LIVE-01` | Quản lý khóa học `/instructor` $\rightarrow$ Khóa học của tôi | 1. Mở `Course Draft Live` (*Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker* - ID: 6).<br>2. Kiểm tra danh sách 2 Section, 8 Video Bunny, Text và File PDF có sẵn.<br>3. Bấm nút "Gửi kiểm duyệt" (`POST /api/instructor/courses/6/submit-review`). | - Thông báo "Gửi kiểm duyệt khóa học thành công".<br>- Trạng thái khóa học chuyển sang `Chờ kiểm duyệt` (`pending_review`).<br>- Admin nhận Email thông báo có khóa học mới gửi duyệt. | `courses.status` đổi từ `draft` $\rightarrow$ `pending_review`. |
| **C2** | `TC-ADMIN-APPROVE` | `ADMIN-LIVE-01` | Kiểm duyệt khóa học `/admin/moderation` | 1. Mở danh sách khóa học chờ duyệt.<br>2. Xem chi tiết nội dung khóa học vừa gửi.<br>3. Bấm nút "Phê duyệt" (`POST /api/admin/moderation/courses/6/approve`). | - Thông báo "Duyệt khóa học thành công".<br>- Khóa học chuyển sang trạng thái `Đã xuất bản` (`published`).<br>- Giảng viên Live nhận Email chúc mừng khóa học đã lên sàn. | `courses.status` đổi từ `pending_review` $\rightarrow$ `published`, gán `published_at = NOW()`. |
| **C3** | `TC-CATALOG-VERIFY` | `GUEST-01` / Mọi User | Trang chủ `/` & Danh mục `/courses` | Mở Trang chủ và Trang Danh mục khóa học Backend. | Khóa học *Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker* xuất hiện công khai trên Trang chủ và Danh mục; học viên có thể bấm vào xem chi tiết và mua ngay. | Hiển thị public realtime trên toàn hệ thống. |

---

## 4. Test Cases Bổ sung: Phân quyền Bình luận Bài học (Lesson Comments)

| Test ID | Persona thực hiện | Khóa học / Bài học | Điều kiện Data trước Test | Thao tác | Expected Output |
| :---: | :--- | :--- | :--- | :--- | :--- |
| **`TC-COMMENT-PAID`** | `LEARNER-LIVE-01` | `Course Purchase Live` (ID: 2, Bài Video số 1) | Đã mua khóa học (`enrollments.status = 'active'`) | Gửi bình luận: *"Bài giảng rất hay!"* | **Thành công:** Bình luận hiển thị ngay trong tab Thảo luận của bài học, tạo bản ghi `comments` (`status = 'visible'`). |
| **`TC-COMMENT-TRIAL`** | `LEARNER-TRIAL-VALID-01` | Course 4 (ID: 4): *Postman API* (Bài Video số 1) | Gói học thử còn hạn (`expires_at > NOW()`) | Gửi bình luận: *"Em đang học thử phần biến môi trường."* | **Thành công:** Bình luận được lưu và hiển thị bình thường (`status = 'visible'`). |
| **`TC-COMMENT-EXPIRED`** | `LEARNER-TRIAL-EXPIRED-01` | Course 10 (ID: 10): *Career Webdev* (Bài Video số 1) | Gói học thử đã hết hạn (`expires_at < NOW()`) | Cố gắng gửi bình luận vào bài học. | **Bị chặn:** API trả về HTTP 403 `Bạn chưa có quyền học hoặc quyền học đã hết hạn`. Không tạo bản ghi trong DB. |
