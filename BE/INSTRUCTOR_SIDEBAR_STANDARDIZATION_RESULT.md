# BÁO CÁO KẾT QUẢ CHUẨN HÓA VÀ ĐỒNG BỘ SIDEBAR GIẢNG VIÊN

**Ngày thực hiện:** 24/07/2026  
**Dự án Frontend:** `F:\Phatnt\Documents\MindHub-Frontend`  
**Dự án Backend:** `F:\Phatnt\laragon\www\MindHub-Backend\be`

---

## 1. TỔNG QUAN AUDIT CÁC COMPONENT SIDEBAR

- **Tập trung nguồn cấu hình:** Tạo cấu hình tập trung [instructorNavigation.ts](file:///F:/Phatnt/Documents/MindHub-Frontend/src/config/instructorNavigation.ts) chứa mảng menu duy nhất `INSTRUCTOR_NAVIGATION_ITEMS`, hàm tính `getActiveNavigationKey()` và `getBreadcrumbLabel()`.
- **Component dùng chung:** Tạo component chuẩn hóa [InstructorSidebar.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/instructor-ui/InstructorSidebar.tsx) dùng chung cho cả desktop và mobile (Side Drawer).
- **Tránh duplicate:** Tích hợp `InstructorSidebar` vào [InstructorDashboard.tsx](file:///F:/Phatnt/Documents/MindHub-Frontend/src/components/InstructorDashboard.tsx) để toàn bộ 9 khu vực trang giảng viên chia sẻ cùng một sidebar duy nhất.

---

## 2. DANH SÁCH MENU VÀ XÓA MỤC "NỘI DUNG BÀI HỌC"

### Danh sách 9 Menu chuẩn sau khi sửa:
1. **Tổng quan** (`/instructor/dashboard`, Icon: `LayoutDashboard`)
2. **Khóa học của tôi** (`/instructor/courses`, Icon: `BookOpen`)
3. **Tạo khóa học** (`/instructor/courses/create`, Icon: `Plus`)
4. **Hỏi đáp & Bình luận** (`/instructor/questions`, Icon: `MessageSquare`)
5. **Học viên** (`/instructor/students`, Icon: `Users`)
6. **Doanh thu** (`/instructor/revenue`, Icon: `BarChart3`)
7. **Rút tiền** (`/instructor/withdrawals`, Icon: `DollarSign`)
8. **Mã giảm giá** (`/instructor/discount-codes`, Icon: `Tag`)
9. **Hồ sơ** (`/instructor/profile`, Icon: `UserRound`)

### Xóa mục "Nội dung bài học"
- **Đã xóa khỏi:** Cấu hình menu, JSX nút điều hướng sidebar, activeTab mapping rải rác, breadcrumb fallback.
- **Duy trì Route nội bộ:** Giữ nguyên route chỉnh sửa nội dung bài học/chương học khi người dùng thực hiện tạo/sửa khóa học (`/instructor/courses/:id/edit`).
- **Active state khi chỉnh sửa bài học:** Khi ở trang chỉnh sửa nội dung khóa học, menu active tương ứng là **"Khóa học của tôi"**.

---

## 3. KÍCH THƯỚC VÀ ĐỒNG BỘ GIAO DIỆN (DESKTOP & MOBILE)

- **Desktop Sidebar:**
  - Cố định chiều rộng: `w-[240px] min-w-[240px] flex-shrink-0 h-screen sticky top-0 bg-white border-r border-[#e7e8ed]`.
  - Header Logo: Logo icon 32px MindHub + nhãn INSTRUCTOR phân cách bằng border mảnh.
  - Active item: `bg-[#E6F0ED] text-[#007A64] font-bold shadow-2xs` mượt mà, không giật nhảy layout.
  - Bottom Footer: Khối card "Nâng cấp tài khoản" (Sparkles icon) và link "Trung tâm hỗ trợ" (`help.mindhub.vn`) hiển thị cố định ở gần đáy sidebar qua `mt-auto`.
- **Mobile & Tablet:**
  - Drawer slide-in mượt từ bên trái (`w-[280px] max-w-[85vw]`), overlay nền tối `bg-slate-900/60`.
  - Nút bấm Hamburger `<Menu />` ở Topbar để mở menu trên mobile.
  - Tự động đóng drawer khi click chọn menu, bấm nút X, click overlay hoặc bấm phím `Esc`. Lock body scroll khi mở drawer.
- **Topbar & Breadcrumb:**
  - Topbar xuất hiện ngay sau Sidebar (`h-16 bg-white border-b border-[#e7e8ed] px-6`).
  - Breadcrumb hiển thị động theo URL path `Giảng viên > [Tên Menu]`.

---

## 4. KẾT QUẢ BUILD VÀ VERIFICATION

```powershell
cd "F:\Phatnt\Documents\MindHub-Frontend"
npx tsc --noEmit
npm run build
```
- **Kết quả:** Build thành công 100% trong 11.43s (`dist/assets/index-uamJny5m.js`), 0 lỗi TypeScript.

---

## 5. ĐIỀU KIỆN HOÀN THÀNH

- [x] Toàn bộ trang giảng viên dùng cùng một nguồn cấu hình sidebar duy nhất.
- [x] Sidebar không đổi chiều rộng giữa các trang (`240px` fixed/sticky desktop).
- [x] Topbar thẳng hàng và đồng bộ breadcrumb theo URL.
- [x] Xóa hoàn toàn entry độc lập "Nội dung bài học" khỏi sidebar.
- [x] Khi ở trang chỉnh sửa bài học, menu active chuyển về "Khóa học của tôi".
- [x] Nâng cấp tài khoản & Trung tâm hỗ trợ nằm đúng đáy sidebar.
- [x] Mobile drawer dùng chung cấu hình menu.
- [x] Không reload document khi click menu (dùng React Router state/history).
- [x] Không commit hoặc push Git.
