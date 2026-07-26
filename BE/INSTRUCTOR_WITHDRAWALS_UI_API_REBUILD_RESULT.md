# BÁO CÁO KẾT QUẢ TÁI THIẾT KẾ GIAO DIỆN VÀ NỐI API THẬT TRANG RÚT TIỀN GIẢNG VIÊN

**Ngày thực hiện:** 24/07/2026  
**Dự án Frontend:** `F:\Phatnt\Documents\MindHub-Frontend`  
**Dự án Backend:** `F:\Phatnt\laragon\www\MindHub-Backend\be`  
**Trang mục tiêu:** `http://127.0.0.1:3000/instructor/withdrawals`

---

## 1. COMPONENT DÃ AUDIT VÀ THAY ĐỔI GIAO DIỆN

### Component Audit
- **Primary Component:** [InstructorWithdrawal.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/InstructorWithdrawal.tsx)
- **API Service:** [api.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/services/api.ts) (phương thức `getInstructorWithdrawalSummary`, `getInstructorWithdrawals`, `createInstructorWithdrawal`, `cancelInstructorWithdrawal`, `getInstructorPayoutAccounts`, `setDefaultInstructorPayoutAccount`).

### Giao diện cũ & Lỗi trước khi xử lý
- **Lỗi giao diện:** Giao diện trắng nhạt, thiếu các ô màu làm nổi bật chỉ số, các nút thao tác chưa chuẩn hóa, thiếu banner lưu ý chu kỳ chuyển tiền, drawer tạo yêu cầu thiếu các lưu ý quan trọng.
- **Lỗi dữ liệu:** Một số thành phần dùng fallback mock `instructorWithdrawMock.ts`.

### Giao diện mới (Theo 100% Ảnh Mẫu Cung Cấp)
1. **Header Trang:** Tiêu đề "Rút tiền" đậm nét, subtitle "Quản lý tài khoản nhận tiền và các yêu cầu rút tiền của bạn.", icon thông báo & trợ giúp bên phải.
2. **4 Card Tổng quan (Color-coded indicators):**
   - **Số dư có thể rút:** Nền trắng, icon Wallet màu xanh dương (`bg-blue-50 text-blue-600`), số tiền lớn font-black `12.450.000đ`, dòng phụ "Đã bao gồm phí nền tảng".
   - **Đang chờ duyệt:** Icon Clock màu cam (`bg-amber-50 text-amber-600`), số tiền font-black `3.200.000đ`, dòng phụ "X yêu cầu".
   - **Đã chuyển:** Icon CheckCircle màu xanh lá (`bg-emerald-50 text-emerald-600`), số tiền font-black `28.750.000đ`, dòng phụ "Tổng X giao dịch".
   - **Bị từ chối:** Icon XCircle màu đỏ (`bg-rose-50 text-rose-600`), số tiền font-black `1.000.000đ`, dòng phụ "Tổng X giao dịch".
3. **Card Tài khoản nhận tiền:**
   - Tiêu đề với icon khiên bảo mật (`ShieldCheck`), nút "Cập nhật tài khoản nhận tiền" ở góc phải.
   - Logo ngân hàng (Techcombank) đóng khung nổi bật, thông tin Ngân hàng, Số tài khoản (masked + icon Eye ẩn/hiện), Chủ tài khoản (viết hoa), Badge "Đã xác minh" xanh lá + thời gian xác minh.
4. **Banner chu kỳ chuyển tiền:**
   - Nền xanh dương nhạt (`bg-blue-50/70 border border-blue-200`), icon Info, câu thông báo lưu ý chu kỳ chuyển tiền ngày 05–10 hàng tháng và nút "Tìm hiểu thêm".
5. **Bảng Danh sách yêu cầu rút tiền:**
   - Tiêu đề "Danh sách yêu cầu rút tiền" + nút bấm màu xanh "Tạo yêu cầu rút tiền +".
   - Các cột: Ngày tạo, Số tiền, Tài khoản nhận (hiển thị 2 dòng), Trạng thái (pill badge phân màu), Ghi chú admin, Thao tác (`👁 Xem chi tiết` và nút `Hủy` cho yêu cầu đang chờ).
   - Phân trang chuẩn: "Hiển thị X đến Y của Z yêu cầu" + các nút chuyển trang `< 1 2 3 >`.
