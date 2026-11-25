<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

/**
 * Hanya untuk halaman login: jika sudah login, arahkan ke dashboard.
 */
class GuestMiddleware
{
    public function handle(): void
    {
        if (Session::get('user') !== null) {
            redirect('dashboard');
        }
    }
}
