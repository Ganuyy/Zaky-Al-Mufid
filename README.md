# SipOrder (SO)

Aplikasi web pemesanan minuman modern berbasis PHP 8+ dengan struktur MVC, Bootstrap 5, dan MySQL. Mendukung role Admin, Kasir, dan Barista dengan fitur POS, pembayaran, struk thermal, antrean barista, dan laporan penjualan.

## Daftar Isi

- [Spesifikasi Teknis](#spesifikasi-teknis)
- [Struktur Folder](#struktur-folder)
- [Instalasi (XAMPP)](#instalasi-xampp)
- [Konfigurasi](#konfigurasi)
- [Data Awal (Seeder)](#data-awal-seeder)
- [Cara Menjalankan](#cara-menjalankan)
- [Akun Demo](#akun-demo)
- [Fitur Utama](#fitur-utama)
- [Struk Pembayaran (Thermal)](#struk-pembayaran-thermal)
- [Keamanan](#keamanan)
- [Validasi & Uji Coba](#validasi--uji-coba)
- [Keterangan Teknis](#keterangan-teknis)

## Spesifikasi Teknis

- PHP 8.1+ (tested 8.1.10)
- MySQL / MariaDB 5.7+ (InnoDB)
- Apache (XAMPP / Laragon)
- Frontend: HTML5, CSS3, JavaScript, Bootstrap 5.3.3, Bootstrap Icons
- Backend: PHP native, struktur MVC + Core Framework sederhana
- Autentikasi: Session PHP, `password_hash` + `password_verify`
- CSRF Token, Prepared Statements (PDO)
- Format struk: thermal 58 mm & 80 mm (CSS @media print)

## Struktur Folder

```
kopi-pintar/
├── app/
│   ├── Core/           -> Env, Database, Router, Controller, Model, View, Session, helpers, ValidationException
│   ├── Controllers/    -> Auth, Dashboard, Menu, Category, User, Order, Payment, Receipt, Barista, Report
│   ├── Models/         -> User, Category, Menu, Order, OrderItem, Payment, OrderStatusLog, Report
│   ├── Services/       -> OrderService, PaymentService, ReceiptService, OrderStatusService
│   ├── Middleware/     -> AuthMiddleware, GuestMiddleware, RoleMiddleware, CsrfMiddleware
│   └── Views/          -> layouts, auth, dashboard, menus, users, pos, barista, reports, receipt, errors
├── config/             -> database.php, app.php
├── public/             -> index.php, assets/, .htaccess
├── routes/             -> web.php
├── database/           -> schema.sql, seed.sql, seed.php
├── storage/logs/       -> log aplikasi (otomatis dibuat saat error)
├── .env.example        -> template konfigurasi
├── .htaccess           -> rewrite ke folder public/
└── README.md
```

## Instalasi (XAMPP)

1. **Ekstrak / letakkan proyek**  
   Tempatkan folder `kopi-pintar` ke dalam `C:\xampp\htdocs\` (atau direktori htdocs XAMPP Anda).

2. **Hidupkan XAMPP**  
   Jalankan Apache dan MySQL.

3. **Buat database**  
   Buka `http://localhost/phpmyadmin`, buat database baru bernama `kopi_pintar` dengan collation `utf8mb4_unicode_ci`.  
   (Atau bisa lewat CLI MySQL)

## Konfigurasi

1. **Salin .env**  
   Di root folder `kopi-pintar`, salin `.env.example` menjadi `.env`. Buka `.env` dan sesuaikan jika diperlukan.

   ```ini
   APP_NAME="SipOrder"
   APP_SHORT="SO"
   APP_DEBUG=true
   APP_TIMEZONE="Asia/Jakarta"

   APP_ADDRESS="Jl. Bumi Lestari, Kabupaten Bekasi"
   APP_PHONE="0899-0091-090"

   RECEIPT_WIDTH=58

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kopi_pintar
   DB_USERNAME=root
   DB_PASSWORD=
   ```

2. **(Opsional) Ubah APP_DEBUG**  
   Pada lingkungan produksi sebaiknya `APP_DEBUG=false` untuk menyembunyikan detail error.

3. **Migrasi database untuk aplikasi yang sudah berjalan**  
   Untuk database lama (yang sudah berisi transaksi), jalankan migrasi tanpa menghapus data:

   ```bash
   php database/migrate.php
   ```

   Atau impor `database/migrations/20261116_customer_and_custom_notes.sql` melalui phpMyAdmin.
   Migrasi menambahkan `orders.customer_name` dan memperluas `order_items.notes` ke 255 karakter.

## Data Awal (Seeder)

Terdapat dua cara untuk mengisi struktur tabel dan data awal.

### Cara A (PHP Seeder - Direkomendasikan)
Jalankan dari CLI atau terminal di dalam folder `kopi-pintar`:

```bash
cd C:\xampp\htdocs\kopi-pintar
php database/seed.php
```

Seeder akan:
- Membuat tabel sesuai `schema.sql`
- Mengisi data awal (kategori, menu, akun demo, transaksi contoh)
- Menghasilkan password hash asli untuk "password"

Output berhasil akan menampilkan:  
`Seeding selesai. Login: admin / kasir01 / barista01  (password: password)`

### Cara B (Impor SQL via phpMyAdmin)
1. Buka phpMyAdmin → database `kopi_pintar`
2. Pilih menu `Import` → unggah file `database/schema.sql` → Go
3. Kemudian unggah `database/seed.sql` → Go

> Catatan: `seed.sql` menggunakan password hash baku untuk "password". Jika terjadi masalah login, gunakan Cara A.

## Cara Menjalankan

Akses aplikasi melalui browser:

```
http://localhost/kopi-pintar/public
```

Atau jika Anda menggunakan rewrite root:
```
http://localhost/kopi-pintar/
```

File `.htaccess` di root sudah mengarahkan request ke `public/`.

## Akun Demo

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Kasir | `kasir01` | `password` |
| Barista | `barista01` | `password` |

Akun `kasir02` berstatus nonaktif untuk demonstrasi.

## Fitur Utama

### Admin
- Dashboard dengan ringkasan penjualan
- Kelola menu (tambah/ubah/hapus/nonaktifkan), kategori
- Kelola pengguna (tambah/ubah/nonaktifkan), role Admin/Kasir/Barista
- Riwayat transaksi & laporan penjualan (periode, metode bayar, top menu)
- Export CSV laporan

### Kasir (POS)
- Kartu menu + filter kategori/cari
- Keranjang dinamis, qty +/-, catatan item
- Customisasi produk opsional (checkbox): gula, suhu, level es, dan pilihan **Lainnya** dengan catatan manual
- Produk sama dengan customisasi berbeda menjadi baris pesanan terpisah
- Input **Nama Pelanggan** opsional saat checkout
- Checkout dengan validasi server-side (hitung total, diskon, uang kembalian)
- Pembayaran Tunai, QRIS, Kartu Debit, E-Wallet
- Konfirmasi pembayaran (non-tunai wajib nomor referensi)
- Struk bukti pembayaran + cetak ulang (tidak membuat transaksi baru)
- Alamat & telepon toko hanya ditampilkan pada struk (dashboard tetap minimalis)

### Barista
- Antrean pesanan (hanya menampilkan yang sudah dibayar & lunas)
- Nama pelanggan & catatan customisasi tampil jelas di antrean dan detail pesanan
- Terima & Proses → Tandai Siap Diambil → Selesai
- Bisa membatalkan dari status `dibayar`/`diproses`
- Riwayat selesai/dibatalkan

## Struk Pembayaran (Thermal)

- Format menyerupai struk kasir mesin thermal
- Tersedia pilihan ukuran **58 mm** dan **80 mm**
- CSS `@media print` menampilkan hanya bagian struk
- Data struk berasal dari database (snapshot harga, jumlah, subtotal)
- Mencetak ulang struk tidak menggandakan transaksi
- Format Rupiah `Rp 48.000`

## Keamanan

- `password_hash` / `password_verify`
- Prepared Statements (PDO) untuk seluruh query berparam
- CSRF Token untuk seluruh POST
- Validasi server-side (jumlah, harga, ketersediaan, uang tunai, referensi)
- Output escaping (`htmlspecialchars`) untuk mencegah XSS
- Session aman (`httponly`, `samesite=Lax`, regenerate_id saat login)
- Role-based access control + middleware
- Database Transaction saat proses pembayaran
- Proteksi double-submit (checkout token + disable submit button)
- Nomor transaksi unik dengan retry saat duplikasi
- Error logging ke `storage/logs/app-YYYY-MM-DD.log`

## Validasi & Uji Coba

Beberapa skenario yang telah diperhatikan:

| Uji Coba | Hasil |
|---|---|
| Login salah / akun nonaktif | Ditolak dengan pesan aman |
| Akses halaman tidak sesuai role | Diroleks ke dashboard / ditolak |
| Menambah 0 atau jumlah negatif | Ditolak |
| Pembayaran tunai kurang | Ditolak, tampil kembalian negatif |
| Menu tidak tersedia | Dilarang ditambahkan / dicegat saat checkout |
| QRIS tanpa referensi | Ditolak (wajib konfirmasi) |
| Double click "Konfirmasi Pembayaran" | Dicegah via token + disable button |
| Harga berubah di admin | Struk lama tetap menggunakan snapshot |
| Transaksi belum lunas tidak muncul di antrean barista | Terpenuhi |
| Hapus menu yang pernah dipakai | Otomatis dinonaktifkan (histori terjaga) |
| Hapus kategori yang masih punya menu | Ditolak (terdapat relasi) |
| Hapus admin aktif terakhir | Ditolak (menjaga akses) |
| Cetak ulang struk | Tidak membuat transaksi baru |
| Laporan hanya menghitung transaksi lunas | Terpenuhi |

## Keterangan Teknis

- **Harga & mata uang**: disimpan sebagai `DECIMAL(12,2)`, ditampilkan tanpa desimal.
- **Status terpisah**: `order_status` (pengerjaan barista) dan `payment_status` (kasir/pembayaran).
- **Relasi aman**: FK menggunakan InnoDB; penggunaan `ON DELETE SET NULL` untuk menjaga audit trail.
- **Cart**: disimpan di session PHP, harga selalu divalidasi ulang dari database saat checkout.
- **Image upload**: format JPG/PNG/WEBP, max 2MB, disimpan di `public/assets/images/menus/`.
- **Responsive**: tampilan berfungsi baik di desktop maupun mobile.
- **Date/Time**: timezone Asia/Jakarta.

© 2026 SipOrder (SO) - Aplikasi Pemesanan Minuman.
