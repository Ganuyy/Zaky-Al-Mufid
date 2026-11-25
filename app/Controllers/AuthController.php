<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 30;

    public function showLogin(): void
    {
        $this->view('auth/login', [
            'error'   => get_flash('error'),
            'success' => get_flash('success'),
        ], 'layouts/blank');
    }

    public function login(): void
    {
        $username = (string) $this->input('username', '');
        $password = (string) ($_POST['password'] ?? '');

        // Throttle percobaan login.
        $lockedUntil = (int) Session::get('login_locked_until', 0);
        if ($lockedUntil > time()) {
            $wait = $lockedUntil - time();
            $this->rememberOld();
            flash_error("Terlalu banyak percobaan. Coba lagi dalam {$wait} detik.");
            $this->redirect('login');
        }

        try {
            if ($username === '' || $password === '') {
                throw new ValidationException('Username dan password wajib diisi.');
            }
            if (mb_strlen($username) > 50 || mb_strlen($password) > 100) {
                throw new ValidationException('Panjang username atau password tidak wajar.');
            }

            $users = new User();
            $user = $users->findByUsername($username);

            // Pesan generik agar tidak membocorkan username mana yang ada.
            if ($user === null || !password_verify($password, (string) $user['password'])) {
                $this->registerFailure();
                throw new ValidationException('Username atau password salah.');
            }

            if ((int) $user['is_active'] !== 1) {
                throw new ValidationException('Akun Anda dinonaktifkan. Hubungi administrator.');
            }

            // Login berhasil.
            Session::forget('login_attempts');
            Session::forget('login_locked_until');
            Session::regenerate();

            Session::set('user', [
                'id'       => (int) $user['id'],
                'name'     => $user['name'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ]);

            flash_success('Selamat datang, ' . $user['name'] . '!');
            $this->redirect('dashboard');
        } catch (ValidationException $e) {
            $this->rememberOld();
            flash_error($e->getMessage());
            $this->redirect('login');
        }
    }

    public function logout(): void
    {
        Session::destroy();
        Session::start();
        flash_success('Anda telah keluar. Terima kasih!');
        $this->redirect('login');
    }

    private function registerFailure(): void
    {
        $attempts = (int) Session::get('login_attempts', 0) + 1;
        Session::set('login_attempts', $attempts);
        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::set('login_locked_until', time() + self::LOCK_SECONDS);
            Session::set('login_attempts', 0);
        }
    }
}
