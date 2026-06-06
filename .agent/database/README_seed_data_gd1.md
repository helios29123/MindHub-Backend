

## 3. Cảnh báo khi chạy seed

File seed có xóa dữ liệu cũ trong các bảng GD1.

Chỉ dùng cho:

- local
- dev
- test database
- demo database

Không chạy trên production.

Nếu chạy trên database thật, dữ liệu cũ có thể bị xóa.

## 4. Quy ước mật khẩu test

Mật khẩu test dùng chung cho các tài khoản có password:

```text
12345678
```

Tuy nhiên cần chú ý:

Trong file SQL seed, cột `password_hash` có thể đang là chuỗi hash mẫu/placeholder. Nếu hash đó không phải bcrypt hợp lệ do Laravel tạo ra thì đăng nhập bằng `12345678` sẽ không thành công.

Sau khi chạy seed, nên chạy thêm câu SQL dưới đây để cập nhật password hash thật cho toàn bộ tài khoản test có mật khẩu:

```sql
UPDATE users
SET password_hash = '$2y$12$SSAm6kDSPo2QJxt.abZtL.BSXPbFxfHCVp/BFpHCkIscDFO2dONU6'
WHERE email IN (
    'admin@mindhub.test',
    'instructor1@mindhub.test',
    'instructor2@mindhub.test',
    'learner1@mindhub.test',
    'learner2@mindhub.test',
    'learner.completed@mindhub.test',
    'locked@mindhub.test',
    'inactive@mindhub.test'
);
```

Hash trên là bcrypt cho mật khẩu:

```text
12345678
```

Tài khoản OAuth `oauth.learner@mindhub.test` có `password_hash = NULL`, không dùng để test đăng nhập email/password.

## 5. Lưu ý cho Laravel Auth

Schema hiện tại dùng cột:

```text
users.password_hash
```

Nếu code Laravel Auth mặc định đang dùng cột `password`, cần xử lý một trong hai cách:

### Cách 1: Đổi logic đăng nhập để kiểm tra `password_hash`

Ví dụ:

```php
Hash::check($request->password, $user->password_hash)
```

### Cách 2: Trong User model, override password field

```php
public function getAuthPassword()
{
    return $this->password_hash;
}
```

Nếu không xử lý điểm này, dữ liệu seed đúng nhưng login vẫn fail vì Laravel tìm sai cột password.

## 6. Danh sách tài khoản test

| Email | Password | Role | Status | Mục đích test |
|---|---|---|---|---|
| `admin@mindhub.test` | `12345678` | admin | active | Test API admin, duyệt khóa, duyệt rút tiền |
| `instructor1@mindhub.test` | `12345678` | instructor | active | Giảng viên chính, có nhiều khóa học |
| `instructor2@mindhub.test` | `12345678` | instructor | active | Test phân quyền không được sửa khóa của instructor khác |
| `learner1@mindhub.test` | `12345678` | learner | active | Learner đã mua khóa Laravel |
| `learner2@mindhub.test` | `12345678` | learner | active | Learner chưa mua Laravel, có order pending/failed |
| `learner.completed@mindhub.test` | `12345678` | learner | active | Learner đã hoàn thành khóa |
| `locked@mindhub.test` | `12345678` | learner | locked | Test tài khoản bị khóa |
| `inactive@mindhub.test` | `12345678` | learner | inactive | Test tài khoản inactive/chưa kích hoạt |
| `oauth.learner@mindhub.test` | Không dùng password | learner | active | Test OAuth account |

## 7. Quy ước dữ liệu course

| ID | Course | Status | Mục đích test |
|---|---|---|---|
| 1 | Laravel REST API Cơ Bản | published | Khóa chính để test mua, học, review, comment, quiz |
| 2 | PHP MVC Nền Tảng | pending_review | Test admin duyệt/từ chối khóa |
| 3 | SQL Database Thực Chiến | approved | Test khóa đã duyệt nhưng chưa publish |
| 4 | ReactJS Cơ Bản | draft | Test course draft/instructor ownership |
| 5 | NodeJS Hidden Course | hidden | Test khóa bị ẩn, không public, không cho mua |
| 6 | Khóa Bị Từ Chối | rejected | Test khóa bị admin từ chối |
| 7 | Git Cơ Bản Miễn Phí | published | Test khóa miễn phí/order 0 đồng |

## 8. Quy ước order/payment

| Order code | User | Course | Status | Payment status | Có enrollment? | Mục đích test |
|---|---|---|---|---|---|---|
| `ORD-2026-0001` | learner1 | Laravel | paid | paid | Có | Mua khóa thành công |
| `ORD-2026-0002` | learner2 | Laravel | pending | unpaid | Không | Chưa thanh toán |
| `ORD-2026-0003` | learner2 | Laravel | failed | failed | Không | Thanh toán thất bại |
| `ORD-2026-0004` | learner2 | Hidden course | cancelled | unpaid | Không | Đơn bị hủy |
| `ORD-2026-0005` | oauth learner | Laravel | expired | unpaid | Không | Đơn quá hạn |
| `ORD-2026-0006` | learner completed | SQL | paid | paid | Có | Học viên hoàn thành khóa |
| `ORD-2026-0007` | learner2 | Git free | paid | paid | Có | Khóa miễn phí |

