<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function searchActive(string $keyword = '', int $limit = 10): array
    {
        $limit = max(1, min(10, $limit));
        $sql = 'SELECT p.id, p.product_code, p.name, p.sale_price, p.stock_quantity, c.name AS category_name
                FROM products p JOIN categories c ON c.id = p.category_id
                WHERE p.is_published = 1 AND c.is_active = 1';
        $params = [];
        if ($keyword !== '') {
            $sql .= ' AND (p.name LIKE :keyword OR p.product_code LIKE :keyword)';
            $params['keyword'] = '%' . $keyword . '%';
        }
        $sql .= ' ORDER BY p.id DESC LIMIT :limit';
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $name => $value) {
            $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function findAllForAdmin(): array
    {
        return $this->pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function categories(): array
    {
        return $this->pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
    }

    public function create(array $data): void
    {
        $statement = $this->pdo->prepare('INSERT INTO products (category_id, product_code, name, slug, description, original_price, sale_price, stock_quantity, is_published)
            VALUES (:category_id, :product_code, :name, :slug, :description, :original_price, :sale_price, :stock_quantity, :is_published)');
        $statement->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->pdo->prepare('UPDATE products SET category_id=:category_id, product_code=:product_code, name=:name, slug=:slug, description=:description,
            original_price=:original_price, sale_price=:sale_price, stock_quantity=:stock_quantity, is_published=:is_published WHERE id=:id');
        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
