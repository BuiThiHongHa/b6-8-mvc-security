<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Bếp Nhà Hà Store') ?></title>
  <style>
    :root { --brown:#713f12; --cream:#fffbeb; --green:#166534; --red:#b91c1c; --ink:#292524; }
    * { box-sizing:border-box; } body { margin:0; font:16px/1.5 system-ui,sans-serif; color:var(--ink); background:#fafaf9; }
    header { background:var(--brown); color:#fff; padding:14px 5%; display:flex; justify-content:space-between; align-items:center; gap:16px; }
    header a { color:#fff; text-decoration:none; margin-left:12px; } .brand { font-weight:800; font-size:1.2rem; }
    main { max-width:1100px; margin:28px auto; padding:0 18px; } .card { background:#fff; border:1px solid #e7e5e4; border-radius:12px; padding:22px; box-shadow:0 2px 8px #00000008; }
    table { width:100%; border-collapse:collapse; } th,td { border-bottom:1px solid #e7e5e4; padding:10px; text-align:left; } th { background:var(--cream); }
    input,select,textarea,button { font:inherit; padding:9px 10px; border-radius:7px; border:1px solid #a8a29e; } button,.button { border:0; background:var(--brown); color:#fff; text-decoration:none; cursor:pointer; display:inline-block; }
    .danger { background:var(--red); }.success { background:#dcfce7;color:var(--green);padding:10px;border-radius:8px; }.error { background:#fee2e2;color:var(--red);padding:10px;border-radius:8px; }
    .muted { color:#78716c; }.row { display:flex; gap:14px; flex-wrap:wrap; }.field { flex:1 1 220px; display:grid; gap:5px; }.actions { display:flex; gap:8px; align-items:center; }.status { margin:10px 0; min-height:1.5em; }
    @media(max-width:650px){ table{font-size:.86rem;} th,td{padding:7px;} }
  </style>
</head>
<body>
<header>
  <a class="brand" href="<?= url('products') ?>">🍲 Bếp Nhà Hà Store — MVC</a>
  <nav>
    <a href="<?= url('products') ?>">Sản phẩm</a>
    <?php if (\App\Core\Auth::role() === 'admin'): ?><a href="<?= url('admin/products') ?>">Quản trị</a><?php endif; ?>
    <?php if (\App\Core\Auth::check()): ?>
      <span>Xin chào, <?= e($_SESSION['full_name'] ?? '') ?></span>
      <form style="display:inline" method="post" action="<?= url('logout') ?>"><input type="hidden" name="csrf" value="<?= e(\App\Core\Auth::csrfToken()) ?>"><button>Đăng xuất</button></form>
    <?php else: ?><a href="<?= url('login') ?>">Đăng nhập</a><?php endif; ?>
  </nav>
</header>
<main>
<?php if ($flash = pullFlash()): ?><p class="<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p><?php endif; ?>