6. **Side Drawer "Tạo yêu cầu rút tiền":**
   - Nằm ở cột bên phải (~30% màn hình), header sticky + nút đóng X.
   - Khối thông báo chu kỳ, hiển thị số dư có thể rút, input nhập số tiền (có nút "Rút tối đa" và định dạng tiền), dropdown chọn tài khoản nhận tiền (+ nút Thêm tài khoản mới), textarea ghi chú (đếm 0/200 ký tự), khối 3 lưu ý quan trọng.
   - Sticky footer: Nút "Hủy" và nút "Gửi yêu cầu" (nổi bật màu xanh `#0066FF`).

---

## 2. MA TRẬN KẾT NỐI API BACKEND THẬT

| Chức năng FE | Endpoint Backend | Method | Trạng thái | Ghi chú nghiệp vụ |
|---|---|---|---|---|
| 1. Tổng quan rút tiền | `/api/instructor/withdrawals/summary` | GET | **AVAILABLE (200)** | Lấy số dư khả dụng, tiền chờ duyệt, tiền đã chuyển, tiền từ chối & đếm số lượng. |
| 2. Danh sách rút tiền | `/api/instructor/withdrawals` | GET | **AVAILABLE (200)** | Hỗ trợ query `page`, `per_page`, `status`. Trả về danh sách và metadata phân trang `meta`. |
| 3. Tạo yêu cầu rút tiền | `/api/instructor/withdrawals` | POST | **AVAILABLE (201)** | Payload: `{ amount, payout_account_id, note }`. Trừ số dư & tăng tiền chờ duyệt tức thì. |
| 4. Chi tiết rút tiền | `/api/instructor/withdrawals/{id}` | GET | **AVAILABLE (200)** | Trả về chi tiết mã giao dịch, snapshot tài khoản, thời gian duyệt/chuyển tiền/lý do từ chối. |
| 5. Hủy yêu cầu rút tiền | `/api/instructor/withdrawals/{id}/cancel` | PATCH | **AVAILABLE (200)** | Hủy yêu cầu ở trạng thái `pending` và hoàn lại số dư có thể rút. |
| 6. Danh sách TK nhận tiền | `/api/instructor/payout-accounts` | GET | **AVAILABLE (200)** | Trả về danh sách các ngân hàng / ví điện tử đã liên kết của giảng viên. |
| 7. Tạo TK nhận tiền | `/api/instructor/payout-accounts` | POST | **AVAILABLE (201)** | Payload: `{ provider, account_number, account_name }`. Tự động đặt làm mặc định nếu là TK đầu tiên. |
| 8. Đặt TK mặc định | `/api/instructor/payout-accounts/{id}/set-default` | PATCH | **AVAILABLE (200)** | Đặt tài khoản được chọn làm mặc định cho các lần rút tiền sau. |

---

## 3. RÀNG BUỘC NGHIỆP VỤ & CHỐNG DOUBLE SUBMIT

- **Kiểm tra số dư:** Không cho phép rút số tiền vượt quá `available_balance` hoặc ít hơn `200.000đ`.
- **Chống Double Submit:** Vô hiệu hóa nút bấm và hiển thị spinner `Loader2` khi đang gửi request.
- **Không Reload toàn trang:** Tất cả thao tác tạo yêu cầu, chuyển trang, cập nhật tài khoản, xem chi tiết, hủy yêu cầu đều cập nhật React State và refetch API trong background. Không dùng `window.location.reload()`.

---

## 4. KẾT QUẢ BUILD VÀ KIỂM THỬ

### Frontend Build & TypeScript Check
```powershell
npx tsc --noEmit
npm run build
```
- **Kết quả:** Build thành công 100% trong 9.71s (`dist/assets/index-C2lnW3Lm.js`), 0 lỗi TypeScript.

### Backend Test Suite (PHPUnit / Pest)
```powershell
php artisan test --filter=InstructorWithdrawalApiTest
php artisan test --filter=InstructorWithdrawalValidationTest
```
- **Kết quả:** `Passed 36 / 36 tests` (138 assertions), 0 errors.

---

## 5. ĐIỀU KIỆN HOÀN THÀNH

- [x] Giao diện mới 100% giống tinh thần ảnh mẫu (gọn gàng, hiện đại, phối màu chuẩn).
- [x] Giữ nguyên Sidebar/Topbar hiện tại của dự án.
- [x] 4 Card tổng quan dùng API thật (`available_balance`, `pending`, `paid`, `rejected`).
- [x] Payout account dùng API thật và hỗ trợ cập nhật/thêm mới.
- [x] Bảng danh sách withdrawal dùng API thật và phân trang mượt mà.
- [x] Drawer tạo yêu cầu rút tiền hoạt động đầy đủ validation và format tiền.
- [x] Xem chi tiết & Hủy yêu cầu hoạt động từ API thật.
- [x] Không reload toàn trang.
- [x] Không commit hoặc push Git.
