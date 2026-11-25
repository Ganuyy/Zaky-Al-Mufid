<?php
/**
 * SipOrder - Seeder PHP
 * Menjalankan schema.sql lalu mengisi data awal dengan password_hash() asli.
 *
 * Cara pakai (CLI):
 *   php database/seed.php
 *
 * Cara pakai (browser, jika php CLI tidak tersedia): tidak disarankan untuk produksi.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'cli-server') {
    http_response_code(403);
    exit("Seeder hanya boleh dijalankan dari command line.\n");
}

$base = dirname(__DIR__);
require $base . '/app/Core/Env.php';
App\Core\Env::load($base . '/.env');

$cfg = require $base . '/config/database.php';

$dsnServer = sprintf('mysql:host=%s;port=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);

try {
    $pdo = new PDO($dsnServer, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Gagal koneksi ke MySQL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo "Membuat schema..." . PHP_EOL;
$schema = file_get_contents($base . '/database/schema.sql');
$pdo->exec($schema);

$pdo->exec("USE `{$cfg['database']}`");

$hash = password_hash('password', PASSWORD_DEFAULT);
echo "Menghasilkan hash demo: $hash" . PHP_EOL;

$pdo->beginTransaction();
try {
    $users = [
        [1, 'Administrator', 'admin',     'admin',   1],
        [2, 'Siti Kasir',    'kasir01',   'kasir',   1],
        [3, 'Budi Barista',  'barista01', 'barista', 1],
        [4, 'Rina Kasir',    'kasir02',   'kasir',   0],
    ];
    $stmt = $pdo->prepare('INSERT INTO users (id,name,username,password,role,is_active) VALUES (?,?,?,?,?,?)');
    foreach ($users as $u) {
        $stmt->execute([$u[0], $u[1], $u[2], $hash, $u[3], $u[4]]);
    }

    $cats = [[1,'Kopi'],[2,'Non-Kopi'],[3,'Teh'],[4,'Dessert']];
    $stmt = $pdo->prepare('INSERT INTO categories (id,name) VALUES (?,?)');
    foreach ($cats as $c) {
        $stmt->execute($c);
    }

    $menus = [
        [1,1,'Es Kopi Susu','Espresso + susu segar + gula aren',18000,1],
        [2,1,'Americano','Espresso dengan air panas/dingin',18000,1],
        [3,1,'Cappuccino','Espresso dengan foam susu lembut',22000,1],
        [4,1,'Caffe Latte','Espresso dengan susu steamed',23000,1],
        [5,1,'Kopi Tubruk','Kopi hitam tradisional gula batu',12000,1],
        [6,1,'Espresso','Single shot espresso murni',15000,1],
        [7,2,'Matcha Latte','Matcha premium dengan susu',24000,1],
        [8,2,'Chocolate','Cokelat kental dengan susu',20000,1],
        [9,2,'Red Velvet','Minuman red velvet creamy',23000,1],
        [10,3,'Es Teh Manis','Teh manis dingin segar',8000,1],
        [11,3,'Lemon Tea','Teh dengan perasan lemon',14000,1],
        [12,4,'Croissant','Pastry mentega panggang',20000,1],
        [13,4,'Cheesecake','New York cheesecake slice',28000,1],
        [14,4,'Brownies','Brownies cokelat fudgy',18000,1],
        [15,4,'Donat Gula','Donat empuk taburan gula',12000,1],
    ];
    $stmt = $pdo->prepare('INSERT INTO menus (id,category_id,name,description,price,is_available) VALUES (?,?,?,?,?,?)');
    foreach ($menus as $m) {
        $stmt->execute($m);
    }

    $orders = [
        [1,'SO-20261013-0001',2,'selesai','lunas','tunai',48000,0,48000,50000,2000,'2026-10-13 09:15:00','2026-10-13 09:16:00'],
        [2,'SO-20261013-0002',2,'selesai','lunas','qris',38000,0,38000,38000,0,'2026-10-13 14:30:00','2026-10-13 14:31:00'],
        [3,'SO-20261014-0001',2,'dibayar','lunas','tunai',58000,0,58000,60000,2000,'2026-10-14 08:05:00','2026-10-14 08:06:00'],
    ];
    $stmt = $pdo->prepare('INSERT INTO orders (id,order_number,cashier_id,order_status,payment_status,payment_method,subtotal,discount,total,amount_paid,change_amount,created_at,paid_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($orders as $o) {
        $stmt->execute($o);
    }

    $items = [
        [1,1,'Es Kopi Susu',18000,2,36000,'Less sugar'],
        [1,5,'Kopi Tubruk',12000,1,12000,null],
        [2,1,'Es Kopi Susu',18000,1,18000,'Tanpa es'],
        [2,12,'Croissant',20000,1,20000,null],
        [3,4,'Caffe Latte',23000,2,46000,'Extra shot'],
        [3,10,'Es Teh Manis',8000,1,8000,null],
    ];
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id,menu_id,menu_name,unit_price,quantity,subtotal,notes) VALUES (?,?,?,?,?,?,?)');
    foreach ($items as $i) {
        $stmt->execute($i);
    }

    $payments = [
        [1,'TRX-DEMO-0001','tunai',48000,'lunas','2026-10-13 09:16:00'],
        [2,'TRX-DEMO-0002','qris',38000,'lunas','2026-10-13 14:31:00'],
        [3,'TRX-DEMO-0003','tunai',58000,'lunas','2026-10-14 08:06:00'],
    ];
    $stmt = $pdo->prepare('INSERT INTO payments (order_id,payment_reference,payment_method,amount,status,paid_at) VALUES (?,?,?,?,?,?)');
    foreach ($payments as $p) {
        $stmt->execute($p);
    }

    $logs = [
        [1,2,'menunggu_pembayaran','dibayar','Pembayaran tunai diterima','2026-10-13 09:16:00'],
        [1,3,'dibayar','diproses','Pesanan mulai dibuat','2026-10-13 09:17:00'],
        [1,3,'diproses','siap_diambil','Pesanan siap','2026-10-13 09:22:00'],
        [1,3,'siap_diambil','selesai','Diambil pelanggan','2026-10-13 09:25:00'],
        [2,2,'menunggu_pembayaran','dibayar','Pembayaran QRIS terkonfirmasi','2026-10-13 14:31:00'],
        [2,3,'dibayar','diproses','Pesanan mulai dibuat','2026-10-13 14:32:00'],
        [2,3,'diproses','siap_diambil','Pesanan siap','2026-10-13 14:36:00'],
        [2,3,'siap_diambil','selesai','Diambil pelanggan','2026-10-13 14:40:00'],
        [3,2,'menunggu_pembayaran','dibayar','Pembayaran tunai diterima','2026-10-14 08:06:00'],
    ];
    $stmt = $pdo->prepare('INSERT INTO order_status_logs (order_id,user_id,old_status,new_status,notes,created_at) VALUES (?,?,?,?,?,?)');
    foreach ($logs as $l) {
        $stmt->execute($l);
    }

    $pdo->commit();
    echo "Seeding selesai. Login: admin / kasir01 / barista01  (password: password)" . PHP_EOL;
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Seeding gagal: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
