<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Throwable;

final class ApiProductController
{
    public function __construct(private ProductRepository $products) {}

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $keyword = trim((string) ($_GET['q'] ?? ''));
            $data = $this->products->searchActive($keyword, 10);
            echo json_encode(['ok' => true, 'data' => $data, 'count' => count($data)], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Không thể tải dữ liệu sản phẩm.'], JSON_UNESCAPED_UNICODE);
        }
    }
}
