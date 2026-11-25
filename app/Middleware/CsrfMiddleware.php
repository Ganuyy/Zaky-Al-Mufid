<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

/**
 * Proteksi CSRF untuk request yang mengubah data (POST).
 */
class CsrfMiddleware
{
    public function handle(): void
    {
        $sessionToken = Session::get('_csrf_token');
        $sentToken    = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!is_string($sessionToken) || $sessionToken === '' || !is_string($sentToken)
            || !hash_equals($sessionToken, $sentToken)) {
            http_response_code(419);
            Session::flash('error', 'Sesi form telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $target  = $referer !== '' ? $referer : base_url('login');
            header('Location: ' . $target);
            exit;
        }
    }
}