Rule quan trọng:

```text
Chỉ order paid/payment_status paid mới được tạo enrollment.
Order pending/failed/cancelled/expired không được có enrollment.
```

## 9. Quy ước coupon

| Code | Status | Mục đích |
|---|---|---|
| `WELCOME100` | active | Coupon giảm cố định cho Laravel |
| `GLOBAL10` | active | Coupon giảm phần trăm toàn hệ thống |
| `OLD50` | expired | Test coupon hết hạn |
| `OFFLINE20` | inactive | Test coupon bị tắt |
| `FULLUSED` | used_up | Test coupon hết lượt |
| `FREEGIT` | active | Test flow khóa miễn phí/Git |

## 10. Các rule nghiệp vụ cần nhớ khi test API

### Auth

- User `active` được đăng nhập.
- User `locked` không được đăng nhập.
- User `inactive` không được đăng nhập.
- Không trả `password_hash` ra response.
- Tài khoản OAuth không dùng email/password thường.

### Course

- Guest/learner chỉ được thấy course `published`.
- Course `draft`, `pending_review`, `approved`, `rejected`, `hidden` không được public như course bình thường.
- Instructor chỉ được sửa khóa học của mình.
- Admin được duyệt/từ chối khóa học.

### Payment/Enrollment

- Tạo order chưa đồng nghĩa có quyền học.
- Chỉ sau khi order `paid` và payment_status `paid` mới tạo enrollment.
- Không cho learner mua lại khóa đã có enrollment.
- Không cho mua course chưa `published`.
- Coupon phải hợp lệ về status, hạn dùng, lượt dùng và course áp dụng.

### Learning

- Lesson `is_preview = true` có thể cho xem trước.
- Lesson không preview cần enrollment.
- Lesson `hidden` không được public.
- Chỉ learner có enrollment mới có progress hợp lệ.

### Review/Comment

- Review nên gắn với order đã paid.
- Một order chỉ nên review một lần nếu database có unique constraint.
- Comment có thể có reply qua `parent_id`.
- Comment `hidden/deleted` không hiển thị như comment bình thường.

### Quiz

- Quiz `published` mới cho learner làm.
- Attempt `in_progress` là đang làm.
- Attempt `submitted` là đã nộp.
- Không cho nộp lại attempt đã submitted nếu rule hệ thống không cho phép.
- Câu hỏi phải có option đúng.

### Finance/Withdraw

- Revenue sinh từ order paid.
- Instructor chỉ xem doanh thu của mình.
- Withdraw request có các trạng thái: pending, approved, rejected, paid, cancelled.
- Request rejected phải có lý do từ chối.

## 11. Checklist sau khi chạy seed

- [ ] Chạy schema thành công.
- [ ] Chạy seed không lỗi foreign key.
- [ ] Chạy câu update bcrypt password nếu cần.
- [ ] Login được bằng `admin@mindhub.test / 12345678`.
- [ ] Login được bằng `learner1@mindhub.test / 12345678`.
- [ ] Login với `locked@mindhub.test` bị chặn đúng.
- [ ] API danh sách khóa học chỉ hiện course published.
- [ ] Learner đã mua xem được bài học trả phí.
- [ ] Learner chưa mua bị chặn khi xem bài học trả phí.
- [ ] Order pending/failed không tạo enrollment.
- [ ] Quiz có câu hỏi và đáp án.
- [ ] Instructor xem được revenue của mình.
- [ ] Admin xử lý withdraw request được.

## 12. Gợi ý mô tả ngắn để dán vào Trello

```text
Seed data GD1 dùng để chèn dữ liệu mẫu phục vụ test API E-learning. File này không phải database hoàn chỉnh, mà là file xóa dữ liệu cũ và insert dữ liệu mẫu vào schema đã tạo sẵn. Dữ liệu được thiết kế theo các tình huống test thực tế: login active/locked/inactive, course nhiều trạng thái, order paid/pending/failed/cancelled/expired, enrollment hợp lệ, lesson preview/non-preview, comment/review, quiz, revenue và withdraw. Khi test, dùng password quy ước 12345678 cho các account test, nhưng cần đảm bảo password_hash là bcrypt hợp lệ hoặc cập nhật lại bằng câu SQL trong README.
```

## 13. Thứ tự sử dụng cho team

```text
1. Pull code/database mới nhất.
2. Tạo database local.
3. Chạy schema SQL.
4. Chạy seed SQL.
5. Cập nhật password hash thật nếu cần.
6. Mở README để lấy account test.
7. Test API theo role và tình huống.
8. Nếu API fail, kiểm tra lại:
   - status dữ liệu
   - enrollment/order liên quan
   - role user
   - password_hash
   - điều kiện nghiệp vụ trong code
```
