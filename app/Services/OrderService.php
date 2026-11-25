<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Models\Menu;
use PDO;

/**
 * Mengelola keranjang pesanan yang disimpan di session.
 *
 * Harga TIDAK pernah dipercaya dari browser. Setiap kali ringkasan dihitung,
 * harga & ketersediaan diambil ulang dari database (source of truth).
 */
class OrderService
{
    private const CART_KEY = 'cart';

    private const MAX_QTY = 99;

    private const MAX_NOTES = 255;

    private Menu $menus;

    public function __construct(?Menu $menus = null)
    {
        $this->menus = $menus ?? new Menu();
    }

    /**
     * Setiap baris keranjang memiliki kunci unik (line key) agar produk yang sama
     * dengan customisasi berbeda dapat menjadi item terpisah.
     *
     * @return array<string,array{menu_id:int,quantity:int,notes:string}>
     */
    public function rawCart(): array
    {
        $cart = Session::get(self::CART_KEY, []);
        if (!is_array($cart)) {
            return [];
        }

        $normalized = [];
        foreach ($cart as $key => $info) {
            if (!is_array($info)) {
                continue;
            }
            $menuId = (int) ($info['menu_id'] ?? (is_numeric($key) ? $key : 0));
            if ($menuId < 1) {
                continue;
            }
            $normalized[(string) $key] = [
                'menu_id'  => $menuId,
                'quantity' => max(1, (int) ($info['quantity'] ?? 1)),
                'notes'    => (string) ($info['notes'] ?? ''),
            ];
        }

        return $normalized;
    }

    /** @param array<string,array{menu_id:int,quantity:int,notes:string}> $cart */
    private function persist(array $cart): void
    {
        Session::set(self::CART_KEY, $cart);
    }

    public function count(): int
    {
        return (int) array_sum(array_map(fn ($i) => (int) $i['quantity'], $this->rawCart()));
    }

    /**
     * @return array{ok:bool,message:string,count:int}
     */
    public function add(int $menuId, int $quantity, string $notes = ''): array
    {
        if ($quantity < 1) {
            return ['ok' => false, 'message' => 'Jumlah minimal 1.', 'count' => $this->count()];
        }

        $menu = $this->menus->find($menuId);
        if ($menu === null) {
            return ['ok' => false, 'message' => 'Menu tidak ditemukan.', 'count' => $this->count()];
        }
        if ((int) $menu['is_available'] !== 1) {
            return ['ok' => false, 'message' => $menu['name'] . ' sedang tidak tersedia.', 'count' => $this->count()];
        }

        $cart = $this->rawCart();
        $note = mb_substr(trim($notes), 0, self::MAX_NOTES);

        // Gabungkan hanya bila menu dan customisasi benar-benar sama.
        $matchedKey = null;
        foreach ($cart as $key => $info) {
            if ($info['menu_id'] === $menuId && trim($info['notes']) === $note) {
                $matchedKey = $key;
                break;
            }
        }

        if ($matchedKey !== null) {
            $cart[$matchedKey]['quantity'] = min(
                self::MAX_QTY,
                (int) $cart[$matchedKey]['quantity'] + $quantity
            );
        } else {
            $key = (string) $menuId;
            if (isset($cart[$key])) {
                $key = $menuId . '-' . bin2hex(random_bytes(4));
            }
            $cart[$key] = [
                'menu_id'  => $menuId,
                'quantity' => min(self::MAX_QTY, $quantity),
                'notes'    => $note,
            ];
        }
        $this->persist($cart);

        return ['ok' => true, 'message' => $menu['name'] . ' ditambahkan ke keranjang.', 'count' => $this->count()];
    }

