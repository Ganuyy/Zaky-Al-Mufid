<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\Category;
use App\Models\Menu;

class MenuController extends Controller
{
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024; // 2 MB

    /** @var array<string,string> */
    private const ALLOWED_IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function index(): void
    {
        $menus      = new Menu();
        $categories = new Category();
        $search     = (string) $this->input('q', '');
        $categoryId = $this->intInput('category_id', 0);

        $this->view('menus/index', [
            'title'      => 'Kelola Menu',
            'menus'      => $menus->allWithCategory($search, $categoryId),
            'categories' => $categories->allOrdered(),
            'search'     => $search,
            'categoryId' => $categoryId,
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
            'csrf'       => csrf_token(),
        ]);
    }

    public function create(): void
    {
        $this->view('menus/form', [
            'title'      => 'Tambah Menu',
            'menu'       => null,
            'categories' => (new Category())->allOrdered(),
            'action'     => base_url('menus'),
            'error'      => get_flash('error'),
        ]);
    }

    public function store(): void
    {
        try {
            $data = $this->validateInput(null);
            (new Menu())->create($data);
            flash_success('Menu "' . $data['name'] . '" berhasil ditambahkan.');
            $this->redirect('menus');
        } catch (ValidationException $e) {
            $this->rememberOld();
            flash_error($e->getMessage());
            $this->redirect('menus/create');
        }
    }

    public function edit(array $params): void
    {
        $menu = (new Menu())->find((int) $params['id']);
        if ($menu === null) {
            flash_error('Menu tidak ditemukan.');
            $this->redirect('menus');
        }

        $this->view('menus/form', [
            'title'      => 'Ubah Menu',
            'menu'       => $menu,
            'categories' => (new Category())->allOrdered(),
            'action'     => base_url('menus/' . $menu['id']),
            'error'      => get_flash('error'),
        ]);
    }

    public function update(array $params): void
    {
        $id = (int) $params['id'];
        $menus = new Menu();
        $menu = $menus->find($id);
        if ($menu === null) {
            flash_error('Menu tidak ditemukan.');
            $this->redirect('menus');
        }

        try {
            $data = $this->validateInput($menu);
            $menus->update($id, $data);
            flash_success('Menu "' . $data['name'] . '" berhasil diperbarui.');
            $this->redirect('menus');
        } catch (ValidationException $e) {
            $this->rememberOld();
            flash_error($e->getMessage());
            $this->redirect('menus/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        $id = (int) $params['id'];
        $menus = new Menu();
        $menu = $menus->find($id);
        if ($menu === null) {
            flash_error('Menu tidak ditemukan.');
            $this->redirect('menus');
        }

        // Bila menu sudah dipakai transaksi, hanya dinonaktifkan agar histori utuh.
        $stmt = \App\Core\Database::connection()
            ->prepare('SELECT COUNT(*) FROM order_items WHERE menu_id = :id');
        $stmt->execute(['id' => $id]);
        $used = (int) $stmt->fetchColumn();

        if ($used > 0) {
            $menus->update($id, ['is_available' => 0]);
            flash_success('Menu "' . $menu['name'] . '" dinonaktifkan karena sudah dipakai pada transaksi.');
        } else {
            $menus->delete($id);
            flash_success('Menu "' . $menu['name'] . '" berhasil dihapus.');
        }
        $this->redirect('menus');
    }

    public function toggle(array $params): void
    {
        $id = (int) $params['id'];
        $menus = new Menu();
        $menu = $menus->find($id);
        if ($menu === null) {
            flash_error('Menu tidak ditemukan.');
            $this->redirect('menus');
        }

        $newValue = (int) $menu['is_available'] === 1 ? 0 : 1;
        $menus->update($id, ['is_available' => $newValue]);
        flash_success('Menu "' . $menu['name'] . '" kini ' . ($newValue ? 'tersedia' : 'tidak tersedia') . '.');
        $this->redirect('menus');
    }

    /**
     * @param array<string,mixed>|null $existing
     * @return array<string,mixed>
     */
    private function validateInput(?array $existing): array
    {
        $name        = (string) $this->input('name', '');
        $description = (string) $this->input('description', '');
        $priceRaw    = (string) $this->input('price', '');
        $categoryId  = $this->intInput('category_id', 0);
        $isAvailable = (int) $this->input('is_available', $existing['is_available'] ?? 1) ? 1 : 0;

        $errors = [];
        if ($name === '' || mb_strlen($name) > 120) {
            $errors[] = 'Nama menu wajib diisi (maksimal 120 karakter).';
        }
        if (mb_strlen($description) > 255) {
            $errors[] = 'Deskripsi maksimal 255 karakter.';
        }

        // Terima "18.000", "18000", "18000.50"
        $normalized = preg_replace('/[^0-9.,]/', '', $priceRaw) ?? '';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);
        if ($normalized === '' || !is_numeric($normalized)) {
            $errors[] = 'Harga wajib berupa angka.';
            $price = 0.0;
        } else {
            $price = round((float) $normalized, 2);
            if ($price < 0) {
                $errors[] = 'Harga tidak boleh negatif.';
            }
            if ($price > 99999999.99) {
                $errors[] = 'Harga terlalu besar.';
            }
        }

        if ((new Category())->find($categoryId) === null) {
            $errors[] = 'Kategori tidak valid.';
        }

        if ($errors !== []) {
            throw new ValidationException(implode(' ', $errors));
        }

        $image = $existing['image'] ?? null;
        $uploaded = $this->handleImageUpload();
        if ($uploaded !== null) {
            $image = $uploaded;
        }

        return [
            'category_id'  => $categoryId,
            'name'         => $name,
            'description'  => $description !== '' ? $description : null,
            'price'        => $price,
            'image'        => $image,
            'is_available' => $isAvailable,
        ];
    }

    private function handleImageUpload(): ?string
    {
        if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['image'];
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException('Gagal mengunggah gambar (kode ' . (int) $file['error'] . ').');
        }
        if ((int) $file['size'] > self::MAX_IMAGE_BYTES) {
            throw new ValidationException('Ukuran gambar maksimal 2 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGE_MIME[$mime])) {
            throw new ValidationException('Format gambar harus JPG, PNG, atau WEBP.');
        }

        $ext  = self::ALLOWED_IMAGE_MIME[$mime];
        $dir  = BASE_PATH . '/public/assets/images/menus';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new ValidationException('Folder gambar tidak dapat dibuat.');
        }

        $filename = 'menu_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            throw new ValidationException('Gagal menyimpan gambar.');
        }

        return 'menus/' . $filename;
    }
}
