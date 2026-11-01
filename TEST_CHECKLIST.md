# Checklist kiểm thử — Bài 6–8 MVC, bảo mật, Fetch API

> Thực hiện sau khi import `database/schema.sql`, `database/seed.sql`, bật MySQL và chạy project. “PASS (code review)” có nghĩa đã kiểm tra logic/cú pháp trong source; “PASS (manual)” là thao tác cần thực hiện trực tiếp trên browser khi database đã chạy.

| ID | Nhóm | Tình huống / bước kiểm tra | Kết quả mong đợi | Kết quả |
|---|---|---|---|---|
| TC01 | MVC | Mở `?route=products` | Request qua `public/index.php` → ProductController → Repository → View; trang hiển thị danh sách. | PASS (code review) |
| TC02 | MVC | Mở quản trị bằng admin | View không có SQL; dữ liệu đi từ `ProductRepository::findAllForAdmin()`. | PASS (code review) |
| TC03 | Auth | Sai email/mật khẩu ở login | Không tạo session; báo “Email hoặc mật khẩu không đúng”. | PASS (code review) |
| TC04 | Auth | Đăng nhập `admin@example.com` / `1234567890` | `password_verify()` đúng, regenerate session ID, session lưu `user_id`, `role=admin`. | PASS (code review) |
| TC05 | Role | Chưa login mở `?route=admin/products` | Redirect về login. | PASS (code review) |
| TC06 | Role | Customer hoặc staff mở `?route=admin/products` | HTTP 403, không cho vào dù biết URL. | PASS (code review) |
| TC07 | CSRF | POST delete/update/create không kèm hoặc sai `csrf` | HTTP 419; không thay đổi CSDL. | PASS (code review) |
| TC08 | Ownership | Customer Hoa POST hủy order ID 1 của Minh | 403; query đối chiếu `id` và `user_id` ở server. | PASS (code review) |
| TC09 | Business | Customer đặt product với quantity lớn hơn `stock_quantity` | Transaction rollback; thông báo không đủ tồn kho; không tạo đơn dở dang. | PASS (code review) |
| TC10 | Business | Customer đặt product còn đủ hàng | Tạo order, item, trừ stock, insert inventory log rồi commit. | PASS (code review) |
| TC11 | API | Mở `?route=api/products&q=tra` | HTTP 200 và JSON `{ok,data,count}`, dữ liệu đúng DB, tối đa 10 record. | PASS (code review) |
| TC12 | API Security | Kiểm JSON API | Không có `password_hash`, `role_id`, email khách hay thông tin nhạy cảm. | PASS (code review) |
| TC13 | Fetch | Gõ “trà” vào live search | Sau debounce 300ms có request Fetch; danh sách cập nhật không reload trang. | PASS (code review) |
| TC14 | Fetch/XSS | Tên product chứa `<script>alert(1)</script>` | Hiển thị dạng chữ do `textContent`; không chạy JavaScript. | PASS (code review) |
| TC15 | UI | Rút kết nối DB rồi search | Response 500 JSON; UI hiển thị lỗi, không vỡ giao diện. | PASS (code review) |

## Kiểm tra thủ công cần chụp minh chứng

1. DevTools > Network khi gõ từ khóa, chụp request `api/products`, status 200 và JSON response.
2. Đăng nhập `minh.nguyen@example.com`, chụp trang/tình huống customer vào `admin/products` cho 403.
3. Xóa trường hidden CSRF trong DevTools rồi submit thao tác thay đổi dữ liệu, chụp trang 419.
4. Customer Hoa gửi `order_id=1` tới `orders/cancel`, chụp 403.
5. Chụp transaction đặt một món còn hàng và kiểm tra tồn kho/log sau đó trên phpMyAdmin.
