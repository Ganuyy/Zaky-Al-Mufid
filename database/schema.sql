-- ============================================================================
-- SipOrder (SO) - Database Schema
-- Engine : InnoDB
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- Semua nominal uang menggunakan DECIMAL (bukan FLOAT) agar akurat.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+07:00';

CREATE DATABASE IF NOT EXISTS `kopi_pintar`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kopi_pintar`;

DROP TABLE IF EXISTS `order_status_logs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- ----------------------------------------------------------------------------
-- Tabel: users
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)    NOT NULL,
  `username`   VARCHAR(50)     NOT NULL,
  `password`   VARCHAR(255)    NOT NULL,
  `role`       ENUM('admin','kasir','barista') NOT NULL DEFAULT 'kasir',
  `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: categories
-- ----------------------------------------------------------------------------
CREATE TABLE `categories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(80)     NOT NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: menus
-- Kategori tidak boleh dihapus selama masih punya menu (RESTRICT).
-- ----------------------------------------------------------------------------
CREATE TABLE `menus` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  BIGINT UNSIGNED NOT NULL,
  `name`         VARCHAR(120)    NOT NULL,
  `description`  VARCHAR(255)    NULL,
  `price`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `image`        VARCHAR(255)    NULL,
  `is_available` TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_menus_category` (`category_id`),
  KEY `idx_menus_available` (`is_available`),
  KEY `idx_menus_name` (`name`),
  CONSTRAINT `fk_menus_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: orders
-- order_status  = status pengerjaan pesanan (barista)
-- payment_status = status pembayaran (kasir)
-- Keduanya dipisah agar alur pembayaran & pembuatan minuman tidak tercampur.
-- cashier_id RESTRICT: histori transaksi tetap utuh walau akun dinonaktifkan.
-- ----------------------------------------------------------------------------
CREATE TABLE `orders` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number`    VARCHAR(30)     NOT NULL,
  `cashier_id`      BIGINT UNSIGNED NOT NULL,
  `customer_name`   VARCHAR(100)    NULL,
  `order_status`    ENUM('menunggu_pembayaran','dibayar','diproses','siap_diambil','selesai','dibatalkan')
                    NOT NULL DEFAULT 'menunggu_pembayaran',
  `payment_status`  ENUM('belum_dibayar','lunas','gagal','dikembalikan')
                    NOT NULL DEFAULT 'belum_dibayar',
  `payment_method`  VARCHAR(30)     NULL,
  `subtotal`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `discount`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `total`           DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `amount_paid`     DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `change_amount`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at`         DATETIME        NULL,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `idx_orders_cashier` (`cashier_id`),
  KEY `idx_orders_status` (`order_status`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_created` (`created_at`),
  KEY `idx_orders_paid_at` (`paid_at`),
  CONSTRAINT `fk_orders_cashier`
    FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: order_items
-- Snapshot (menu_name, unit_price) agar struk lama tidak berubah saat harga menu berubah.
-- menu_id SET NULL: menu boleh dihapus tapi baris histori tetap ada.
-- ----------------------------------------------------------------------------
CREATE TABLE `order_items` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`   BIGINT UNSIGNED NOT NULL,
  `menu_id`    BIGINT UNSIGNED NULL,
  `menu_name`  VARCHAR(120)    NOT NULL,
  `unit_price` DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `quantity`   INT UNSIGNED    NOT NULL DEFAULT 1,
  `subtotal`   DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `notes`      VARCHAR(255)    NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_items_order` (`order_id`),
  KEY `idx_items_menu` (`menu_id`),
  CONSTRAINT `fk_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_items_menu`
    FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: payments
-- ----------------------------------------------------------------------------
CREATE TABLE `payments` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`          BIGINT UNSIGNED NOT NULL,
  `payment_reference` VARCHAR(64)     NULL,
  `payment_method`    VARCHAR(30)     NOT NULL,
  `amount`            DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `status`            ENUM('pending','lunas','gagal','dikembalikan')
                      NOT NULL DEFAULT 'pending',
  `paid_at`           DATETIME        NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_status` (`status`),
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabel: order_status_logs  (audit trail perubahan status)
-- ----------------------------------------------------------------------------
CREATE TABLE `order_status_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`   BIGINT UNSIGNED NOT NULL,
  `user_id`    BIGINT UNSIGNED NULL,
  `old_status` VARCHAR(30)     NULL,
  `new_status` VARCHAR(30)     NOT NULL,
  `notes`      VARCHAR(255)    NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_order` (`order_id`),
  KEY `idx_logs_user` (`user_id`),
  CONSTRAINT `fk_logs_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
