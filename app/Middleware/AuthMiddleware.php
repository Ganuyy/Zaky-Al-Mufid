<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

/**
 * Memastikan pengguna sudah login.
 */
class AuthMiddleware
{
    public function handle(): void
    {
        if (Session::get('user') === null) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }
}
