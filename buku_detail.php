<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: buku_list.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, k.nama_kategori FROM buku b
    JOIN kategori k ON k.id = b.kategori_id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Buku tidak ditemukan.'];
    header('Location: buku_list.php');
    exit;
}

// riwayat peminjam buku ini
$riwayat = $pdo->prepare("
    SELECT a.nama, a.kode_anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.tanggal_kembali, p.status
    FROM detail_peminjaman dp
    JOIN peminjaman p ON p.id = dp.peminjaman_id
    JOIN anggota a ON a.id = p.anggota_id
    WHERE dp.buku_id = ?
    ORDER BY p.tanggal_pinjam DESC
");
$riwayat->execute([$id]);
$riwayat = $riwayat->fetchAll();

$page_title = $buku['judul'];
require 'includes/header.php';
?>

<a href="buku_list.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="row g-4">
  <div class="col-md-3">
    <div class="card p-3 text-center">
      <?php if ($buku['foto']): ?>
        <img src="assets/uploads/buku/<?= htmlspecialchars($buku['foto']) ?>" class="img-fluid rounded" style="aspect-ratio:2/3; object-fit:cover; width:100%;">
      <?php else: ?>
        <div class="bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="aspect-ratio:2/3;">
          <i class="bi bi-book text-secondary" style="font-size:3rem;"></i>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-9">
    <div class="card p-4">
      <h3><?= htmlspecialchars($buku['judul']) ?></h3>
      <span class="badge bg-secondary mb-3"><?= htmlspecialchars($buku['nama_kategori']) ?></span>

      <div class="row mb-3">
        <div class="col-sm-6"><strong>Kode Buku</strong><br><?= htmlspecialchars($buku['kode_buku']) ?></div>
        <div class="col-sm-6"><strong>Penulis</strong><br><?= htmlspecialchars($buku['penulis']) ?></div>
        <div class="col-sm-6 mt-2"><strong>Penerbit</strong><br><?= htmlspecialchars($buku['penerbit']) ?></div>
        <div class="col-sm-6 mt-2"><strong>Tahun</strong><br><?= $buku['tahun'] ?></div>
        <div class="col-sm-6 mt-2"><strong>Stok Tersedia</strong><br><?= $buku['stok'] ?></div>
      </div>

      <strong>Sinopsis</strong>
      <p class="text-muted"><?= $buku['sinopsis'] ? nl2br(htmlspecialchars($buku['sinopsis'])) : '<em>Belum ada sinopsis.</em>' ?></p>
    </div>
  </div>
</div>

<div class="card p-3 mt-4">
  <h5 class="mb-3">Riwayat Peminjaman Buku Ini</h5>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Anggota</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$riwayat): ?>
          <tr><td colspan="5" class="text-center text-muted">Buku ini belum pernah dipinjam</td></tr>
        <?php else: foreach ($riwayat as $r):
          $badge = ['dipinjam'=>'primary','dikembalikan'=>'success','terlambat'=>'danger','hilang'=>'dark'];
          $b = $badge[$r['status']] ?? 'secondary';
        ?>
          <tr>
            <td><?= htmlspecialchars($r['nama']) ?> <small class="text-muted">(<?= htmlspecialchars($r['kode_anggota']) ?>)</small></td>
            <td><?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?></td>
            <td><?= date('d M Y', strtotime($r['tanggal_jatuh_tempo'])) ?></td>
            <td><?= $r['tanggal_kembali'] ? date('d M Y', strtotime($r['tanggal_kembali'])) : '-' ?></td>
            <td><span class="badge bg-<?= $b ?>"><?= ucfirst($r['status']) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
