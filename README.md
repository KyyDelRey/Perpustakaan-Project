# Sistem Informasi Perpustakaan (PHP + MySQL)

## Fitur
- Login admin (password sudah di-hash pakai bcrypt)
- CRUD Kategori + halaman Detail (lihat semua buku dalam kategori tsb)
- CRUD Buku (dengan kategori, stok, sinopsis & foto sampul) + halaman Detail (foto, sinopsis, riwayat peminjam)
- CRUD Anggota
- Peminjaman buku (multi-buku sekaligus, otomatis potong stok)
- Pengembalian buku (otomatis hitung denda keterlambatan & kembalikan stok)
- Cetak bukti peminjaman (siap print, isi: data anggota, daftar buku, tenggat waktu, rincian denda)
- Dashboard ringkasan

## Cara Install (Laragon)

1. Copy folder project
   Copy seluruh folder `perpustakaan-app` ke `C:\laragon\www\`

2. **Import database**
   - Buka phpMyAdmin (`localhost/phpmyadmin`)
   - import `database/perpustakaan_fixed.sql`

3. Pastikan folder upload bisa ditulis
   Folder `assets/uploads/buku/` dipakai buat nyimpen foto sampul yang di-upload. Di Laragon (Windows) biasanya otomatis writable, tapi kalau upload gagal, cek permission folder tersebut.

4. Cek konfigurasi koneksi
   Buka `config/koneksi.php`, sesuaikan kalau username/password MySQL kamu bukan default Laragon (`root` / kosong).

5. Jalankan
   - Start Laragon
   - Buka browser ke `http://perpustakaan-app.test/` (Laragon otomatis bikin domain dari nama folder) atau `http://localhost/perpustakaan-app/`

6. **Login default**
   - Username: `admin`
   - Password: `admin123`

   Bisa diganti langsung lewat tabel `users` di phpMyAdmin (pakai password yang di-hash bcrypt, jangan simpan plain text).

## Struktur Folder
```
perpustakaan-app/
├── config/koneksi.php        -> koneksi database (PDO)
├── includes/                 -> header, footer, cek login
├── database/perpustakaan_fixed.sql
├── assets/css/style.css
├── login.php / logout.php / index.php
├── kategori_list.php
├── buku_list.php
├── anggota_list.php
├── peminjaman_list.php / peminjaman_tambah.php / peminjaman_kembalikan.php
```
