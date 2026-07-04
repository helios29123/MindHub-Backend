# 2026-06-26-LEARN-VIDEO-FIX: Khắc phục trình phát Video HTML5 thực tế & Giải quyết xung đột Git

## 1. Tóm tắt nhiệm vụ
- Thay thế trình phát video bài giảng giả lập bằng thẻ HTML5 `<video>` thực tế trong giao diện học tập `ClassroomScreen.tsx`.
- Đồng bộ hóa các bộ điều khiển tùy chỉnh (play/pause, tốc độ phát, thanh tua thời gian) với trạng thái phát của video thực tế.
- Tự động phân giải đường dẫn video tương đối (như `/videos/laravel/intro.mp4`) trỏ về thư mục `/media/` trên Backend server.
- Giải quyết triệt để các xung đột Git (Merge Conflict) phát sinh khi kéo code từ remote `origin/main` trong lúc đang lưu trữ thay đổi ở Local Stash.

## 2. Quyết định kỹ thuật & Thiết kế
- **Tải và Đồng bộ Video HTML5**:
  - Sử dụng React `useRef<HTMLVideoElement>` để quản lý thẻ `<video>` thực tế.
  - Lắng nghe các sự kiện native `onTimeUpdate` để cập nhật `videoTime` state, `onDurationChange` để cập nhật `totalVideoDuration` state động, và `onEnded` để tự động kích hoạt tiến độ hoàn thành bài học.
  - Sử dụng `useEffect` để đồng bộ hóa trạng thái `isPlaying` (play/pause) và `videoSpeed` (playbackRate) với thẻ video.
  - Lắng nghe sự kiện `loadedmetadata` để tự động tua (seek) video về vị trí giây đã lưu gần nhất (`localStorage` hoặc tiến độ tải từ Backend) khi chuyển đổi bài học.
- **Phân giải URL Video (`getAbsoluteVideoUrl`)**:
  - Ánh xạ các đường dẫn tương đối bắt đầu bằng `/videos/` hoặc `/uploads/` trỏ về `[Server_Base_URL]/media/...` để tải đúng tài nguyên video đã upload trên hosting.
- **Giải quyết xung đột Git (Merge Conflict)**:
  - Khôi phục tệp `src/components/ModeratorTab.tsx` từ stash bằng cách chạy checkout cụ thể từ stash ID.
  - Giữ lại mảng tài khoản seed `DB_SEED_ACCOUNTS` và hàm gọi đăng ký `register({ ... })` dạng object mới từ remote để đảm bảo đồng bộ hệ thống.
  - Gộp thành công các state học tập cục bộ (`chapters`, `activeLessonDetail`) với logic kiểm tra an toàn danh sách bài học của server trong `ClassroomScreen.tsx`.
  - Staging tất cả tệp đã resolve bằng `git add` và dọn dẹp Git stash bằng `git stash drop`.

## 3. Các file đã chỉnh sửa & tạo mới
- **Frontend Components**:
  - [ClassroomScreen.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/components/ClassroomScreen.tsx) (Chỉnh sửa - Tích hợp video HTML5, sync events và seek progress)
  - [AdminDashboard.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/components/AdminDashboard.tsx) (Chỉnh sửa - Giải quyết xung đột Git conflict markers bao quanh ModeratorTab)
  - [AuthScreens.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/components/AuthScreens.tsx) (Chỉnh sửa - Giải quyết xung đột seed accounts và API register payload)
  - [ModeratorTab.tsx](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/components/ModeratorTab.tsx) (Tạo mới/Khôi phục từ stash để phân tách component)
- **Frontend Services**:
  - [api.ts](file:///d:/laragon/www/MindHub/MindHub-Frontend/src/services/api.ts) (Khôi phục từ bản HEAD để giải quyết xung đột sạch sẽ)

## 4. Trạng thái & Việc còn dở
- **Trạng thái**: Hoàn thành 100%. Đã chạy thử lệnh `npm run build` thành công, không phát sinh lỗi biên dịch và đã thực hiện deploy phiên bản hoàn thiện lên máy chủ production (`https://mindhub.io.vn`).
- **Việc dở dang**: Không có. Trình phát video đã hoạt động ổn định và tất cả các mục trong `unconnected_api_checklist.md` đã được kết nối hoàn thiện.
