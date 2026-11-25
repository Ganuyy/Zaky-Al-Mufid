<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        $this->view('menus/categories', [
            'title'      => 'Kategori Menu',
            'categories' => (new Category())->allOrdered(),
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    public function store(): void
    {
        $name = (string) $this->input('name', '');
        try {
            if ($name === '' || mb_strlen($name) > 80) {
                throw new ValidationException('Nama kategori wajib diisi (maksimal 80 karakter).');
            }
            $categories = new Category();
            if ($categories->nameExists($name)) {
                throw new ValidationException('Kategori "' . $name . '" sudah ada.');
            }
            $categories->create(['name' => $name]);
            flash_success('Kategori "' . $name . '" berhasil ditambahkan.');
        } catch (ValidationException $e) {
            flash_error($e->getMessage());
        }
        $this->redirect('categories');
    }

    public function update(array $params): void
    {
        $id = (int) $params['id'];
        $name = (string) $this->input('name', '');
        try {
            $categories = new Category();
            if ($categories->find($id) === null) {
                throw new ValidationException('Kategori tidak ditemukan.');
            }
            if ($name === '' || mb_strlen($name) > 80) {
                throw new ValidationException('Nama kategori wajib diisi (maksimal 80 karakter).');
            }
            if ($categories->nameExists($name, $id)) {
                throw new ValidationException('Nama kategori sudah dipakai.');
            }
            $categories->update($id, ['name' => $name]);
            flash_success('Kategori berhasil diperbarui.');
        } catch (ValidationException $e) {
            flash_error($e->getMessage());
        }
        $this->redirect('categories');
    }

    public function destroy(array $params): void
    {
        $id = (int) $params['id'];
        $categories = new Category();
        $category = $categories->find($id);
        if ($category === null) {
            flash_error('Kategori tidak ditemukan.');
            $this->redirect('categories');
        }
        if ($categories->hasMenus($id)) {
            flash_error('Kategori "' . $category['name'] . '" masih memiliki menu dan tidak dapat dihapus.');
            $this->redirect('categories');
        }

        $categories->delete($id);
        flash_success('Kategori "' . $category['name'] . '" berhasil dihapus.');
        $this->redirect('categories');
    }
}
