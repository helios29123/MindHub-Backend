# BÁO CÁO KẾT QUẢ SỬA ĐỔI “GIÁ BÁN” THEO PHẦN TRĂM GIẢM GIÁ KHÓA HỌC

## 1. Component Giá Bán
- **Tên màn hình**: Form tạo / chỉnh sửa khóa học của Instructor (`InstructorDashboard.tsx` & `CourseBuilderWizard.tsx`).
- **Thành phần giao diện**:
  - Ô nhập **Giá bán gốc (VND)** (Raw integer number).
  - Công tắc Toggle **"Áp dụng giá khuyến mãi"**.
  - Ô nhập **Phần trăm giảm giá (%)** (nhập số nguyên từ 1% đến 99%).
  - Khung Preview **Thực tế thanh toán** (Hiển thị định dạng VND tự động, read-only).

## 2. Schema Bảng `courses` Trước / Sau
- **Trước**:
  - `price DECIMAL(12,2) DEFAULT 0`
  - `sale_price DECIMAL(12,2) NULL`
- **Sau**:
  - `price DECIMAL(12,2) DEFAULT 0` (Giá bán gốc)
  - `discount_percent TINYINT UNSIGNED NULL` (Phần trăm giảm giá từ 1 đến 99)
  - `sale_price DECIMAL(12,2) NULL` (Giá thực tế thanh toán sau khi giảm)

## 3. Công Tắt Giảm Giá
- **Trạng thái Tắt (`has_discount = false`)**:
  - Ô "Phần trăm giảm giá (%)" bị vô hiệu hóa (disabled) hoặc ẩn.
  - Giá thực tế thanh toán bằng đúng Giá bán gốc (`sale_price = price`).
  - Gửi `discount_percent: null` và `has_discount: false` lên Backend.
  - Hiển thị mô tả: *"Khóa học đang bán theo giá gốc."*
- **Trạng thái Bật (`has_discount = true`)**:
  - Hiển thị và cho phép nhập ô "Phần trăm giảm giá (%)" từ 1 đến 99.
  - Hiển thị mô tả: *"Nhập tỷ lệ giảm từ 1% đến 99%."*

## 4. Công Thức Tính Giá Thực Tế Thanh Toán
```ts
sale_price = Math.round(original_price * (100 - discount_percent) / 100)
```
- Tự động làm tròn về số nguyên VND.
- Ví dụ:
  - Giá bán gốc: `499.000đ`
  - Phần trăm giảm: `40%`
  - Thực tế thanh toán: `299.400đ`

## 5. Payload Gửi Lên Backend
- **Khi có giảm giá**:
```json
{
  "original_price": 499000,
  "price": 499000,
  "has_discount": true,
  "discount_percent": 40
}
```
- **Khi không giảm giá**:
```json
{
  "original_price": 499000,
  "price": 499000,
  "has_discount": false,
  "discount_percent": null
}
```

## 6. Backend Validation & Safety
- **Logic tự tính toán tại Backend**: Backend không tin tưởng `sale_price` do Client gửi lên. Khi `has_discount = true` và `discount_percent` hợp lệ, Backend tự động tính toán lại `sale_price = round(original_price * (100 - discount_percent) / 100)`. Khi `has_discount = false`, `sale_price = original_price`.
- **Validation Rules**:
  - `original_price` / `price`: `required|numeric|min:1` (hoặc `min:0` trong bản nháp).
  - `has_discount`: `boolean`.
  - `discount_percent`: `nullable|integer|min:1|max:99|required_if:has_discount,true`.

## 7. Migration Database
- File migration mới: `database/migrations/2026_08_01_000000_add_discount_percent_to_courses_table.php`
- Đã chạy thành công: `2026_08_01_000000_add_discount_percent_to_courses_table .. 1s DONE`

## 8. Mapping Giá & Tương Thích Dữ Liệu Cũ
- Khi mở khóa học cũ để chỉnh sửa:
  - Nếu đã có `discount_percent > 0`: bật công tắc, điền đúng phần trăm.
  - Nếu chỉ có dữ liệu cũ `sale_price < price`: tự động tính ngược `discount_percent = Math.round((price - sale_price) / price * 100)` để hiển thị UI và bật công tắc.
  - Nếu không có giảm giá: tắt công tắc, để trống phần trăm.

## 9. Kiểm Thử (Create / Edit / Reload / API)
- Đã tạo và chạy test suite `CoursePriceTest` (`tests/Feature/CoursePriceTest.php`):
  1. Giá gốc 499,000, không giảm: `sale_price = 499000`, `discount_percent = null` -> **PASS**
  2. Giá gốc 499,000, giảm 40%: `sale_price = 299400`, `discount_percent = 40` -> **PASS**
  3. Giảm 1%: hợp lệ (`sale_price = 99000`) -> **PASS**
  4. Giảm 99%: hợp lệ (`sale_price = 1000`) -> **PASS**
  5. Giảm 0%: trả về HTTP 422 error -> **PASS**
  6. Giảm 100%: trả về HTTP 422 error -> **PASS**
  7. Tắt công tắc sau khi giảm 40%: `sale_price` khôi phục bằng giá gốc, `discount_percent = null` -> **PASS**

## 10. Danh Sách File Frontend Đã Sửa
- `src/shared/types.ts`: Bổ sung `discount_percent` & `has_discount` vào interface `Course`.
- `src/services/api.ts`: Cập nhật payload gửi API `createCourseDraft`, `updateCourseDraft`, `updateCourse`.
- `src/features/instructor/InstructorDashboard.tsx`: Bổ sung switch công tắc, ô % giảm giá, công thức tính live, validation UI, autosave draft & local storage persistence.
- `src/features/instructor/components/CourseBuilderWizard.tsx`: Đồng bộ giao diện và logic tính giá theo phần trăm.

## 11. Danh Sách File Backend Đã Sửa
- `database/migrations/2026_08_01_000000_add_discount_percent_to_courses_table.php` [NEW]
- `app/Models/Course.php`
- `app/Http/Requests/Instructor/StoreCourseRequest.php`
- `app/Http/Requests/Instructor/UpdateCourseRequest.php`
- `app/Http/Requests/Instructor/InstructorCourseDraftRequest.php`
- `app/Services/Instructor/InstructorCourseService.php`
- `app/Http/Resources/Instructor/InstructorCourseResource.php`
- `app/Http/Resources/Instructor/InstructorCourseDetailResource.php`
- `tests/Feature/CoursePriceTest.php` [NEW]

## 12. Typecheck Frontend
- Lệnh: `npx tsc --noEmit`
- Kết quả: **PASS** (0 errors).

## 13. Build Production Frontend
- Lệnh: `npm run build`
- Kết quả: **PASS** (`vite v6.4.3 building for production... dist/ index.html built successfully`).

---
*Ghi chú: Không có commit hoặc push Git theo yêu cầu.*
