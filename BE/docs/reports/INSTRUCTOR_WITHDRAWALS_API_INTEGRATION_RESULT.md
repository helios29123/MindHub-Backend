# BÁO CÁO KẾT QUẢ TÍCH HỢP API RÚT TIỀN GIẢNG VIÊN (INSTRUCTOR WITHDRAWALS)

## 1. Component Frontend đã Audit
- **Path**: `src/components/InstructorWithdrawal.tsx`
- **Component**: `InstructorWithdrawal`
- **Routing & Container**: Tích hợp trực tiếp tại `src/components/InstructorDashboard.tsx` (dưới route `/instructor/withdrawals`).

---

## 2. Mock / Hard-code đã Tìm thấy
- `INSTRUCTOR_WITHDRAW_MOCK` trong `src/data/instructorWithdrawMock.ts`.
- Các giá trị giả định ban đầu:
  - `withdrawableBalance`: 12.450.000 đ
  - `totalPendingWithdrawal`: 3.200.000 đ
  - `totalWithdrawn`: 28.750.000 đ
  - `totalRejected`: 1.000.000 đ
  - Payout Account hardcoded: Techcombank `1903 **** **** 1234`
- Danh sách 5 giao dịch mẫu trong mock file.

---

## 3. API Backend đã Có
Tất cả 8 API cho Rút tiền & Payout Account đều đã sẵn sàng trong Laravel Backend (`routes/api/instructor.php`):
1. `GET /api/instructor/withdrawals/summary` (`InstructorWithdrawalController@summary`)
2. `GET /api/instructor/withdrawals` (`InstructorWithdrawalController@index`)
3. `GET /api/instructor/withdrawals/{id}` (`InstructorWithdrawalController@show`)
4. `POST /api/instructor/withdrawals` (`InstructorWithdrawalController@store`)
5. `PATCH /api/instructor/withdrawals/{id}/cancel` (`InstructorWithdrawalController@cancel`)
6. `GET /api/instructor/payout-accounts` (`InstructorPayoutAccountController@index`)
7. `GET /api/instructor/payout-accounts/default` (`InstructorPayoutAccountController@default`)
8. `POST /api/instructor/payout-accounts` & `PATCH /api/instructor/payout-accounts/{id}` (`InstructorPayoutAccountController@store` / `update`)

---

## 4. API còn Thiếu
- Không có API core nào bị thiếu.

---

## 5. API đã Bổ sung / Mở rộng Field
- **Mở rộng `InstructorWithdrawalSummaryResource` & `InstructorWithdrawalRepository`**:
  - Bổ sung `rejected_withdraw_amount` (Tổng số tiền bị từ chối)
  - Bổ sung `pending_count` (Số lượng yêu cầu chờ duyệt)
  - Bổ sung `paid_count` (Số lượng yêu cầu đã chuyển)
  - Bổ sung `rejected_count` (Số lượng yêu cầu bị từ chối)

---

## 6. Business Rule Available Balance
- **Công thức chuẩn**:
  $$\text{Available Balance} = \max(\text{Available Revenue} - \text{Pending Withdrawals}, 0)$$
- **Logic**: Chỉ revenue có `status = 'available'` mới được tính vào thu nhập khả dụng. Toàn bộ các yêu cầu rút tiền đang ở trạng thái `pending` hoặc `approved` đều được trừ trực tiếp khỏi số dư khả dụng để chống rút quá hạn mức.

---

## 7. Minimum Withdrawal
- Số tiền rút tối thiểu: **200.000 đ** (`min: 200000`).
- Được kiểm tra và validate đồng bộ ở cả Frontend và Backend `InstructorWithdrawalService.php`.

---

## 8. Pending Balance Handling
- Khi instructor gửi yêu cầu rút $X$ đ, trạng thái yêu cầu là `pending`.
- `pending_withdraw_amount` tăng $X$ đ.
- `available_balance` giảm $X$ đ ngay lập tức.
- Nếu admin từ chối hoặc giảng viên hủy yêu cầu (`cancelled`), $X$ đ được giải phóng trở lại `available_balance`.

---

## 9. Summary API
- `GET /api/instructor/withdrawals/summary`
- Response trả về đầy đủ: `available_revenue`, `pending_withdraw_amount`, `paid_withdraw_amount`, `rejected_withdraw_amount`, `available_balance`, `pending_count`, `paid_count`, `rejected_count`, `can_create_withdrawal`, `payout_account`.

---

## 10. List API
- `GET /api/instructor/withdrawals`
- Hỗ trợ tham số: `page`, `per_page`, `status`, `date_from`, `date_to`.
- Output: Danh sách item có `id`, `display_code`, `amount`, `status`, `status_label`, `account`, `requested_at`, `approved_at`, `paid_at`, `rejected_reason`.

---

## 11. Detail API
- `GET /api/instructor/withdrawals/{id}`
- Trả về thông tin chi tiết của 1 yêu cầu rút tiền thuộc instructor đang đăng nhập.

