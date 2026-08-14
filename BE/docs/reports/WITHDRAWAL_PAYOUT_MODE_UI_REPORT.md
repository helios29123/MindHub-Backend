# Withdrawal Payout Mode UI Report

## 1. Auto Mode Identification
- **Status:** PASS
- **Implementation:** Khi một yêu cầu rút tiền được xử lý qua `FakePayoutGateway` hoặc cổng thanh toán tự động khác (sepay, v.v.), `payout_provider` được thiết lập chính xác (ví dụ: `fake`). Dựa vào trường này, API trả về `payout_mode = 'auto'`. Giao diện Frontend Admin sẽ hiển thị huy hiệu `AUTO` (màu xanh indigo) và chi tiết "Phương thức: Tự động" cùng với tên provider (ví dụ: "Fake Gateway") và mã giao dịch (`provider_payout_id`).

## 2. Manual Mode Identification
- **Status:** PASS
- **Implementation:** Khi Admin sử dụng chức năng Mark Paid (Thanh toán thủ công), hệ thống kiểm tra nếu `payout_provider` đang rỗng, sẽ tự động gán `payout_provider = 'manual'` và lưu lại mã giao dịch tham chiếu do Admin nhập vào `provider_payout_id`. API nhận diện provider `'manual'` và trả về `payout_mode = 'manual'`. Frontend sẽ hiển thị huy hiệu `MANUAL` (màu xám) và chi tiết "Phương thức: Thủ công", "Xử lý bởi: Admin" cùng với mã giao dịch tương ứng.

## 3. Admin UI
- **Status:** PASS
- **Implementation:** 
  - Tại trang Quản lý yêu cầu rút tiền (`WithdrawalsManagement.tsx`), huy hiệu `AUTO` hoặc `MANUAL` được hiển thị rõ ràng ngay bên dưới trạng thái chính (`Paid`, `Processing`, v.v.) trong danh sách.
  - Trong thanh kéo (Drawer) chi tiết, huy hiệu cũng xuất hiện cạnh trạng thái ở tiêu đề. 
  - Một mục mới "Phương thức chi trả & Giao dịch" được thêm vào chi tiết, hiển thị rõ phương thức (Tự động / Thủ công / Không xác định), người xử lý (Admin) hoặc tên Provider tương ứng, và mã giao dịch.

## 4. Instructor UI
- **Status:** PASS
- **Implementation:** 
  - Resource API của giảng viên (`InstructorWithdrawalResource` và `InstructorWithdrawalDetailResource`) đã được cập nhật để trả về `payout_mode`. 
  - Modal lịch sử thanh toán của giảng viên (`InstructorWithdrawal.tsx`) hiện sẽ hiển thị "Phương thức chi trả: Tự động" hoặc "Thủ công" khi nhấp vào xem chi tiết, loại bỏ các khái niệm "provider" phức tạp không cần thiết đối với giảng viên.

## 5. Old Data Handling
- **Status:** PASS
- **Implementation:** Với các dữ liệu cũ không có `payout_provider` (`null`), logic API sẽ trả về `payout_mode = null`. Hệ thống UI được lập trình cẩn thận để hiện thị "Phương thức: Không xác định" đối với các giao dịch này thay vì tự đoán là thủ công, tránh gây nhầm lẫn hoặc ảnh hưởng đến dữ liệu cũ.

## 6. Tests
- **Status:** PASS
- **Implementation:** Bài test Feature `WithdrawalPayoutModeTest.php` đã được tạo để đảm bảo:
  - Khi Admin gọi Mark Paid, database cập nhật `payout_provider = 'manual'`.
  - API Admin Withdrawal (Index & Show) trả về chính xác cấu trúc `payout_provider` và `payout_mode`. 
  - Các bài test đã chạy thành công 100%.

---
**READY TO COMMIT:** YES
