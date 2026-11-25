<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Kelas dasar controller.
 */
abstract class Controller
{
    /**
     * @param array<string,mixed> $data
     */
    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function back(string $fallback = '/'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $target  = $referer !== '' ? $referer : base_url($fallback);
        header('Location: ' . $target);
        exit;
    }

    /** @return array<string,mixed>|null */
    protected function user(): ?array
    {
        return auth_user();
    }

    protected function requireUser(): array
    {
        $user = auth_user();
        if ($user === null) {
            redirect('login');
        }

        return $user;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function intInput(string $key, int $default = 0): int
    {
        $value = $this->input($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    protected function floatInput(string $key, float $default = 0.0): float
    {
        $value = $this->input($key, $default);
        if (is_string($value)) {
            // Bersihkan format "50.000" atau "50,000" menjadi 50000
            $value = preg_replace('/[^0-9.,-]/', '', $value) ?? '';
            $value = str_replace(['.', ','], '', $value);
        }
        return is_numeric($value) ? (float) $value : $default;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Simpan input lama saat validasi gagal (kecuali field sensitif).
     * @param array<int,string> $except
     */
    protected function rememberOld(array $except = []): void
    {
        $except = array_merge($except, ['password', 'password_confirmation', '_csrf_token']);
        $old = [];
        foreach ($_POST as $key => $value) {
            if (in_array($key, $except, true)) {
                continue;
            }
            $old[$key] = is_string($value) ? $value : '';
        }
        Session::flash('_old', $old);
    }
}
