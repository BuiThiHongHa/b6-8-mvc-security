<?php $title = 'Đăng nhập'; require dirname(__DIR__) . '/layout/header.php'; ?>
<section class="card" style="max-width:500px;margin:auto"><h1>Đăng nhập</h1><p class="muted">Session được tạo ở server; mật khẩu kiểm tra bằng <code>password_verify()</code>.</p>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= url('login') ?>"><input type="hidden" name="csrf" value="<?= e(\App\Core\Auth::csrfToken()) ?>">
 <p class="field"><label>Email</label><input name="email" type="email" required value="admin@example.com"></p>
 <p class="field"><label>Mật khẩu</label><input name="password" type="password" required value="1234567890"></p>
 <button>Đăng nhập</button>
</form><p class="muted">Tài khoản demo: admin@example.com, minh.nguyen@example.com, hoa.pham@example.com — cùng mật khẩu <code>1234567890</code>.</p></section>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
