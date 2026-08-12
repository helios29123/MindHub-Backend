# CATEGORIES-REORDER: Cấu hình kéo thả toàn dòng & Thuật toán sắp xếp danh mục bằng ký tự (Pure Alphabetical Sort Order)

## 1. Tóm tắt nhiệm vụ
- Cải thiện tốc độ tải trang danh mục và tối ưu hóa truy vấn cơ sở dữ liệu.
- Thiết lập thuật toán sắp xếp danh mục theo ký tự (Lexicographical String Sort Order) thay thế cho số nguyên để tối ưu hiệu năng ghi DB (chỉ ghi 1 bản ghi thay vì dịch chuyển vị trí hàng loạt).
- Hỗ trợ tính năng kéo thả toàn bộ dòng danh mục trực tiếp thay thế cho việc phải kéo bằng biểu tượng grip handle nhỏ.
- Đảm bảo hiển thị trực quan số thứ tự tự nhiên (1, 2, 3, 2.1, 2.2) trên frontend.

## 2. Quyết định kỹ thuật & Thiết kế
- **Cơ sở dữ liệu (Database)**:
  - Chuyển đổi cột `sort_order` trong bảng `categories` từ kiểu số nguyên `INT` sang chuỗi `VARCHAR` thông qua migration `2026_08_01_140532_change_sort_order_to_string_in_categories_table.php`.
  - Chuẩn hóa lại toàn bộ chuỗi sắp xếp về bảng chữ cái thuần tự (`'a'`, `'b'`, `'c'`, `'d'`) bằng migration `2026_08_01_141210_convert_sort_order_to_alphabetical_in_categories_table.php`.
- **Backend API & Resources**:
  - Sửa đổi ép kiểu của thuộc tính `sort_order` trong `AdminCategoryResource.php` để giữ nguyên kiểu dữ liệu chuỗi (string) truyền về frontend thay vì ép sang `(int)`.
  - Cập nhật logic sinh vị trí tiếp theo `nextSortOrder` trong `AdminCategoryRepository.php` tự động cộng chữ cái (ví dụ: `'c'` -> `'d'`).
- **Frontend Kéo thả Toàn dòng & Hiệu ứng Nhấc dòng**:
  - Gỡ bỏ hoàn toàn cột biểu tượng grip handle kéo dọc (`GripVertical`) khỏi bảng.
  - Sử dụng thuộc tính `draggable` và style `-webkit-user-drag` trên thẻ `<tr>` đồng thời gán `select-none` động cho tất cả các thẻ `<td>` khi kéo thả để tránh xung đột bôi đen văn bản của Chrome.
  - Sử dụng mẹo trì hoãn trạng thái mờ dòng bằng `setTimeout(..., 0)` và gán `.category-name-container` làm ảnh bóng `setDragImage` giúp ảnh bóng di chuyển rõ nét theo con trỏ chuột, còn dòng thực tế dưới bảng mờ đi tạo hiệu ứng nhấc dòng.
- **Thuật toán sinh mã sắp xếp phân số cơ số 36 (Base-36 Fractional Indexing)**:
  - Cập nhật `generateSortOrderBetween` sử dụng tập ký tự an toàn `0-9a-z` làm cơ số 36 và áp dụng quy tắc ngăn chặn nghiêm ngặt việc kết thúc chuỗi bằng ký tự `'0'`. Khi kéo lên đầu trước `'1'`, nó sinh ra `'0m'`; tiếp tục kéo lên đầu sẽ sinh ra `'00m'`, `'000m'`,... Lớp đệm số `'0'` đóng vai trò như phần số thập phân mở rộng vô hạn.
  - Đảm bảo tính toán đồng bộ, tuyến tính và triệt tiêu hoàn toàn sự lệch pha sắp xếp giữa MySQL `ORDER BY` và JS `localeCompare("en")`.

## 3. Các file đã chỉnh sửa & tạo mới
- **Database Migrations (Tạo mới & chạy)**:
  - [2026_08_01_140532_change_sort_order_to_string_in_categories_table.php](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/database/migrations/2026_08_01_140532_change_sort_order_to_string_in_categories_table.php)
  - [2026_08_01_141210_convert_sort_order_to_alphabetical_in_categories_table.php](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/database/migrations/2026_08_01_141210_convert_sort_order_to_alphabetical_in_categories_table.php)
- **Backend (Chỉnh sửa)**:
  - [AdminCategoryResource.php](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/app/Http/Resources/Admin/AdminCategoryResource.php) (Giữ kiểu dữ liệu chuỗi cho `sort_order`).
  - [AdminCategoryRepository.php](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/app/Repositories/Admin/AdminCategoryRepository.php) (Cập nhật logic `nextSortOrder`).
  - [AdminCategoryService.php](file:///d:/laragon/www/MindHub/MindHub-Backend/BE/app/Services/Admin/AdminCategoryService.php) (Giữ kiểu dữ liệu chuỗi khi lưu danh mục).
- **Frontend (Chỉnh sửa)**:
  - [CategoriesPage.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/features/admin/categories/CategoriesPage.tsx) (Hàm sinh mã `generateSortOrderBetween` và logic đồng bộ sắp xếp tree view).
  - [CategoryRow.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/features/admin/categories/components/CategoryRow.tsx) (Kéo thả toàn dòng, hiệu ứng nhấc dòng và select-none động).

## 4. Trạng thái & Việc còn dở
- **Trạng thái**: Hoàn thành 100%. Đã chạy lại cơ sở dữ liệu và thử nghiệm kéo thả trên trình duyệt Chrome hoạt động hoàn hảo, lưu trữ chính xác.
- **Việc dở dang**: Không có.
