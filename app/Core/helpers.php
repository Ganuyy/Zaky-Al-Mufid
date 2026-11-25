<?php

declare(strict_types=1);

use App\Core\Session;

if (!function_exists('e')) {
    /** Escape output HTML untuk mencegah XSS. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return $path === '' ? BASE_URL : BASE_URL . '/' . $path;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('siporder_logo')) {
    /**
     * Komponen logo SipOrder yang dapat dipakai ulang di seluruh aplikasi.
     * Menghasilkan inline SVG (tajam di semua ukuran, mengikuti font aplikasi).
     *
     * @param array{
     *     type?: 'horizontal'|'icon',
     *     variant?: 'dark'|'light',
     *     class?: string,
     *     height?: int|float|string,
     *     title?: string
     * } $options
     */
    function siporder_logo(array $options = []): string
    {
        static $seq = 0;
        $seq++;

        $type    = ($options['type'] ?? 'horizontal') === 'icon' ? 'icon' : 'horizontal';
        $variant = ($options['variant'] ?? 'dark') === 'light' ? 'light' : 'dark';
        $class   = trim((string) ($options['class'] ?? ''));
        $title   = (string) ($options['title'] ?? 'SipOrder');
        $height  = $options['height'] ?? null;
        $light   = $variant === 'light';

        $uid    = 'soLogo' . $seq;
        $cupA   = $light ? '#E3AC74' : '#A65B25';
        $cupB   = $light ? '#A65B25' : '#71350F';
        $steam  = $light ? '#F8EDE0' : '#A65B25';
        $handle = $light ? '#F8EDE0' : '#71350F';
        $line   = $light ? '#F8EDE0' : '#4B250F';
        $label  = $light ? '#F8EDE0' : '#4B250F';

        $mark = '<defs><linearGradient id="' . $uid . '" x1="18" y1="24" x2="44" y2="55" gradientUnits="userSpaceOnUse">'
            . '<stop offset="0" stop-color="' . $cupA . '"/><stop offset="1" stop-color="' . $cupB . '"/></linearGradient></defs>'
            . '<g fill="none" stroke="' . $steam . '" stroke-width="2.6" stroke-linecap="round">'
            . '<path d="M25 18.5c-2.8-3.2 2.4-4.8-.4-8.4"/>'
            . '<path d="M33 16.5c-2.8-3.2 2.4-4.8-.4-8.4"/>'
            . '<path d="M41 18.5c-2.8-3.2 2.4-4.8-.4-8.4"/>'
            . '</g>'
            . '<path d="M45.5 29.5h3.2a7 7 0 0 1 0 14h-3.6" fill="none" stroke="' . $handle . '" stroke-width="3.4" stroke-linecap="round"/>'
            . '<path d="M12.5 25h33.5l-2.5 17.4A13 13 0 0 1 30.6 54h-.7A13 13 0 0 1 16.9 42.4Z" fill="url(#' . $uid . ')"/>'
            . '<ellipse cx="29.25" cy="25" rx="16.75" ry="2.9" fill="#4B250F"/>'
            . '<path d="M10.5 56h38" fill="none" stroke="' . $line . '" stroke-width="3" stroke-linecap="round"/>'
            . '<path d="M31.3 32.4c3.1-.7 5.9 1.9 4.8 4.9-1 2.6-4.4 3.3-6.5 1.7-2.1-1.6-1.5-5.7 1.7-6.6Z" fill="#F8EDE0" opacity=".9"/>'
            . '<path d="M32.6 33.6c-1.4 1.2-2.4 2.8-2.7 4.6" fill="none" stroke="#A65B25" stroke-width="1.2" stroke-linecap="round"/>';

        if ($type === 'icon') {
            $viewBox = '0 0 64 64';
            $body    = $mark;
        } else {
            $viewBox = '0 0 232 64';
            $body    = $mark
                . '<text x="72" y="43" font-family="Poppins, \'Segoe UI\', Helvetica, Arial, sans-serif"'
                . ' font-size="34" font-weight="700" letter-spacing="-0.8" fill="' . $label . '">SipOrder</text>';
        }

        $attrs = ' class="' . e(trim('kp-logo ' . $class)) . '"';
        if ($height !== null) {
            $h = is_numeric($height) ? ((float) $height) . 'px' : (string) $height;
            $attrs .= ' style="height:' . e($h) . ';width:auto"';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $viewBox . '" role="img" aria-label="' . e($title) . '"'
            . $attrs . '><title>' . e($title) . '</title>' . $body . '</svg>';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        $target = preg_match('#^https?://#i', $path) ? $path : base_url($path);
        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('rupiah')) {
    /** Format angka ke Rupiah tanpa desimal: 48000 -> "Rp 48.000". */
    function rupiah(float|int|string|null $value, bool $withPrefix = true): string
    {
        $n = (float) ($value ?? 0);
        $formatted = number_format($n, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth_user')) {
    /** @return array<string,mixed>|null */
    function auth_user(): ?array
    {
        $user = Session::get('user');

        return is_array($user) ? $user : null;
    }
}

if (!function_exists('has_role')) {
    function has_role(string ...$roles): bool
    {
        $user = auth_user();

        return $user !== null && in_array($user['role'] ?? '', $roles, true);
    }
}

if (!function_exists('app_config')) {
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        static $config = null;
        if ($config === null) {
            $config = require BASE_PATH . '/config/app.php';
        }
        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): string
    {
        static $old = null;
        if ($old === null) {
            $old = Session::getFlash('_old', []);
            if (!is_array($old)) {
                $old = [];
            }
        }

        return (string) ($old[$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value): void
    {
        Session::flash($key, $value);
    }
}

if (!function_exists('flash_success')) {
    function flash_success(string $message): void
    {
        Session::flash('success', $message);
    }
}

if (!function_exists('flash_error')) {
    function flash_error(string $message): void
    {
        Session::flash('error', $message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $key): mixed
    {
        return Session::getFlash($key);
    }
}

if (!function_exists('order_status_label')) {
    function order_status_label(string $status): string
    {
        $map = app_config('order_statuses', []);

        return $map[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (!function_exists('order_status_badge')) {
    /** Kelas Bootstrap badge untuk tiap status pesanan. */
    function order_status_badge(string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => 'badge-kp-secondary',
            'dibayar'             => 'badge-kp-info',
            'diproses'            => 'badge-kp-warning',
            'siap_diambil'        => 'badge-kp-success',
            'selesai'             => 'badge-kp-dark',
            'dibatalkan'          => 'badge-kp-danger',
            default               => 'badge-kp-secondary',
        };
    }
}

if (!function_exists('payment_status_label')) {
    function payment_status_label(string $status): string
    {
        $map = app_config('payment_statuses', []);

        return $map[$status] ?? strtoupper(str_replace('_', ' ', $status));
    }
}

if (!function_exists('payment_status_badge')) {
    function payment_status_badge(string $status): string
    {
        return match ($status) {
            'lunas'         => 'badge-kp-success',
            'belum_dibayar' => 'badge-kp-warning',
            'gagal'         => 'badge-kp-danger',
            'dikembalikan'  => 'badge-kp-secondary',
            default         => 'badge-kp-secondary',
        };
    }
}

if (!function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        if ($method === null || $method === '') {
            return '-';
        }
        $methods = app_config('payment_methods', []);

        return $methods[$method]['label'] ?? strtoupper($method);
    }
}

if (!function_exists('indonesian_date')) {
    function indonesian_date(?string $datetime, bool $withTime = true): string
    {
        if ($datetime === null || $datetime === '') {
            return '-';
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return '-';
        }
        $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $out = date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($withTime) {
            $out .= ' ' . date('H:i', $ts);
        }

        return $out;
    }
}

if (!function_exists('indonesian_month_year')) {
    function indonesian_month_year(?string $date = null): string
    {
        $ts = $date !== null ? strtotime($date) : time();
        if ($ts === false) {
            $ts = time();
        }
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

if (!function_exists('menu_customization_type')) {
    /**
     * Kelompokkan kategori menu menjadi tipe customisasi standar.
     * Kategori tak dikenal diperlakukan seperti non-kopi agar tetap fleksibel.
     */
    function menu_customization_type(?string $category): string
    {
        $name = mb_strtolower(trim((string) $category));

        if ($name === '') {
            return 'noncoffee';
        }
        if (str_contains($name, 'non') && (str_contains($name, 'kopi') || str_contains($name, 'coffee'))) {
            return 'noncoffee';
        }
        if (str_contains($name, 'kopi') || str_contains($name, 'coffee') || str_contains($name, 'espresso')) {
            return 'coffee';
        }
        if (str_contains($name, 'teh') || str_contains($name, 'tea')) {
            return 'tea';
        }
        if (str_contains($name, 'dessert') || str_contains($name, 'desert') || str_contains($name, 'kue')
            || str_contains($name, 'cake') || str_contains($name, 'snack') || str_contains($name, 'makanan')
            || str_contains($name, 'pastry')) {
            return 'dessert';
        }

        return 'noncoffee';
    }
}

if (!function_exists('menu_customization_groups')) {
    /**
     * Pilihan customisasi per tipe produk.
     * Setiap kelompok bersifat single-select dan opsional; "Lainnya" ditangani UI.
     *
     * @return array<int,array{label:string,options:array<int,string>}>
     */
    function menu_customization_groups(string $type): array
    {
        $sugar = [
            'label'   => 'Sugar Level',
            'options' => ['No Sugar', 'Less Sugar', 'Normal Sugar', 'Extra Sugar'],
        ];
        $temperature = [
            'label'   => 'Temperature',
            'options' => ['Hot', 'Ice'],
        ];
        $iceStandard = [
            'label'   => 'Ice Level',
            'options' => ['Normal Ice', 'Less Ice'],
        ];
        $iceCoffee = [
            'label'   => 'Ice Level',
            'options' => ['Normal Ice', 'Less Ice', 'Extra Ice', 'Less Ice Cube'],
        ];

        return match ($type) {
            'coffee'    => [$sugar, $temperature, $iceCoffee],
            'tea'       => [$sugar, $temperature, $iceStandard],
            'noncoffee' => [$sugar, $temperature, $iceStandard],
            default     => [],
        };
    }
}

if (!function_exists('menu_customization_options_json')) {
    /** JSON pilihan customisasi untuk seluruh tipe (dipakai UI kasir). */
    function menu_customization_options_json(): string
    {
        $options = [];
        foreach (['coffee', 'noncoffee', 'tea', 'dessert'] as $type) {
            $options[$type] = menu_customization_groups($type);
        }

        return (string) json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('duration_text')) {
    /** Selisih durasi dalam menit/jam untuk display. */
    function duration_text(?string $from, ?string $to = null): string
    {
        if ($from === null || $from === '') {
            return '-';
        }
        $start = strtotime($from);
        $end   = $to !== null && $to !== '' ? strtotime($to) : time();
        if ($start === false || $end === false || $end < $start) {
            return '-';
        }
        $diff = $end - $start;
        $minutes = intdiv($diff, 60);
        if ($minutes < 1) {
            return 'kurang dari 1 menit';
        }
        if ($minutes < 60) {
            return $minutes . ' menit';
        }
        $hours = intdiv($minutes, 60);
        $rem   = $minutes % 60;

        return $hours . ' jam ' . $rem . ' menit';
    }
}
