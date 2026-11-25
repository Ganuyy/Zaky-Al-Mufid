<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Model dasar dengan operasi umum CRUD berbasis tabel.
 */
abstract class Model
{
    protected string $table = '';

    protected string $primaryKey = 'id';

    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function table(): string
    {
        return $this->table;
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int,array<string,mixed>> */
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}");

        return $stmt->fetchAll();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $fields  = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
        $holders = implode(', ', array_map(fn ($c) => ":{$c}", $columns));

        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` ({$fields}) VALUES ({$holders})");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }
        $sets = implode(', ', array_map(fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $data['__id'] = $id;

        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = :__id");

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");

        return $stmt->execute(['id' => $id]);
    }
}
