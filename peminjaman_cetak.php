<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$DENDA_PER_HARI = 1000; // harus sama dengan yang di peminjaman_kembalikan.php

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: peminjaman_list.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, a.nama AS nama_anggota, a.kode_anggota, a.alamat, a.no_hp
    FROM peminjaman p
    JOIN anggota a ON a.id = p.anggota_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$peminjaman = $stmt->fetch();

if (!$peminjaman) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Data peminjaman tidak ditemukan.'];
    header('Location: peminjaman_list.php');
    exit;
}

$bukuStmt = $pdo->prepare("
    SELECT b.kode_buku, b.judul, b.penulis
    FROM detail_peminjaman dp
    JOIN buku b ON b.id = dp.buku_id
    WHERE dp.peminjaman_id = ?
    ORDER BY b.judul ASC
");
$bukuStmt->execute([$id]);
$bukuList = $bukuStmt->fetchAll();

// hitung info denda / keterlambatan buat ditampilkan
$sudahKembali = $peminjaman['status'] === 'dikembalikan';
$hariAcuan    = $sudahKembali ? $peminjaman['tanggal_kembali'] : date('Y-m-d');
$telatHari    = max(0, (int)((strtotime($hariAcuan) - strtotime($peminjaman['tanggal_jatuh_tempo'])) / 86400));
$estimasiDenda = $sudahKembali ? $peminjaman['denda'] : $telatHari * $DENDA_PER_HARI;

$badgeText = [
    'dipinjam' => 'Dipinjam',
    'dikembalikan' => 'Sudah Dikembalikan',
    'terlambat' => 'Terlambat',
    'hilang' => 'Hilang',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bukti Peminjaman #<?= $peminjaman['id'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #e9ecef; }
  .kertas { max-width: 750px; margin: 30px auto; background: #fff; padding: 40px; }
  .kop { border-bottom: 3px solid #212529; padding-bottom: 15px; margin-bottom: 20px; }
  table.info td { padding: 3px 8px; vertical-align: top; }
  table.buku th, table.buku td { padding: 8px; }
  .ttd-box { width: 220px; text-align: center; }
  .ttd-line { margin-top: 60px; border-top: 1px solid #333; }

  @media print {
    body { background: #fff; }
    .kertas { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body>

<div class="container no-print py-3">
  <a href="peminjaman_list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
  <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Cetak / Print</button>
</div>

<div class="kertas shadow-sm">

  <div class="kop d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-0">Sistem Informasi Perpustakaan</h4>
      <small class="text-muted">Bukti Peminjaman Buku</small>
    </div>
    <div class="text-end">
      <div><strong>No. Transaksi:</strong> #<?= str_pad($peminjaman['id'], 5, '0', STR_PAD_LEFT) ?></div>
      <div><small class="text-muted">Dicetak: <?= date('d M Y H:i') ?></small></div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-6">
      <table class="info">
        <tr><td width="110"><strong>Nama Anggota</strong></td><td>: <?= htmlspecialchars($peminjaman['nama_anggota']) ?></td></tr>
        <tr><td><strong>Kode Anggota</strong></td><td>: <?= htmlspecialchars($peminjaman['kode_anggota']) ?></td></tr>
        <tr><td><strong>Alamat</strong></td><td>: <?= htmlspecialchars($peminjaman['alamat']) ?></td></tr>
        <tr><td><strong>No. HP</strong></td><td>: <?= htmlspecialchars($peminjaman['no_hp']) ?></td></tr>
      </table>
    </div>
    <div class="col-6">
      <table class="info">
        <tr><td width="130"><strong>Tanggal Pinjam</strong></td><td>: <?= date('d F Y', strtotime($peminjaman['tanggal_pinjam'])) ?></td></tr>
        <tr><td><strong>Tenggat Waktu</strong></td><td>: <?= date('d F Y', strtotime($peminjaman['tanggal_jatuh_tempo'])) ?></td></tr>
        <tr><td><strong>Tanggal Kembali</strong></td><td>: <?= $peminjaman['tanggal_kembali'] ? date('d F Y', strtotime($peminjaman['tanggal_kembali'])) : '-' ?></td></tr>
        <tr><td><strong>Status</strong></td><td>: <?= $badgeText[$peminjaman['status']] ?? ucfirst($peminjaman['status']) ?></td></tr>
      </table>
    </div>
  </div>

  <h6>Daftar Buku Dipinjam</h6>
  <table class="table table-bordered buku mb-4">
    <thead class="table-light">
      <tr><th width="40">No</th><th>Kode Buku</th><th>Judul</th><th>Penulis</th></tr>
    </thead>
    <tbody>
      <?php if (!$bukuList): ?>
        <tr><td colspan="4" class="text-center text-muted">Tidak ada data buku</td></tr>
      <?php else: $no = 1; foreach ($bukuList as $b): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($b['kode_buku']) ?></td>
          <td><?= htmlspecialchars($b['judul']) ?></td>
          <td><?= htmlspecialchars($b['penulis']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <h6>Rincian Denda</h6>
  <table class="table table-bordered mb-4">
    <tbody>
      <tr>
        <td width="250">Jumlah Keterlambatan</td>
        <td><?= $telatHari > 0 ? $telatHari . ' hari' : 'Tidak terlambat' ?></td>
      </tr>
      <tr>
        <td>Tarif Denda</td>
        <td>Rp <?= number_format($DENDA_PER_HARI, 0, ',', '.') ?> / hari</td>
      </tr>
      <tr class="table-light">
        <td><strong><?= $sudahKembali ? 'Total Denda' : 'Estimasi Denda Saat Ini' ?></strong></td>
        <td><strong><?= $estimasiDenda > 0 ? 'Rp ' . number_format($estimasiDenda, 0, ',', '.') : 'Rp 0' ?></strong></td>
      </tr>
      <?php if (!$sudahKembali && $telatHari > 0): ?>
      <tr>
        <td colspan="2"><small class="text-muted fst-italic">*Buku belum dikembalikan, denda final dihitung saat pengembalian.</small></td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="d-flex justify-content-between mt-5">
    <div class="ttd-box">
      <div>Peminjam</div>
      <div class="ttd-line">(<?= htmlspecialchars($peminjaman['nama_anggota']) ?>)</div>
    </div>
    <div class="ttd-box">
      <div>Petugas Perpustakaan</div>
      <div class="ttd-line">(....................................)</div>
    </div>
  </div>

</div>

</body>
</html>
