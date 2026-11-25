<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE name = :name';
        $params = ['name' => $name];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasMenus(int $categoryId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM menus WHERE category_id = :id');
        $stmt->execute(['id' => $categoryId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array<int,array<string,mixed>> */
    public function allOrdered(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    }
}
