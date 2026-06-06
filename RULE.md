# 02_AI_CODING_RULES.md

> File này là bộ quy tắc ngắn gọn để AI viết code Laravel thống nhất trong dự án.  
> Chỉ dùng cho phần AI trực tiếp sinh code. Không dùng để mô tả ERD, database chi tiết, phân công nhóm, Git workflow hay báo cáo.

---

## 1. Naming Convention

AI phải đặt tên rõ nghĩa, thống nhất và đúng convention Laravel/PHP.

### Bắt buộc

- Class dùng `PascalCase`.
  - Đúng: `CourseController`, `PaymentService`, `OrderRepository`, `StoreCourseRequest`
- Method dùng `camelCase`.
  - Đúng: `createCourse()`, `updateLesson()`, `getUserProfile()`
- Variable dùng `camelCase`.
  - Đúng: `$courseData`, `$totalAmount`, `$currentUser`
- Constant dùng `UPPER_CASE`.
  - Đúng: `DEFAULT_PAGE_SIZE`, `MAX_LOGIN_ATTEMPTS`

### Không được làm

- Không đặt tên mơ hồ như `$data1`, `$x`, `$temp`, `handleData()` nếu có thể đặt rõ hơn.
- Không dùng nhiều kiểu đặt tên lẫn lộn trong cùng một module.
- Không đổi tên class, method, biến quan trọng nếu đặc tả đã quy định sẵn.

---

## 2. Controller Convention

Controller chỉ điều phối request/response, không xử lý nghiệp vụ chính.

### Controller được làm

- Nhận request.
- Gọi Form Request để lấy dữ liệu đã validate.
- Gọi Service.
- Trả về Resource hoặc JSON response chuẩn.

### Controller không được làm

- Không viết business logic trong Controller.
- Không query database phức tạp trong Controller.
- Không validate trực tiếp bằng `$request->validate()` nếu chức năng create/update cần Form Request.
- Không xử lý transaction lớn trong Controller.
- Không gọi Model trực tiếp cho nghiệp vụ phức tạp.

### Mẫu đúng

```php
public function store(StoreCourseRequest $request)
{
    $course = $this->courseService->create($request->validated());

    return new CourseResource($course);
}
```

---

## 3. Form Request Validation

Validation phải tách ra Form Request đối với các API create/update hoặc API có input quan trọng.

### Bắt buộc

- Dùng Form Request cho create/update.
- Rule validation phải rõ ràng.
- Custom message nên dễ hiểu.
- Controller chỉ dùng `$request->validated()`.

### Không được làm

- Không validate inline trong Controller cho request phức tạp.
- Không bỏ qua validation input.
- Không tin dữ liệu client gửi lên.

### Mẫu đúng

```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'price' => ['required', 'numeric', 'min:0'],
    ];
}
```

---

## 4. Service Rule

Service chứa business logic chính của module.

### Service được làm

- Xử lý nghiệp vụ.
- Gọi Repository để thao tác database.
- Dùng transaction khi thao tác nhiều bảng liên quan.
- Kiểm tra điều kiện nghiệp vụ trước khi tạo/sửa/xóa dữ liệu.

### Service không được làm

- Không return raw HTTP response.
- Không format response API.
- Không chứa validation rule thay cho Form Request.
- Không chứa query quá chi tiết nếu đã dùng Repository.

### Mẫu đúng

```php
public function create(array $data): Course
{
    return DB::transaction(function () use ($data) {
        return $this->courseRepository->create($data);
    });
}
```

---

## 5. Repository Rule

Repository chỉ xử lý truy vấn database.

### Repository được làm

- `create`, `update`, `delete`, `find`, `paginate`.
- Viết query Eloquent/Query Builder.
- Gom các query lặp lại trong module.

### Repository không được làm

- Không xử lý business logic.
- Không kiểm tra quyền user.
- Không format response API.
- Không validate request.

### Mẫu đúng

```php
public function create(array $data): Course
{
    return Course::create($data);
}
```

---

## 6. Resource / API Response Rule

API response phải thống nhất để frontend dễ xử lý.

### Success response

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

### Error response

```json
{
  "success": false,
  "message": "Something went wrong",
  "errors": {}
}
```

### Pagination response

```json
{
  "success": true,
  "message": "Fetched successfully",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 0
  }
}
```

### Bắt buộc

- Dùng API Resource để format dữ liệu trả về khi response có model/entity.
- Không trả dữ liệu thô nếu cần che field nhạy cảm.
- Không trả password, token hash, secret key trong response.

---

## 7. Request Flow Rule

AI phải viết code theo flow chuẩn sau:

