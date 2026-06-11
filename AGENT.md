# AGENT.md

Behavioral guidelines to reduce common LLM coding mistakes. Merge with project-specific instructions as needed.

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

---

**These guidelines are working if:** fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.

---

## 5. Sử dụng CodeGraph & Ghi nhớ Agent Memory (ai-memory)

Để tối ưu hóa hiệu năng làm việc và tránh các lỗi sai phổ biến của LLM:

### A. Sử dụng CodeGraph để đọc Codebase
- **Đọc hiểu trước khi code**: Trước khi tiến hành chỉnh sửa hoặc thêm mới mã nguồn, bắt buộc sử dụng các công cụ của `codegraph` (`codegraph_search`, `codegraph_context`, `codegraph_callers`, v.v.) để tìm hiểu cấu trúc lớp (classes), phương thức (methods), các import và luồng gọi của các hàm liên quan.
- **Không tự giả định**: Tận dụng tối đa công cụ CodeGraph để kiểm chứng sự tồn tại và kiểu dữ liệu của các phương thức/thuộc tính trong codebase thay vì tự suy đoán.
- **Cập nhật index**: Sau khi pull code hoặc thay đổi cấu trúc tệp đáng kể, cần cập nhật index bằng cách chạy lệnh:
  ```powershell
  codegraph index [path_to_project]
  ```

### B. Ghi nhớ Agent Memory (`ai-memory`)
- **Ghi nhận lịch sử làm việc**: Sau khi hoàn thành một nhiệm vụ, một API mới hoặc một đợt phát triển tính năng, Agent phải ghi lại nhật ký vào thư mục `.agent/ai-memory/`.
- **Định dạng đặt tên tệp**: Đặt tên tệp theo định dạng `YYYY-MM-DD-FEATURE-ID.md` (Ví dụ: `2026-06-09-COURSE-04.md`).
- **Nội dung cấu trúc của file Memory**:
  1. **Tóm tắt nhiệm vụ**: Mô tả ngắn gọn nhiệm vụ, các API endpoints hoặc yêu cầu chức năng.
  2. **Quyết định kỹ thuật & Thiết kế**: Lý do lựa chọn giải pháp kỹ thuật, thiết kế DB, tối ưu hóa (eager loading, hasManyThrough), cơ chế validation, kiểm soát lỗi.
  3. **Các file đã chỉnh sửa & tạo mới**: Liệt kê các tệp đã tạo hoặc chỉnh sửa dưới dạng markdown links.
  4. **Trạng thái & Việc còn dở**: Xác nhận hoàn thành hoặc lưu ý những phần việc chưa làm xong để các Agent tiếp theo tiếp quản.