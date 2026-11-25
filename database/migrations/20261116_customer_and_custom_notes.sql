-- ============================================================================
-- Migrasi: Nama pelanggan & catatan customisasi item
-- Aman dijalankan pada database yang sudah berisi data (tidak menghapus data).
-- Jalankan sekali melalui phpMyAdmin / CLI MySQL:
--   mysql -u root kopi_pintar < database/migrations/20261116_customer_and_custom_notes.sql
-- Atau gunakan: php database/migrate.php
-- ============================================================================

ALTER TABLE `orders`
  ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `cashier_id`;

ALTER TABLE `order_items`
  MODIFY COLUMN `notes` VARCHAR(255) NULL;
