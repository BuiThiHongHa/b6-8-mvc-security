<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Repository\UserRepository;

final class AuthController
{
    public function __construct(private UserRepository $users) {}

    public function login(): void
    {
        if (Auth::check()) {
            edirect('products');
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::verifyCsrf($_POST['csrf'] ?? null);
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $user = $this->users->findActiveByEmail($email);
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Email hoặc mật khẩu không đúng.';
            } else {
                Auth::login($user);
                \flash('success', 'Đăng nhập thành công. Xin chào ' . $user['full_name'] . '!');
                edirect('products');
            }
        }
        require dirname(__DIR__) . '/View/auth/login.php';
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        Auth::verifyCsrf($_POST['csrf'] ?? null);
        Auth::logout();
        session_start();
        \flash('success', 'Bạn đã đăng xuất an toàn.');
        edirect('login');
    }
}
