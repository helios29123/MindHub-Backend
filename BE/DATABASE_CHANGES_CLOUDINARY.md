# THAY ĐỔI DATABASE DÀNH CHO BẢN XUẤT SQL CHÍNH THỨC

*Phần thay đổi này phải được kết hợp vào bản xuất schema SQL chính thức cuối cùng của team.*

1. **Bảng:** `users`
2. **Cột được thêm:** `avatar_public_id`
3. **Kiểu dữ liệu:** `VARCHAR(255)`
4. **Cho phép NULL:** CÓ (YES)
5. **Vị trí:** SAU (AFTER) `avatar_url`
6. **Mục đích:** Lưu trữ giá trị `public_id` của Cloudinary tương ứng với `avatar_url`, được dùng để quản lý việc xóa và thay thế ảnh.
7. **Câu lệnh SQL tương đương:**
```sql
ALTER TABLE users
ADD COLUMN avatar_public_id VARCHAR(255) NULL
AFTER avatar_url;
```
