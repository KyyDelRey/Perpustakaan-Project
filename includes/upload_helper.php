<?php
/**
 * Proses upload foto sampul buku.
 * Return nama file baru kalau sukses upload, null kalau tidak ada file baru,
 * atau throw Exception kalau file tidak valid.
 */
function upload_foto_buku(?array $file): ?string
{
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // gak ada file yang di-upload
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload foto gagal (kode error: ' . $file['error'] . ').');
    }

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'avif' => 'image/avif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!array_key_exists($ext, $allowed)) {
        throw new Exception('Format foto harus JPG, PNG, WEBP, atau AVIF.');
    }

    if ($file['size'] > 2 * 1024 * 1024) { // maks 2MB
        throw new Exception('Ukuran foto maksimal 2MB.');
    }

    $namaBaru = uniqid('buku_') . '.' . $ext;
    $tujuan   = __DIR__ . '/../assets/uploads/buku/' . $namaBaru;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        throw new Exception('Gagal menyimpan file foto ke server.');
    }

    return $namaBaru;
}

function hapus_foto_buku(?string $namaFile): void
{
    if (!$namaFile) return;
    $path = __DIR__ . '/../assets/uploads/buku/' . $namaFile;
    if (is_file($path)) {
        @unlink($path);
    }
}
