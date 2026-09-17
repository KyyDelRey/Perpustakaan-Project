-- =========================================================
-- MIGRASI: Tambah fitur Detail Buku
-- Jalankan file ini di phpMyAdmin (tab SQL) kalau database
-- `perpustakaan` LU UDAH ADA datanya dan gak mau di-reset.
-- Kalau baru install dari nol, gak perlu jalankan ini —
-- cukup import database/perpustakaan_fixed.sql yang baru.
-- =========================================================

USE perpustakaan;

ALTER TABLE buku
  ADD COLUMN sinopsis TEXT NULL AFTER stok,
  ADD COLUMN foto VARCHAR(255) NULL AFTER sinopsis;
