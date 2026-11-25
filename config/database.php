<?php
/**
 * Konfigurasi koneksi database.
 * Nilai diambil dari environment (.env). Jangan menulis kredensial di kode.
 */

declare(strict_types=1);

use App\Core\Env;

return [
    'host'     => (string) Env::get('DB_HOST', '127.0.0.1'),
    'port'     => (string) Env::get('DB_PORT', '3306'),
    'database' => (string) Env::get('DB_DATABASE', 'kopi_pintar'),
    'username' => (string) Env::get('DB_USERNAME', 'root'),
    'password' => (string) Env::get('DB_PASSWORD', ''),
    'charset'  => 'utf8mb4',
];
