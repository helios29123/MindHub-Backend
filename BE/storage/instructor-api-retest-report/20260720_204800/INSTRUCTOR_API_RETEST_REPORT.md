# INSTRUCTOR API RETEST REPORT

## 1. Môi trường
- Branch: `feature/full-instructor-api-revenue-share`
- Commit: `67e82377d42f19445b91c94bdc1cb52d82808636`
- PHP: `8.3.16`
- Laravel: `12.61.1`
- DB status: Online (`DB OK`)
- Time: 2026-07-20 20:49:00+07:00

## 2. Tổng quan
- Route expected: Đầy đủ các route đã khai báo.
- Route found: Đầy đủ và chính xác các route theo yêu cầu.
- Route missing: Không có route nào bị thiếu.
- PHP lint pass/fail: PASS (Tất cả file nguồn đều không lỗi cú pháp).
- Feature test pass/fail: FAIL (Nhiều test case bị lỗi do bất đồng bộ Schema DB và Validation Request).
- API 500: Không có (chỉ có lỗi MySQL crash do thiếu cột `updated_at` trong môi trường test).
- Ownership lỗi:
  - Xóa Coupon của instructor khác trả về `403 Forbidden` thay vì `404 Not Found`.
- Revenue share lỗi:
  - Bảng `revenues` trên MySQL local thiếu cột `updated_at`, dẫn tới query insert thô trong test `setUp` bị lỗi: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'updated_at'`.
- Withdrawal lỗi:
  - Chi tiết Withdrawal bị lỗi: `Attempt to read property "id" on array` do service layer trả về `array` thay vì `object` (Model/stdClass).
  - Lỗi database seed trùng khóa chính `users.PRIMARY` khi chạy test.
- Q&A lỗi:
  - Filter `status=all` bị từ chối 422 vì `all` không nằm trong danh sách validation `in:answered,unanswered`.
  - Filter `sort=oldest` bị loại bỏ khi validate do thiếu rule khai báo, dẫn tới kết quả sắp xếp sai.
- Coupon lỗi:
  - Cho phép cập nhật trạng thái Active đối với các Coupon đã Expired hoặc Used Up (mong đợi 409 nhưng trả về 200).
- Notification lỗi: Không phát hiện lỗi.

## 3. Kết quả route
- Withdrawals map đúng chưa: Đã map đúng về `InstructorWithdrawalController`, không còn bị override bởi `InstructorCourseController`.
- Q&A missing method còn không: Đã bổ sung đầy đủ và đúng method trên `InteractionController`.
- Coupons route đủ chưa: Đầy đủ.
- Payout route đủ chưa: Đầy đủ.
- Notifications route đủ chưa: Đầy đủ.

## 4. Kết quả chia lợi nhuận
- PaymentService còn 100/0 không: Không. Đã gọi qua `RevenueShareService` để xử lý.
- RevenueShareService có 70/30 không: Có. Logic mặc định chia 70% cho giảng viên và 30% cho nền tảng.
- Có chống duplicate không: Có. Có kiểm tra tồn tại `order_id` trước khi insert.
- Test order 500000 ra số nào:
  - gross_amount: 500,000đ
  - instructor_amount: 350,000đ
  - platform_fee_amount: 150,000đ

## 5. Kết quả withdrawals
- Controller còn TypeError không: Không. Đã truyền `$instructorId` kiểu `int` chính xác.
- Balance dùng instructor_amount chưa: Rồi (sử dụng `SUM(revenues.instructor_amount)`).
- amount > balance có 422 không: Có. Service ném exception trả lỗi 422.

## 6. Lỗi còn lại
### Lỗi P0 / P1
1. **Lỗi Withdrawal Detail Resource**: Lỗi `Attempt to read property "id" on array` khi show chi tiết withdrawal.
2. **Thiếu validation rules cho Q&A**: `InstructorQuestionQueryRequest` thiếu rule cho `status=all` và `sort` khiến API trả về lỗi 422 hoặc bị sai thứ tự sắp xếp.
3. **Database Schema của revenues**: Bảng `revenues` trên DB thiếu cột `updated_at`, làm crash các test liên quan tới khóa học do setUp test sử dụng query insert thô chứa `updated_at`.
4. **Duplicate Primary Key on User Test Seed**: Test case Withdrawal bị crash do chèn trùng ID 4 trong setUp.

### Lỗi P2
1. **Logic kích hoạt Coupon**: Không chặn chuyển trạng thái Active khi Coupon đã Expired/Used Up.
2. **Mã phản hồi Coupon Delete**: Mong đợi trả về `404 Not Found` thay vì `403 Forbidden` khi thao tác coupon không thuộc quyền sở hữu.

## 7. Kết luận
- Chưa thể tiến hành nối FE hoàn toàn do lỗi crash chi tiết Withdrawal và validation Q&A.
- Cần điều chỉnh kiểu dữ liệu trả về của service layer thành Eloquent Model thay vì raw array, đồng thời bổ sung đầy đủ validation rules.

---
### Thư mục Report
- **Folder report path**: `storage/instructor-api-retest-report/20260720_204800`
- **File report path**: `storage/instructor-api-retest-report/20260720_204800/INSTRUCTOR_API_RETEST_REPORT.md`

### Git Status Short
```
 M app/Http/Controllers/InstructorCourseController.php
 M app/Http/Controllers/InstructorWithdrawalController.php
 M app/Http/Controllers/InteractionController.php
 M app/Http/Controllers/ReportController.php
 M app/Http/Requests/Interaction/InstructorQuestionQueryRequest.php
 M app/Http/Resources/Instructor/InstructorPayoutAccountResource.php
 M app/Http/Resources/Interaction/InstructorQuestionResource.php
 M app/Http/Resources/Marketing/InstructorCouponResource.php
 M app/Models/InstructorProfile.php
 M app/Models/Order.php
 M app/Models/PayoutAccount.php
 M app/Models/User.php
 M app/Repositories/Instructor/InstructorWithdrawalRepository.php
 M app/Repositories/Interaction/InstructorQuestionRepository.php
 M app/Repositories/Marketing/MarketingCouponRepository.php
 M app/Services/Instructor/InstructorWithdrawalService.php
 M app/Services/Interaction/InstructorQuestionService.php
 M app/Services/Payment/PaymentService.php
 M routes/api/instructor.php
 M routes/api/marketing.php
?? ../.vscode/
?? app/Http/Controllers/InstructorCouponController.php
?? app/Http/Controllers/InstructorNotificationController.php
?? app/Http/Controllers/InstructorPayoutAccountController.php
?? app/Http/Requests/Admin/AdminDashboardQueryRequest.php
?? app/Http/Requests/Admin/AdminNotificationQueryRequest.php
?? app/Http/Requests/Admin/AdminPayoutBatchRequest.php
?? app/Http/Requests/Admin/AdminPayoutQueryRequest.php
...
```
