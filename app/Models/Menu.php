<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Menu extends Model
{
    protected string $table = 'menus';

    /** @return array<int,array<string,mixed>> */
    public function allWithCategory(string $search = '', int $categoryId = 0): array
    {
        $sql = 'SELECT m.*, c.name AS category_name
                FROM menus m
                JOIN categories c ON c.id = m.category_id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND m.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        if ($categoryId > 0) {
            $sql .= ' AND m.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        $sql .= ' ORDER BY c.name ASC, m.name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** Menu yang tersedia untuk Kasir/POS. */
    /** @return array<int,array<string,mixed>> */
    public function availableForSale(string $search = '', int $categoryId = 0): array
    {
        $sql = 'SELECT m.*, c.name AS category_name
                FROM menus m
                JOIN categories c ON c.id = m.category_id
                WHERE m.is_available = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND m.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        if ($categoryId > 0) {
            $sql .= ' AND m.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        $sql .= ' ORDER BY c.name ASC, m.name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function findWithCategory(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, c.name AS category_name
             FROM menus m JOIN categories c ON c.id = m.category_id
             WHERE m.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM menus')->fetchColumn();
    }

    public function countAvailable(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM menus WHERE is_available = 1')->fetchColumn();
    }
}
