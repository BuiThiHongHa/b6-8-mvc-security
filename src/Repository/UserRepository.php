<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT u.id, u.email, u.password_hash, u.full_name, r.role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :email AND u.is_active = 1 LIMIT 1');
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }
}
