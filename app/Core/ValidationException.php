<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Dilempar saat validasi bisnis gagal. Pesannya aman untuk ditampilkan ke pengguna.
 */
class ValidationException extends \RuntimeException
{
}
