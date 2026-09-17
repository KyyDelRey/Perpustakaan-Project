<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

// auto update status terlambat
$pdo->query("UPDATE peminjaman SET status = 'terlambat' WHERE status = 'dipinjam' AND tanggal_jatuh_tempo < CURDATE()");

$peminjaman = $pdo->query("
    SELECT p.*, a.nama AS nama_anggota, a.kode_anggota
    FROM peminjaman p
    JOIN anggota a ON a.id = p.anggota_id
    ORDER BY p.id DESC
")->fetchAll();

// ambil daftar buku per peminjaman
$detail_stmt = $pdo->prepare("
    SELECT b.judul FROM detail_peminjaman dp
    JOIN buku b ON b.id = dp.buku_id
    WHERE dp.peminjaman_id = ?
");

$page_title = 'Peminjaman';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Data Peminjaman</h3>
  <a href="peminjaman_tambah.php" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Pinjam Buku</a>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>#</th><th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Tgl Kembali</th><th>Status</th><th>Denda</th><th class="text-end">Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$peminjaman): ?>
          <tr><td colspan="9" class="text-center text-muted">Belum ada data peminjaman</td></tr>
        <?php else: foreach ($peminjaman as $p):
          $detail_stmt->execute([$p['id']]);
          $judul_list = array_column($detail_stmt->fetchAll(), 'judul');
          $badge = ['dipinjam'=>'primary','dikembalikan'=>'success','terlambat'=>'danger','hilang'=>'dark'];
          $b = $badge[$p['status']] ?? 'secondary';
        ?>
          <tr>
            <td>#<?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nama_anggota']) ?> <br><small class="text-muted"><?= htmlspecialchars($p['kode_anggota']) ?></small></td>
            <td><?= htmlspecialchars(implode(', ', $judul_list)) ?></td>
            <td><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
            <td><?= date('d M Y', strtotime($p['tanggal_jatuh_tempo'])) ?></td>
            <td><?= $p['tanggal_kembali'] ? date('d M Y', strtotime($p['tanggal_kembali'])) : '-' ?></td>
            <td><span class="badge bg-<?= $b ?>"><?= ucfirst($p['status']) ?></span></td>
            <td><?= $p['denda'] > 0 ? 'Rp ' . number_format($p['denda'],0,',','.') : '-' ?></td>
            <td class="text-end">
              <a href="peminjaman_cetak.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                <i class="bi bi-printer"></i> Cetak
              </a>
              <?php if (in_array($p['status'], ['dipinjam','terlambat'])): ?>
                <a href="peminjaman_kembalikan.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Tandai buku ini sudah dikembalikan?')">
                  <i class="bi bi-check2-circle"></i> Kembalikan
                </a>
              <?php else: ?>
                <span class="text-muted small">Selesai</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
