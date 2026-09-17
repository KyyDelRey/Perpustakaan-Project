<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: peminjaman_list.php');
    exit;
}

$DENDA_PER_HARI = 1000; // Rp1.000 / hari keterlambatan

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM peminjaman WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $peminjaman = $stmt->fetch();

    if (!$peminjaman) {
        throw new Exception('Data peminjaman tidak ditemukan.');
    }

    $hari_ini = date('Y-m-d');
    $telat_hari = max(0, (strtotime($hari_ini) - strtotime($peminjaman['tanggal_jatuh_tempo'])) / 86400);
    $denda = $telat_hari > 0 ? $telat_hari * $DENDA_PER_HARI : 0;

    $update = $pdo->prepare("UPDATE peminjaman SET tanggal_kembali = ?, status = 'dikembalikan', denda = ? WHERE id = ?");
    $update->execute([$hari_ini, $denda, $id]);

    // kembalikan stok semua buku di peminjaman ini
    $bukuList = $pdo->prepare("SELECT buku_id FROM detail_peminjaman WHERE peminjaman_id = ?");
    $bukuList->execute([$id]);
    $updateStok = $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
    foreach ($bukuList->fetchAll() as $row) {
        $updateStok->execute([$row['buku_id']]);
    }

    $pdo->commit();

    $msg = $denda > 0
        ? "Buku dikembalikan. Terlambat $telat_hari hari, denda Rp " . number_format($denda,0,',','.') . "."
        : "Buku berhasil dikembalikan tepat waktu.";
    $_SESSION['flash'] = ['type' => $denda > 0 ? 'warning' : 'success', 'message' => $msg];
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal: ' . $e->getMessage()];
}

header('Location: peminjaman_list.php');
exit;
