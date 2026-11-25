<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ValidationException;
use App\Models\User;

class UserController extends Controller
{
    private const ROLES = ['admin', 'kasir', 'barista'];

    public function index(): void
    {
        $users  = new User();
        $search = (string) $this->input('q', '');
        $role   = (string) $this->input('role', '');
        if ($role !== '' && !in_array($role, self::ROLES, true)) {
            $role = '';
        }

        $this->view('users/index', [
            'title'   => 'Kelola Pengguna',
            'users'   => $users->allWithFilter($search, $role),
            'search'  => $search,
            'role'    => $role,
            'roles'   => self::ROLES,
            'success' => get_flash('success'),
            'error'   => get_flash('error'),
        ]);
    }

    public function create(): void
    {
        $this->view('users/form', [
            'title'  => 'Tambah Pengguna',
            'user'   => null,
            'roles'  => self::ROLES,
            'action' => base_url('users'),
            'error'  => get_flash('error'),
        ]);
    }

    public function store(): void
    {
        try {
            $data = $this->validateInput(null);
            (new User())->create($data);
            flash_success('Pengguna "' . $data['name'] . '" berhasil ditambahkan.');
            $this->redirect('users');
        } catch (ValidationException $e) {
            $this->rememberOld();
            flash_error($e->getMessage());
            $this->redirect('users/create');
        }
    }

    public function edit(array $params): void
    {
        $user = (new User())->find((int) $params['id']);
        if ($user === null) {
            flash_error('Pengguna tidak ditemukan.');
            $this->redirect('users');
        }

        $this->view('users/form', [
            'title'  => 'Ubah Pengguna',
            'user'   => $user,
            'roles'  => self::ROLES,
            'action' => base_url('users/' . $user['id']),
            'error'  => get_flash('error'),
        ]);
    }

    public function update(array $params): void
    {
        $id    = (int) $params['id'];
        $users = new User();
        $user  = $users->find($id);
        if ($user === null) {
            flash_error('Pengguna tidak ditemukan.');
            $this->redirect('users');
        }

        try {
            $data = $this->validateInput($user);
            $users->update($id, $data);
            flash_success('Pengguna "' . $data['name'] . '" berhasil diperbarui.');
            $this->redirect('users');
        } catch (ValidationException $e) {
            $this->rememberOld();
            flash_error($e->getMessage());
            $this->redirect('users/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        $id     = (int) $params['id'];
        $me     = $this->user();
        $users  = new User();
        $target = $users->find($id);

        if ($target === null) {
            flash_error('Pengguna tidak ditemukan.');
            $this->redirect('users');
        }
        if ((int) $me['id'] === $id) {
            flash_error('Anda tidak dapat menghapus akun Anda sendiri.');
            $this->redirect('users');
        }
        if ($target['role'] === 'admin' && $users->countActiveAdmins($id) === 0) {
            flash_error('Tidak dapat menghapus admin aktif terakhir.');
            $this->redirect('users');
        }

        if ($users->hasOrders($id)) {
            $users->update($id, ['is_active' => 0]);
            flash_success('Pengguna "' . $target['name'] . '" dinonaktifkan karena memiliki riwayat transaksi.');
        } else {
            $users->delete($id);
            flash_success('Pengguna "' . $target['name'] . '" berhasil dihapus.');
        }
        $this->redirect('users');
    }

    public function toggle(array $params): void
    {
        $id     = (int) $params['id'];
        $me     = $this->user();
        $users  = new User();
        $target = $users->find($id);

        if ($target === null) {
            flash_error('Pengguna tidak ditemukan.');
            $this->redirect('users');
        }
        if ((int) $me['id'] === $id) {
            flash_error('Anda tidak dapat menonaktifkan akun Anda sendiri.');
            $this->redirect('users');
        }

        $newValue = (int) $target['is_active'] === 1 ? 0 : 1;
        if ($newValue === 0 && $target['role'] === 'admin' && $users->countActiveAdmins($id) === 0) {
            flash_error('Tidak dapat menonaktifkan admin aktif terakhir.');
            $this->redirect('users');
        }

        $users->update($id, ['is_active' => $newValue]);
        flash_success('Pengguna "' . $target['name'] . '" kini ' . ($newValue ? 'aktif' : 'nonaktif') . '.');
        $this->redirect('users');
    }

    /**
     * @param array<string,mixed>|null $existing
     * @return array<string,mixed>
     */
    private function validateInput(?array $existing): array
    {
        $name     = (string) $this->input('name', '');
        $username = (string) $this->input('username', '');
        $role     = (string) $this->input('role', 'kasir');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirmation'] ?? '');
        $isActive = (int) $this->input('is_active', $existing['is_active'] ?? 1) ? 1 : 0;

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100) {
            $errors[] = 'Nama wajib diisi (maksimal 100 karakter).';
        }
        if ($username === '' || mb_strlen($username) > 50 || !preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            $errors[] = 'Username wajib diisi (huruf, angka, titik, garis bawah, tanda hubung; maksimal 50).';
        }
        if (!in_array($role, self::ROLES, true)) {
            $errors[] = 'Role tidak valid.';
        }

        $users = new User();
        if ($username !== '' && $users->usernameExists($username, $existing['id'] ?? null)) {
            $errors[] = 'Username "' . $username . '" sudah digunakan.';
        }

        $isNew = $existing === null;
        if ($isNew || $password !== '') {
            if (mb_strlen($password) < 6) {
                $errors[] = 'Password minimal 6 karakter.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Konfirmasi password tidak cocok.';
            }
        }

        if ($errors !== []) {
            throw new ValidationException(implode(' ', $errors));
        }

        $data = [
            'name'      => $name,
            'username'  => $username,
            'role'      => $role,
            'is_active' => $isActive,
        ];
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $data;
    }
}
