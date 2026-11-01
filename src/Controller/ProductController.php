<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Repository\ProductRepository;
use PDOException;

final class ProductController
{
    public function __construct(private ProductRepository $products) {}

    public function index(): void
    {
        $products = $this->products->searchActive('', 10);
        require dirname(__DIR__) . '/View/products/index.php';
    }

    public function adminIndex(): void
    {
        Auth::requireRole(['admin']);
        $products = $this->products->findAllForAdmin();
        require dirname(__DIR__) . '/View/admin/products/index.php';
    }

    public function form(?int $id = null): void
    {
        Auth::requireRole(['admin']);
        $product = $id ? $this->products->find($id) : null;
        if ($id && !$product) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }
        $categories = $this->products->categories();
        $errors = [];
        require dirname(__DIR__) . '/View/admin/products/form.php';
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $this->requirePostCsrf();
        [$data, $errors] = $this->validatedData();
        if ($errors) {
            $product = $data;
            $categories = $this->products->categories();
            require dirname(__DIR__) . '/View/admin/products/form.php';
            return;
        }
        try {
            $this->products->create($data);
            \flash('success', 'Đã tạo sản phẩm bằng luồng MVC.');
            edirect('admin/products');
        } catch (PDOException $e) {
            $product = $data;
            $categories = $this->products->categories();
            $errors = ['Mã SKU hoặc slug đã tồn tại.'];
            require dirname(__DIR__) . '/View/admin/products/form.php';
        }
    }

    public function update(int $id): void
    {
        Auth::requireRole(['admin']);
        $this->requirePostCsrf();
        if (!$this->products->find($id)) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }
        [$data, $errors] = $this->validatedData();
        if ($errors) {
            $product = $data + ['id' => $id];
            $categories = $this->products->categories();
            require dirname(__DIR__) . '/View/admin/products/form.php';
            return;
        }
        try {
            $this->products->update($id, $data);
            \flash('success', 'Đã cập nhật sản phẩm.');
            edirect('admin/products');
        } catch (PDOException $e) {
            $product = $data + ['id' => $id];
            $categories = $this->products->categories();
            $errors = ['Mã SKU hoặc slug đã tồn tại.'];
            require dirname(__DIR__) . '/View/admin/products/form.php';
        }
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $this->requirePostCsrf();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            \flash('error', 'ID sản phẩm không hợp lệ.');
        } else {
            try {
                $this->products->delete($id);
                \flash('success', 'Đã xóa sản phẩm.');
            } catch (PDOException $e) {
                \flash('error', 'Không thể xóa món đã có dữ liệu liên quan.');
            }
        }
        edirect('admin/products');
    }

    private function requirePostCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        Auth::verifyCsrf($_POST['csrf'] ?? null);
    }

    private function validatedData(): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $code = strtoupper(trim((string) ($_POST['product_code'] ?? '')));
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $originalPrice = filter_input(INPUT_POST, 'original_price', FILTER_VALIDATE_FLOAT);
        $salePrice = filter_input(INPUT_POST, 'sale_price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
        $errors = [];
        if ($name === '') $errors[] = 'Tên sản phẩm không được để trống.';
        if ($code === '') $errors[] = 'Mã SKU không được để trống.';
        if (!$categoryId) $errors[] = 'Danh mục không hợp lệ.';
        if ($originalPrice === false || $originalPrice < 0 || $salePrice === false || $salePrice < 0) $errors[] = 'Giá phải là số không âm.';
        if ($stock === false || $stock < 0) $errors[] = 'Tồn kho phải là số nguyên không âm.';
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name . '-' . $code)), '-'));
        return [[
            'category_id' => $categoryId ?: 0, 'product_code' => $code, 'name' => $name, 'slug' => $slug,
            'description' => trim((string) ($_POST['description'] ?? '')), 'original_price' => $originalPrice ?: 0,
            'sale_price' => $salePrice ?: 0, 'stock_quantity' => $stock ?: 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ], $errors];
    }
}
