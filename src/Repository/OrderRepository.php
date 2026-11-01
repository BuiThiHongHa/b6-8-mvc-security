<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class OrderRepository
{
    public function __construct(private PDO $pdo) {}

    public function placeOne(int $userId, int $productId, int $quantity): string
    {
        if ($quantity < 1) {
            throw new \RuntimeException('Số lượng đặt phải lớn hơn 0.');
        }
        $this->pdo->beginTransaction();
        try {
            $productStatement = $this->pdo->prepare('SELECT id, name, sale_price, stock_quantity FROM products WHERE id = :id AND is_published = 1 FOR UPDATE');
            $productStatement->execute(['id' => $productId]);
            $product = $productStatement->fetch();
            if (!$product) {
                throw new \RuntimeException('Sản phẩm không tồn tại hoặc đang tạm ngừng bán.');
            }
            if ((int) $product['stock_quantity'] < $quantity) {
                throw new \RuntimeException('Sản phẩm không đủ tồn kho.');
            }
            $userStatement = $this->pdo->prepare('SELECT full_name, phone, address, email FROM users WHERE id = :id');
            $userStatement->execute(['id' => $userId]);
            $user = $userStatement->fetch();
            $total = (float) $product['sale_price'] * $quantity;
            $code = 'ORD-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $orderStatement = $this->pdo->prepare("INSERT INTO orders (order_code, user_id, customer_name, customer_phone, customer_email, shipping_address, total_amount, order_status) VALUES (:code, :user_id, :name, :phone, :email, :address, :total, 'pending')");
            $orderStatement->execute(['code' => $code, 'user_id' => $userId, 'name' => $user['full_name'], 'phone' => $user['phone'] ?: 'Chưa cập nhật', 'email' => $user['email'], 'address' => $user['address'] ?: 'Chưa cập nhật', 'total' => $total]);
            $orderId = (int) $this->pdo->lastInsertId();
            $itemStatement = $this->pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal) VALUES (:order_id, :product_id, :name, :price, :quantity, :subtotal)');
            $itemStatement->execute(['order_id' => $orderId, 'product_id' => $productId, 'name' => $product['name'], 'price' => $product['sale_price'], 'quantity' => $quantity, 'subtotal' => $total]);
            $stockStatement = $this->pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id');
            $stockStatement->execute(['quantity' => $quantity, 'id' => $productId]);
            $logStatement = $this->pdo->prepare("INSERT INTO inventory_logs (product_id, order_id, change_type, quantity_change, stock_after, reason) VALUES (:product_id, :order_id, 'order_deduct', :change, :after_stock, :reason)");
            $logStatement->execute(['product_id' => $productId, 'order_id' => $orderId, 'change' => -$quantity, 'after_stock' => (int) $product['stock_quantity'] - $quantity, 'reason' => 'Đặt hàng qua luồng MVC bảo vệ']);
            $this->pdo->commit();
            return $code;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    public function findByUser(int $orderId, int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, order_code, order_status FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1');
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        return $statement->fetch() ?: null;
    }

    public function cancelOwnedPendingOrder(int $orderId, int $userId): bool
    {
        $statement = $this->pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = :id AND user_id = :user_id AND order_status = 'pending'");
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        return $statement->rowCount() === 1;
    }
}