    /**
     * @return array{ok:bool,message:string,count:int}
     */
    public function update(string $lineKey, int $quantity, ?string $notes = null): array
    {
        $cart = $this->rawCart();
        $key = $this->resolveKey($cart, $lineKey);
        if ($key === null) {
            return ['ok' => false, 'message' => 'Item tidak ada di keranjang.', 'count' => $this->count()];
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
            $this->persist($cart);

            return ['ok' => true, 'message' => 'Item dihapus dari keranjang.', 'count' => $this->count()];
        }

        $cart[$key]['quantity'] = min(self::MAX_QTY, $quantity);
        if ($notes !== null) {
            $cart[$key]['notes'] = mb_substr(trim($notes), 0, self::MAX_NOTES);
        }
        $this->persist($cart);

        return ['ok' => true, 'message' => 'Keranjang diperbarui.', 'count' => $this->count()];
    }

    /**
     * @return array{ok:bool,message:string,count:int}
     */
    public function remove(string $lineKey): array
    {
        $cart = $this->rawCart();
        $key = $this->resolveKey($cart, $lineKey);
        if ($key !== null) {
            unset($cart[$key]);
            $this->persist($cart);
        }

        return ['ok' => true, 'message' => 'Item dihapus.', 'count' => $this->count()];
    }

    /**
     * Cari kunci baris; mendukung data sesi lama yang masih memakai menu_id.
     *
     * @param array<string,array{menu_id:int,quantity:int,notes:string}> $cart
     */
    private function resolveKey(array $cart, string $lineKey): ?string
    {
        if (isset($cart[$lineKey])) {
            return $lineKey;
        }

        foreach ($cart as $key => $info) {
            if ((string) $info['menu_id'] === $lineKey) {
                return $key;
            }
        }

        return null;
    }

    public function clear(): void
    {
        Session::forget(self::CART_KEY);
    }

    /**
     * Ringkasan keranjang dengan harga terkini dari database.
     *
     * @return array{
     *   items:array<int,array<string,mixed>>,
     *   subtotal:float,
     *   total:float,
     *   count:int,
     *   problems:array<int,string>,
     *   valid:bool
     * }
     */
    public function summary(): array
    {
        $cart = $this->rawCart();
        if ($cart === []) {
            return [
                'items'    => [],
                'subtotal' => 0.0,
                'total'    => 0.0,
                'count'    => 0,
                'problems' => [],
                'valid'    => false,
            ];
        }

        $ids = array_values(array_unique(array_map(
            static fn (array $info): int => (int) $info['menu_id'],
            $cart
        )));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT m.*, c.name AS category_name
             FROM menus m JOIN categories c ON c.id = m.category_id
             WHERE m.id IN ({$placeholders})"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = $row;
        }

        $items = [];
        $subtotal = 0.0;
        $count = 0;
        $problems = [];

        foreach ($cart as $key => $info) {
            $menuId = (int) $info['menu_id'];
            $quantity = max(1, (int) ($info['quantity'] ?? 1));
            $notes = (string) ($info['notes'] ?? '');

            if (!isset($byId[$menuId])) {
                $problems[] = "Menu #{$menuId} sudah tidak ada dan akan diabaikan.";
                continue;
            }

            $menu = $byId[$menuId];
            $unitPrice = (float) $menu['price'];
            $lineSubtotal = $unitPrice * $quantity;

            if ((int) $menu['is_available'] !== 1) {
                $problems[] = $menu['name'] . ' sedang tidak tersedia.';
            }

            $items[] = [
                'key'          => (string) $key,
                'menu_id'      => $menuId,
                'menu_name'    => $menu['name'],
                'category'     => $menu['category_name'],
                'unit_price'   => $unitPrice,
                'quantity'     => $quantity,
                'subtotal'     => $lineSubtotal,
                'notes'        => $notes,
                'is_available' => (int) $menu['is_available'] === 1,
            ];

            $subtotal += $lineSubtotal;
            $count += $quantity;
        }

        return [
            'items'    => $items,
            'subtotal' => $subtotal,
            'total'    => $subtotal,
            'count'    => $count,
            'problems' => $problems,
            'valid'    => $items !== [] && $problems === [],
        ];
    }
}