---

## 12. Create API
- `POST /api/instructor/withdrawals`
- Payload contract: `{ "amount": 500000, "payout_account_id": 1, "note": "Rút tiền tháng 7" }`.
- Backend tự lấy `instructorId` từ Laravel Session Auth (`request()->user()->id`).

---

## 13. Cancel API
- `PATCH /api/instructor/withdrawals/{id}/cancel`
- Cho phép giảng viên chủ động hủy yêu cầu rút tiền khi status còn là `pending`.

---

## 14. Payout Account API
- `GET /api/instructor/payout-accounts/default` & `GET /api/instructor/payout-accounts`
- `POST /api/instructor/payout-accounts` & `PATCH /api/instructor/payout-accounts/{id}`
- Trả về tài khoản nhận tiền thật, mask số tài khoản an toàn (`account_number_masked`).

---

## 15. Snapshot Account
- Khi tạo `withdraw_requests`, Backend lưu snapshot `account_number_snapshot` và `account_name_snapshot` tại thời điểm tạo yêu cầu, bảo đảm lịch sử giao dịch không bị thay đổi khi instructor cập nhật tài khoản sau này.

---

## 16. Validation
- Validate `amount`: numeric, min 200.000, max available balance.
- Validate `payout_account_id`: bắt buộc, thuộc sở hữu của instructor, status `active`.
- Trả về lỗi 422 / 409 dạng JSON chuẩn.

---

## 17. Authorization
- Mọi query và mutation đều gắn chặt với `user_id = $request->user()->id`.
- Giảng viên A không thể xem, tạo, hủy hoặc dùng tài khoản payout của giảng viên B.

---

## 18. Race Condition & Dual Prevention
- Nút submit trên Frontend tự động disable và hiển thị loading spinner khi đang submit (`isSubmitting = true`).
- Backend sử dụng check balance trong transaction để ngăn việc rút âm số dư.

---

## 19. Deep Link & Navigation
- Hỗ trợ xem trang tại `/instructor/withdrawals`.
- Hỗ trợ xem chi tiết yêu cầu rút tiền và toast thông báo tương ứng.

---

## 20. Mapping Frontend
- `InstructorWithdrawal.tsx` đã loại bỏ hoàn toàn mock object `INSTRUCTOR_WITHDRAW_MOCK`.
- Sử dụng `Promise.allSettled` để load song song summary, payout account mặc định và danh sách yêu cầu.

---

## 21. Pagination
- Đấu nối trực tiếp với `meta` của API Backend (`current_page`, `last_page`, `per_page`, `total`).
- Hỗ trợ nút Previous/Next và chuyển trang mượt mà không reload trang.

---

## 22. Loading / Empty / Error
- Skeleton / Loader spinner khi đang tải dữ liệu.
- Empty state: `"Bạn chưa có yêu cầu rút tiền nào."` và `"Bạn chưa thiết lập tài khoản nhận tiền"`.
- Error state: `"Không thể tải chỉ số tổng quan"` khi API gặp lỗi, không biến lỗi API thành số 0.

---

## 23. Consistency với Revenue và Dashboard
- Nguồn dữ liệu số dư có thể rút (`available_balance`) của trang **Rút tiền** dùng chung một repository calculation (`InstructorWithdrawalRepository::getSummary`) với trang **Doanh thu** và **Dashboard**.

---

## 24. File Frontend đã Sửa
- [src/services/api.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/services/api.ts)
- [src/components/InstructorWithdrawal.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/InstructorWithdrawal.tsx)

---

## 25. File Backend đã Sửa
- [be/app/Repositories/Instructor/InstructorWithdrawalRepository.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Repositories/Instructor/InstructorWithdrawalRepository.php)
- [be/app/Http/Resources/Instructor/InstructorWithdrawalSummaryResource.php](file:///F:/Phatnt/laragon/www/MindHub-Backend/be/app/Http/Resources/Instructor/InstructorWithdrawalSummaryResource.php)

---

## 26. Kết quả Test
- `npx tsc --noEmit`: **PASSED (0 errors)**.
- `php artisan optimize:clear`: **PASSED**.

---

## 27. Kết quả Build
- `npm run build`: **PASSED in 1m 11s**.

---

## 28. Git Diff --Stat
```
Frontend:
 src/components/InstructorWithdrawal.tsx | 627 +++++++++++++++++++------------
 src/services/api.ts                     | 112 +++++-
 2 files changed, 480 insertions(+), 259 deletions(-)

Backend:
 app/Http/Resources/Instructor/InstructorWithdrawalSummaryResource.php | 4 ++++
 app/Repositories/Instructor/InstructorWithdrawalRepository.php        | 20 ++++++++++++++++++
 2 files changed, 24 insertions(+)
```

---

## 29. Phần còn Thiếu
- Không có. Tính năng rút tiền giảng viên đã hoàn thành tích hợp API thật 100%.
