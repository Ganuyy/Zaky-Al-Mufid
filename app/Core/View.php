<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Render view + layout.
 */
class View
{
    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View tidak ditemukan: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $layout) . '.php';
        if (!is_file($layoutFile)) {
            echo $content;

            return;
        }

        require $layoutFile;
    }
}
