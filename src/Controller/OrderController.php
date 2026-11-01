<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Repository\OrderRepository;

final class OrderController
{
    public function __construct(private OrderRepository $orders) {}

    public function create(): void
    {
        Auth::requireRole(['customer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        Auth::verifyCsrf($_POST['csrf'] ?? null);
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        try {
            if (!$productId || !$quantity) throw new \RuntimeException('Dữ liệu đặt hàng không hợp lệ.');
            $code = $this->orders->placeOne((int) Auth::id(), $productId, $quantity);
            \flash('success', 'Đặt hàng thành công: ' . $code . '. Tồn kho đã được trừ trong transaction.');
        } catch (\Throwable $error) {
            \flash('error', $error->getMessage());
        }
        \redirect('products');
    }

    public function cancel(): void
    {
        Auth::requireRole(['customer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        Auth::verifyCsrf($_POST['csrf'] ?? null);
        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        if (!$orderId || !$this->orders->findByUser($orderId, (int) Auth::id())) {
            http_response_code(403);
            exit('Bạn không có quyền hủy đơn hàng này.');
        }
        if ($this->orders->cancelOwnedPendingOrder($orderId, (int) Auth::id())) {
            \flash('success', 'Đơn hàng đã được hủy.');
        } else {
            \flash('error', 'Chỉ được hủy đơn đang chờ xác nhận.');
        }
        
edirect('products');
    }
}
