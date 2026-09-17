<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$total_buku    = $pdo->query("SELECT COALESCE(SUM(stok),0) AS total FROM buku")->fetch()['total'];
$total_judul   = $pdo->query("SELECT COUNT(*) AS total FROM buku")->fetch()['total'];
$total_anggota = $pdo->query("SELECT COUNT(*) AS total FROM anggota")->fetch()['total'];
$total_pinjam  = $pdo->query("SELECT COUNT(*) AS total FROM peminjaman WHERE status IN ('dipinjam','terlambat')")->fetch()['total'];

// Update status jadi 'terlambat' otomatis kalau lewat jatuh tempo & belum dikembalikan
$pdo->query("UPDATE peminjaman SET status = 'terlambat' WHERE status = 'dipinjam' AND tanggal_jatuh_tempo < CURDATE()");

$peminjaman_terbaru = $pdo->query("
    SELECT p.id, a.nama AS nama_anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status
    FROM peminjaman p
    JOIN anggota a ON a.id = p.anggota_id
    ORDER BY p.id DESC
    LIMIT 5
")->fetchAll();

$page_title = 'Dashboard';
require 'includes/header.php';
?>

<h3 class="mb-4">Dashboard</h3>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card p-3">
      <div class="text-muted small">Judul Buku</div>
      <div class="fs-3 fw-bold"><?= $total_judul ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="text-muted small">Total Stok Buku</div>
      <div class="fs-3 fw-bold"><?= $total_buku ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="text-muted small">Anggota</div>
      <div class="fs-3 fw-bold"><?= $total_anggota ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card p-3">
      <div class="text-muted small">Sedang Dipinjam</div>
      <div class="fs-3 fw-bold"><?= $total_pinjam ?></div>
    </div>
  </div>
</div>

<div class="card p-3">
  <h5 class="mb-3">Peminjaman Terbaru</h5>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>#</th><th>Anggota</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (!$peminjaman_terbaru): ?>
          <tr><td colspan="5" class="text-center text-muted">Belum ada data peminjaman</td></tr>
        <?php else: foreach ($peminjaman_terbaru as $p): ?>
          <tr>
            <td>#<?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nama_anggota']) ?></td>
            <td><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
            <td><?= date('d M Y', strtotime($p['tanggal_jatuh_tempo'])) ?></td>
            <td>
              <?php
                $badge = ['dipinjam'=>'primary','dikembalikan'=>'success','terlambat'=>'danger','hilang'=>'dark'];
                $b = $badge[$p['status']] ?? 'secondary';
              ?>
              <span class="badge bg-<?= $b ?>"><?= ucfirst($p['status']) ?></span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
