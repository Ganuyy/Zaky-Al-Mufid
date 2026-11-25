<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

/**
 * Router sederhana berbasis pola URI dengan parameter dinamis ({id}).
 */
class Router
{
    /** @var array<int,array{method:string,uri:string,action:mixed,middleware:array<int|string,mixed>,meta:array<string,mixed>}> */
    private array $routes = [];

    public function get(string $uri, mixed $action, array $middleware = [], array $meta = []): void
    {
        $this->add('GET', $uri, $action, $middleware, $meta);
    }

    public function post(string $uri, mixed $action, array $middleware = [], array $meta = []): void
    {
        $this->add('POST', $uri, $action, $middleware, $meta);
    }

    /**
     * @param array<int|string,mixed> $middleware
     * @param array<string,mixed>     $meta
     */
    private function add(string $method, string $uri, mixed $action, array $middleware, array $meta): void
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => '/' . trim($uri, '/'),
            'action'     => $action,
            'middleware' => $middleware,
            'meta'       => $meta,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }

        $methodMismatch = false;

        foreach ($this->routes as $route) {
            $params = $this->matchUri($route['uri'], $uri);
            if ($params === null) {
                continue;
            }
            if ($route['method'] !== $method) {
                $methodMismatch = true;
                continue;
            }

            $this->runRoute($route, $params);

            return;
        }

        if ($methodMismatch) {
            $this->abort(405, 'Metode HTTP tidak diizinkan untuk alamat ini.');
        }

        $this->abort(404, 'Halaman tidak ditemukan.');
    }

    /**
     * @return array<string,string>|null
     */
    private function matchUri(string $pattern, string $uri): ?array
    {
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === $uri) {
            return [];
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches) === 1) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            return $params;
        }

        return null;
    }

    /**
     * @param array{method:string,uri:string,action:mixed,middleware:array<int|string,mixed>,meta:array<string,mixed>} $route
     * @param array<string,string> $params
     */
    private function runRoute(array $route, array $params): void
    {
        // Global CSRF untuk request yang mengubah data.
        $meta = $route['meta'];
        if ($route['method'] !== 'GET' && ($meta['csrf'] ?? true) === true) {
            (new CsrfMiddleware())->handle();
        }

        foreach ($route['middleware'] as $key => $value) {
            // Dukung format ['role:admin,kasir'] dan ['role' => 'admin,kasir']
            $spec = is_int($key) ? (string) $value : $key . ':' . $value;
            $this->runMiddleware($spec);
        }

        $action = $route['action'];

        if (is_callable($action) && !is_array($action)) {
            $action($params);

            return;
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $methodName] = $action;
            if (!class_exists($class)) {
                throw new \RuntimeException("Controller tidak ditemukan: {$class}");
            }
            $controller = new $class();
            if (!method_exists($controller, $methodName)) {
                throw new \RuntimeException("Method tidak ditemukan: {$class}::{$methodName}");
            }
            $controller->{$methodName}($params);

            return;
        }

        throw new \RuntimeException('Definisi route tidak valid.');
    }

    private function runMiddleware(string $spec): void
    {
        [$name, $argString] = array_pad(explode(':', $spec, 2), 2, '');
        $args = $argString === '' ? [] : array_map('trim', explode(',', $argString));

        $middleware = match ($name) {
            'auth'  => new AuthMiddleware(),
            'guest' => new GuestMiddleware(),
            'role'  => new RoleMiddleware($args),
            default => throw new \RuntimeException("Middleware tidak dikenal: {$name}"),
        };

        $middleware->handle();
    }

    public function abort(int $status, string $message): never
    {
        http_response_code($status);
        View::render('errors/error', ['status' => $status, 'message' => $message], 'layouts/blank');
        exit;
    }

    /**
     * URI relatif terhadap BASE_URL.
     */
    public static function currentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);

        if (BASE_URL !== '' && str_starts_with($uri, BASE_URL)) {
            $uri = substr($uri, strlen(BASE_URL));
        }

        return '/' . trim($uri, '/');
    }
}
