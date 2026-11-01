<?php $title = 'Tìm kiếm món ăn'; require dirname(__DIR__) . '/layout/header.php'; ?>
<section class="card"><h1>Thực đơn Bếp Nhà Hà</h1><p class="muted">Live search gọi JSON endpoint bằng Fetch; render bằng <code>textContent</code>, không dùng <code>innerHTML</code>.</p>
<label for="live-search">Tìm theo tên món hoặc SKU</label><input id="live-search" placeholder="Ví dụ: trà sữa, TS-THAI..." style="width:100%;margin:8px 0">
<p id="search-status" class="status" aria-live="polite">Sẵn sàng tìm kiếm.</p>
<?php if (\App\Core\Auth::role() === 'customer'): ?>
<form method="post" action="<?= url('orders/create') ?>" class="row" style="margin:14px 0;padding:12px;background:#fffbeb;border-radius:9px"><input type="hidden" name="csrf" value="<?= e(\App\Core\Auth::csrfToken()) ?>"><label>Sản phẩm ID <input type="number" name="product_id" min="1" required></label><label>Số lượng <input type="number" name="quantity" min="1" required></label><button>Đặt món (có kiểm tra tồn kho)</button></form>
<?php elseif (!\App\Core\Auth::check()): ?><p class="muted">Đăng nhập tài khoản khách hàng để thử luồng đặt món có role guard và CSRF.</p><?php endif; ?>
<div style="overflow:auto"><table><thead><tr><th>Mã</th><th>Tên món</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th></tr></thead><tbody id="product-results"></tbody></table></div></section>
<script>
const input=document.getElementById('live-search'), body=document.getElementById('product-results'), status=document.getElementById('search-status');
let timer, requestId=0;
const money = new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'});
function addCell(row, value){ const cell=document.createElement('td'); cell.textContent=value; row.appendChild(cell); }
function render(products){ body.replaceChildren(); if(!products.length){const row=document.createElement('tr'),cell=document.createElement('td');cell.colSpan=5;cell.textContent='Không tìm thấy sản phẩm phù hợp.';row.appendChild(cell);body.appendChild(row);return;} products.forEach(product=>{const row=document.createElement('tr');addCell(row,product.product_code);addCell(row,product.name);addCell(row,product.category_name);addCell(row,money.format(Number(product.sale_price)));addCell(row,String(product.stock_quantity));body.appendChild(row);}); }
async function search(keyword){const id=++requestId;status.textContent='Đang tải dữ liệu...'; try {const response=await fetch('<?= url('api/products') ?>&q='+encodeURIComponent(keyword),{headers:{Accept:'application/json'}});if(!response.ok)throw new Error('HTTP '+response.status);const json=await response.json();if(id!==requestId)return;if(!json.ok)throw new Error(json.message||'API phản hồi lỗi');render(json.data);status.textContent='Đã tìm thấy '+json.count+' sản phẩm.';}catch(error){if(id!==requestId)return;body.replaceChildren();status.textContent='Không thể tải dữ liệu: '+error.message;}}
input.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>search(input.value.trim()),300);});search('');
</script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
