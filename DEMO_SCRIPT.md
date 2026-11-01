# Kịch bản demo báo cáo — Bài 6–8 (10 phút)

## 0:00–1:00 — Bài toán và vai trò
- Giới thiệu Bếp Nhà Hà Store: bán đồ ăn/đồ uống, quản lý sản phẩm, khách đặt món.
- Vai trò: admin quản trị menu; customer tìm kiếm/đặt/hủy đơn của mình; staff không có quyền admin trong phạm vi demo.

## 1:00–2:30 — CSDL/ERD và ràng buộc
- Mở `database/schema.sql`: `roles → users`, `categories → products`, `users → orders → order_items`.
- Chỉ ra UNIQUE SKU/email, foreign keys, `CHECK stock_quantity >= 0`.

## 2:30–5:00 — Luồng MVC CRUD
- Mở `public/index.php`: Front Controller/router.
- Đăng nhập admin; vào quản trị sản phẩm.
- Chỉ đường request: Controller → `ProductRepository` → PDO → View. Tạo/sửa một sản phẩm bằng form POST + CSRF.
- Nói rõ View không có SQL, Repository không render HTML.

## 5:00–6:45 — Bảo mật
- Logout rồi vào URL admin: login guard chuyển trang login.
- Login customer rồi truy cập admin: 403 server-side.
- Mở source `Auth::verifyCsrf`; thử bỏ token để thấy 419.
- Giải thích `password_verify`, `session_regenerate_id`, ownership hủy đơn.

## 6:45–8:30 — API và Fetch
- Vào thực đơn và gõ “trà”.
- Mở DevTools Network: `api/products`, HTTP 200, response JSON.
- Chỉ ra `ApiProductController` chỉ trả data cần hiển thị; JS dùng debounce + `textContent`.

## 8:30–9:30 — Transaction nghiệp vụ
- Login customer, đặt số lượng vượt tồn: bị từ chối/rollback.
- Đặt số lượng hợp lệ: tạo đơn/item, trừ kho và ghi inventory log trong transaction.

## 9:30–10:00 — Tổng kết
- Mở TEST_CHECKLIST.md, nêu lỗi đã gặp: MySQL chưa chạy (PDO connection refused), cách sửa là bật service, import schema/seed và kiểm config.
- Hướng hoàn thiện: giỏ nhiều item, upload ảnh, pagination, gửi email.

## Câu hỏi có thể được hỏi
- **SQL ở đâu?** Repository; Controller không có query dài và View không query DB.
- **Tại sao không chỉ ẩn nút admin?** Người dùng vẫn có thể gõ URL; do đó server gọi `requireRole`.
- **CSRF ngăn gì?** Ngăn website khác ép browser đang login gửi POST thay đổi dữ liệu.
- **Vì sao dùng `FOR UPDATE`?** Khóa dòng product trong transaction để kiểm tồn/trừ kho không bị tranh chấp.
