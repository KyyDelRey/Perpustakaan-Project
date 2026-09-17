-- =========================================================
-- PERPUSTAKAAN - Struktur database yang sudah diperbaiki
-- Perbaikan dari versi sebelumnya:
-- 1. Tambah AUTO_INCREMENT di semua kolom id
-- 2. Samakan tipe data kolom relasi (anggota_id, kategori_id, dll jadi INT)
-- 3. Tambah FOREIGN KEY antar tabel
-- 4. tanggal_kembali dibuat boleh NULL (belum dikembalikan)
-- 5. buku.tahun diganti dari DATE -> YEAR (lebih masuk akal untuk "tahun terbit")
-- 6. Tambah default value & kolom created_at buat jaga-jaga
-- 7. users.password disiapkan buat menyimpan hash (bukan plain text)
-- =========================================================

DROP DATABASE IF EXISTS perpustakaan;
CREATE DATABASE perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE perpustakaan;

-- --------------------------------------------------------
-- Tabel kategori
-- --------------------------------------------------------
CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO kategori (nama_kategori) VALUES
('Fiksi'), ('Non-Fiksi'), ('Sains'), ('Sejarah'), ('Komik');

-- --------------------------------------------------------
-- Tabel anggota
-- --------------------------------------------------------
CREATE TABLE anggota (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_anggota VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabel buku
-- --------------------------------------------------------
CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_buku VARCHAR(50) NOT NULL UNIQUE,
  judul VARCHAR(150) NOT NULL,
  penulis VARCHAR(100) NOT NULL,
  penerbit VARCHAR(100) NOT NULL,
  tahun YEAR NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  sinopsis TEXT NULL,
  foto VARCHAR(255) NULL,
  kategori_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun, stok, sinopsis, kategori_id) VALUES
('KD1', 'Seporsi Mie Kuah', 'Djoko Anwar', 'Idlix Media', 2026, 10, 'Sinopsis contoh, silakan diedit lewat aplikasi.', 1);

-- --------------------------------------------------------
-- Tabel peminjaman
-- --------------------------------------------------------
CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  anggota_id INT NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_jatuh_tempo DATE NOT NULL,
  tanggal_kembali DATE NULL,
  status ENUM('dipinjam','dikembalikan','terlambat','hilang') NOT NULL DEFAULT 'dipinjam',
  denda INT NOT NULL DEFAULT 0,
  FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabel detail_peminjaman
-- --------------------------------------------------------
CREATE TABLE detail_peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id INT NOT NULL,
  buku_id INT NOT NULL,
  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabel users (buat login admin)
-- --------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- User default: username = admin, password = admin123 (sudah di-hash pakai bcrypt)
INSERT INTO users (username, password) VALUES
('admin', '$2b$12$7ioPqFvfthzxCY.I6E0Xi.CyDJwwiaPCz0G0WNFNLWPKNSDd8VfAe');
