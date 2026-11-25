<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

/**
 * Role-based access control. Menerima daftar role yang diizinkan.
 */
class RoleMiddleware
{
    /** @var array<int,string> */
    private array $allowed;

    /**
     * @param array<int,string> $allowed
     */
    public function __construct(array $allowed)
    {
        $this->allowed = $allowed;
    }

    public function handle(): void
    {
        $user = Session::get('user');

        if ($user === null) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }

        if (!in_array($user['role'] ?? '', $this->allowed, true)) {
            Session::flash('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            http_response_code(403);
            redirect('dashboard');
        }
    }
}
