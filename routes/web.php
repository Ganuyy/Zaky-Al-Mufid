<?php

declare(strict_types=1);

/**
 * Definisi route aplikasi SipOrder.
 *
 * Format: $router->get(uri, [Controller::class, 'method'], [middleware], [meta])
 * Middleware:
 *   'auth'          -> wajib login
 *   'guest'         -> hanya untuk yang belum login
 *   'role:admin'    -> role-based access control
 *
 * @var \App\Core\Router $router
 */

use App\Controllers\AuthController;
use App\Controllers\BaristaController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\ReceiptController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Core\Session;

$router->get('/', function () {
    redirect(Session::get('user') !== null ? 'dashboard' : 'login');
});

// ---------------------------------------------------------------- Autentikasi
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

// ---------------------------------------------------------------- Dashboard
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// --------------------------------------------------- Pengguna (khusus Admin)
$router->get('/users', [UserController::class, 'index'], ['role:admin']);
$router->get('/users/create', [UserController::class, 'create'], ['role:admin']);
$router->post('/users', [UserController::class, 'store'], ['role:admin']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['role:admin']);
$router->post('/users/{id}', [UserController::class, 'update'], ['role:admin']);
$router->post('/users/{id}/delete', [UserController::class, 'destroy'], ['role:admin']);
$router->post('/users/{id}/toggle', [UserController::class, 'toggle'], ['role:admin']);

// --------------------------------------------------- Menu & Kategori (Admin)
$router->get('/menus', [MenuController::class, 'index'], ['role:admin']);
$router->get('/menus/create', [MenuController::class, 'create'], ['role:admin']);
$router->post('/menus', [MenuController::class, 'store'], ['role:admin']);
$router->get('/menus/{id}/edit', [MenuController::class, 'edit'], ['role:admin']);
$router->post('/menus/{id}', [MenuController::class, 'update'], ['role:admin']);
$router->post('/menus/{id}/delete', [MenuController::class, 'destroy'], ['role:admin']);
$router->post('/menus/{id}/toggle', [MenuController::class, 'toggle'], ['role:admin']);

$router->get('/categories', [CategoryController::class, 'index'], ['role:admin']);
$router->post('/categories', [CategoryController::class, 'store'], ['role:admin']);
$router->post('/categories/{id}', [CategoryController::class, 'update'], ['role:admin']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy'], ['role:admin']);

// ------------------------------------------------ Kasir / POS (Admin & Kasir)
$router->get('/pos', [OrderController::class, 'pos'], ['role:admin,kasir']);
$router->get('/cart', [OrderController::class, 'cart'], ['role:admin,kasir']);
$router->post('/cart/add', [OrderController::class, 'addToCart'], ['role:admin,kasir']);
$router->post('/cart/update', [OrderController::class, 'updateCart'], ['role:admin,kasir']);
$router->post('/cart/remove', [OrderController::class, 'removeFromCart'], ['role:admin,kasir']);
$router->post('/cart/clear', [OrderController::class, 'clearCart'], ['role:admin,kasir']);

$router->get('/checkout', [PaymentController::class, 'checkout'], ['role:admin,kasir']);
$router->post('/payment/process', [PaymentController::class, 'process'], ['role:admin,kasir']);

$router->get('/receipt/{id}', [ReceiptController::class, 'show'], ['role:admin,kasir']);

// Riwayat transaksi (Admin melihat semua, Kasir melihat miliknya)
$router->get('/orders/history', [OrderController::class, 'history'], ['role:admin,kasir']);
$router->get('/orders/{id}', [OrderController::class, 'show'], ['role:admin,kasir']);

// ---------------------------------------------------- Barista (Admin & Barista)
$router->get('/barista/queue', [BaristaController::class, 'queue'], ['role:admin,barista']);
$router->get('/barista/order/{id}', [BaristaController::class, 'show'], ['role:admin,barista']);
$router->post('/orders/{id}/status', [BaristaController::class, 'updateStatus'], ['role:admin,barista']);

// -------------------------------------------------- Laporan (khusus Admin)
$router->get('/reports', [ReportController::class, 'index'], ['role:admin']);
$router->get('/reports/export', [ReportController::class, 'export'], ['role:admin']);
