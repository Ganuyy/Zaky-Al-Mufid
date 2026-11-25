<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    /** @return array<string,mixed>|null */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
        $params = ['username' => $username];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array<int,array<string,mixed>> */
    public function allWithFilter(string $search = '', string $role = ''): array
    {
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (name LIKE :search OR username LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($role !== '') {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }
        $sql .= ' ORDER BY FIELD(role, "admin", "kasir", "barista"), name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function hasOrders(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE cashier_id = :id');
        $stmt->execute(['id' => $userId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countActiveAdmins(?int $exceptId = null): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1";
        $params = [];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
