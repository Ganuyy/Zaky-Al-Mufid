<?php
/**
 * SipOrder - Migration runner sederhana (idempotent).
 *
 * Menambahkan kolom yang dibutuhkan fitur baru tanpa menghapus data lama.
 * Cara pakai (CLI):
 *   php database/migrate.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    exit("Migration hanya boleh dijalankan dari command line.\n");
}

$base = dirname(__DIR__);
require $base . '/app/Core/Env.php';
App\Core\Env::load($base . '/.env');

$cfg = require $base . '/config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $cfg['host'],
    $cfg['port'],
    $cfg['database'],
    $cfg['charset']
);

try {
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Gagal koneksi ke MySQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

/** @return array<string,mixed>|null */
function columnInfo(PDO $pdo, string $database, string $table, string $column): ?array
{
    $stmt = $pdo->prepare(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND COLUMN_NAME = :col
         LIMIT 1'
    );
    $stmt->execute(['db' => $database, 'tbl' => $table, 'col' => $column]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

$database = (string) $cfg['database'];

$changed = 0;

if (columnInfo($pdo, $database, 'orders', 'customer_name') === null) {
    $pdo->exec('ALTER TABLE `orders` ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `cashier_id`');
    echo "OK  : kolom orders.customer_name ditambahkan." . PHP_EOL;
    $changed++;
} else {
    echo "SKIP: kolom orders.customer_name sudah ada." . PHP_EOL;
}

$notes = columnInfo($pdo, $database, 'order_items', 'notes');
$notesLength = $notes !== null ? (int) ($notes['CHARACTER_MAXIMUM_LENGTH'] ?? 0) : 0;
if ($notes !== null && $notesLength < 255) {
    $pdo->exec('ALTER TABLE `order_items` MODIFY COLUMN `notes` VARCHAR(255) NULL');
    echo "OK  : kolom order_items.notes diperluas ke VARCHAR(255)." . PHP_EOL;
    $changed++;
} elseif ($notes === null) {
    fwrite(STDERR, 'WARN: kolom order_items.notes tidak ditemukan. Jalankan schema.sql untuk instalasi baru.' . PHP_EOL);
} else {
    echo "SKIP: kolom order_items.notes sudah VARCHAR({$notesLength})." . PHP_EOL;
}

echo $changed > 0
    ? "Migrasi selesai. {$changed} perubahan diterapkan." . PHP_EOL
    : "Tidak ada perubahan. Database sudah terbaru." . PHP_EOL;
