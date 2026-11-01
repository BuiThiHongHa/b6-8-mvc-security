<?php
declare(strict_types=1);

session_start();
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/Core/helpers.php';

use App\Controller\ApiProductController;
use App\Controller\AuthController;
use App\Controller\OrderController;
use App\Controller\ProductController;
use App\Core\Database;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;

try {
    $pdo = Database::connection();
} catch (Throwable $error) {
    http_response_code(503);
    if (($_GET['route'] ?? '') === 'api/products') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $title = 'Cần kết nối cơ sở dữ liệu';
    require dirname(__DIR__) . '/src/View/layout/header.php';
    echo '<section class="card"><h1>Chưa thể kết nối cơ sở dữ liệu</h1><p>Hãy bật MySQL, import <code>database/schema.sql</code> và <code>database/seed.sql</code>, sau đó kiểm tra thông tin DB trong biến môi trường hoặc <code>config/config.php</code>.</p></section>';
    require dirname(__DIR__) . '/src/View/layout/footer.php';
    exit;
}
$productController = new ProductController(new ProductRepository($pdo));
$authController = new AuthController(new UserRepository($pdo));
$orderController = new OrderController(new OrderRepository($pdo));
$apiProductController = new ApiProductController(new ProductRepository($pdo));
$route = $_GET['route'] ?? 'products';

switch ($route) {
    case 'products': $productController->index(); break;
    case 'login': $authController->login(); break;
    case 'logout': $authController->logout(); break;
    case 'admin/products': $productController->adminIndex(); break;
    case 'admin/products/create': $productController->form(); break;
    case 'admin/products/edit': $productController->form(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0); break;
    case 'admin/products/store': $productController->store(); break;
    case 'admin/products/update': $productController->update(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0); break;
    case 'admin/products/delete': $productController->delete(); break;
    case 'orders/create': $orderController->create(); break;
    case 'orders/cancel': $orderController->cancel(); break;
    case 'api/products': $apiProductController->search(); break;
    default: http_response_code(404); echo '404 - Route not found';
}
