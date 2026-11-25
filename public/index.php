<?php

declare(strict_types=1);

/**
 * SipOrder - Front Controller
 * Semua request masuk melalui file ini.
 */

use App\Core\Router;
use App\Core\ValidationException;
use App\Core\View;

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

// --- Autoload & environment -------------------------------------------------
require BASE_PATH . '/app/Core/Env.php';
App\Core\Env::load(BASE_PATH . '/.env');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// --- BASE_URL (agar aplikasi berjalan di subfolder apa pun) -----------------
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$baseUrl = rtrim($scriptDir, '/');
if ($baseUrl === '.' || $baseUrl === '\\') {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

require BASE_PATH . '/app/Core/helpers.php';

// --- Konfigurasi ------------------------------------------------------------
$config = require BASE_PATH . '/config/app.php';
date_default_timezone_set($config['timezone']);

App\Core\Database::configure(require BASE_PATH . '/config/database.php');
App\Core\Session::start();

// --- Security headers -------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");

// --- Dispatch ---------------------------------------------------------------
$router = new Router();
require BASE_PATH . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], Router::currentUri());
} catch (ValidationException $e) {
    App\Core\Session::flash('error', $e->getMessage());
    redirect('/');
} catch (\Throwable $e) {
    // Catat aktivitas penting / error ke file log.
    $logDir = BASE_PATH . '/storage/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir . '/app-' . date('Y-m-d') . '.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' @ '
            . $e->getFile() . ':' . $e->getLine() . PHP_EOL,
            FILE_APPEND
        );
    }

    $message = !empty($config['debug'])
        ? $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'
        : 'Terjadi kesalahan pada server. Silakan hubungi administrator.';

    http_response_code(500);
    View::render('errors/error', ['status' => 500, 'message' => $message], 'layouts/blank');
}
