<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Singleton koneksi PDO (prepared statements, exception mode).
 */
class Database
{
    private static ?PDO $pdo = null;

    /** @var array<string,string> */
    private static array $config = [];

    /**
     * @param array<string,string> $config
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $c = self::$config;
        if ($c === []) {
            throw new PDOException('Konfigurasi database belum dimuat.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $c['host'] ?? '127.0.0.1',
            $c['port'] ?? '3306',
            $c['database'] ?? 'kopi_pintar',
            $c['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $c['username'] ?? 'root',
                $c['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException('Koneksi database gagal: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return self::$pdo;
    }
}