```txt
Request
→ Form Request
→ Controller
→ Service
→ Repository
→ Model / Database
→ Resource
→ JSON Response
```

### Không được đảo flow

Sai:

```txt
Controller → Model trực tiếp → Response
```

Đúng:

```txt
Controller → Service → Repository → Resource/Response
```

---

## 8. HTTP Status Code Rule

AI phải dùng đúng HTTP status code.

| Status | Ý nghĩa |
|---|---|
| `200` | Lấy dữ liệu / cập nhật / xóa thành công |
| `201` | Tạo mới thành công |
| `400` | Request sai logic nghiệp vụ |
| `401` | Chưa đăng nhập / thiếu token |
| `403` | Không có quyền |
| `404` | Không tìm thấy dữ liệu |
| `422` | Validation failed |
| `500` | Lỗi server |

---

## 9. Security Rule

### Bắt buộc

- Validate tất cả input quan trọng.
- Kiểm tra quyền bằng Policy/Gate khi thao tác dữ liệu thuộc user hoặc role cụ thể.
- Không trả raw exception ra ngoài API.
- Không hardcode token, password, secret key.
- Không expose field nhạy cảm trong Resource.

### Không được làm

- Không viết `.env` thật vào code.
- Không hardcode thông tin database, API key, payment secret.
- Không cho user sửa/xóa dữ liệu của người khác nếu chưa kiểm tra quyền.
- Không bỏ qua auth middleware với API cần đăng nhập.

---

## 10. Migration Rule Chung

File này không mô tả chi tiết bảng/cột. Khi cần viết migration, AI phải dựa vào `DATABASE_SPEC.md` hoặc ERD được cung cấp riêng.

### Bắt buộc

- Chỉ sinh migration theo database spec/ERD đã được cung cấp.
- Không tự ý thêm bảng/cột ngoài đặc tả.
- Migration phải có `up()` và `down()`.
- Migration phải rollback được.
- Dùng foreign key nếu quan hệ đã được mô tả trong database spec.

### Không được làm

- Không tự bịa field vì “có vẻ cần”.
- Không sửa migration cũ nếu không được yêu cầu.
- Không đổi tên bảng/cột nếu đặc tả đã chốt.

---

## 11. Policy / Gate Rule

Dùng Policy/Gate cho các chức năng cần phân quyền theo user, role hoặc quyền sở hữu dữ liệu.

### Khi nào cần Policy/Gate

- User chỉ được sửa/xóa dữ liệu của chính mình.
- Instructor chỉ được quản lý khóa học của mình.
- Admin có quyền quản lý toàn bộ.
- Student chỉ được học khóa đã mua hoặc được cấp quyền.

### Không được làm

- Không chỉ kiểm tra role bằng if rải rác lung tung trong Controller.
- Không bỏ qua authorization với chức năng update/delete.
- Không để user truyền `user_id` lên rồi tin luôn.

---

## 12. Exception Handling Rule

AI phải xử lý lỗi rõ ràng, không làm API văng lỗi thô.

### Bắt buộc

- Lỗi validation trả `422`.
- Lỗi không tìm thấy dữ liệu trả `404`.
- Lỗi không có quyền trả `403`.
- Lỗi chưa đăng nhập trả `401`.
- Lỗi nghiệp vụ trả `400` hoặc status phù hợp.

### Không được làm

- Không trả raw stack trace.
- Không dùng `dd()`, `dump()`, `var_dump()` trong code hoàn chỉnh.
- Không catch exception rồi nuốt lỗi im lặng.

---

## 13. AI Must Not Do

AI tuyệt đối không được:

- Không tự ý thêm chức năng ngoài yêu cầu.
- Không tự ý đổi kiến trúc module.
- Không tự ý đổi tên bảng, cột, model, route nếu spec đã có.
- Không viết toàn bộ logic trong Controller.
- Không bỏ qua Form Request với API create/update.
- Không bỏ qua response format chuẩn.
- Không tạo code debug trong bản hoàn chỉnh.
- Không hardcode secret hoặc dữ liệu nhạy cảm.
- Không tự giả định đã chạy migration, seed, composer install hoặc cấu hình `.env`.
- Nếu thiếu thông tin quan trọng, phải ghi rõ `Missing Information` thay vì tự bịa.

---

## 14. Output Rule Khi AI Sinh Code

Khi được yêu cầu code một module, AI phải trả kết quả theo format:

```md
## Files To Create / Update
- ...

## Code
### File: app/Http/Controllers/...
```php
...
```

### File: app/Services/...
```php
...
```

## Commands Needed
```bash
php artisan make:...
php artisan migrate
```

## Notes
- Missing Information nếu có.
- Các giả định nếu bắt buộc phải giả định.
```
