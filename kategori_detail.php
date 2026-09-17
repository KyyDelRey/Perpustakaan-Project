<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: kategori_list.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->execute([$id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Kategori tidak ditemukan.'];
    header('Location: kategori_list.php');
    exit;
}

$buku = $pdo->prepare("SELECT * FROM buku WHERE kategori_id = ? ORDER BY judul ASC");
$buku->execute([$id]);
$buku = $buku->fetchAll();

$page_title = $kategori['nama_kategori'];
require 'includes/header.php';
?>

<a href="kategori_list.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Buku dalam kategori "<?= htmlspecialchars($kategori['nama_kategori']) ?>"</h3>
  <span class="badge bg-dark fs-6"><?= count($buku) ?> judul</span>
</div>

<div class="row g-3">
  <?php if (!$buku): ?>
    <p class="text-muted">Belum ada buku di kategori ini.</p>
  <?php else: foreach ($buku as $b): ?>
    <div class="col-md-3 col-sm-4 col-6">
      <a href="buku_detail.php?id=<?= $b['id'] ?>" class="text-decoration-none text-dark">
        <div class="card p-2 h-100">
          <?php if ($b['foto']): ?>
            <img src="assets/uploads/buku/<?= htmlspecialchars($b['foto']) ?>" class="rounded mb-2" style="aspect-ratio:2/3; object-fit:cover; width:100%;">
          <?php else: ?>
            <div class="bg-secondary bg-opacity-10 rounded mb-2 d-flex align-items-center justify-content-center" style="aspect-ratio:2/3;">
              <i class="bi bi-book text-secondary" style="font-size:2rem;"></i>
            </div>
          <?php endif; ?>
          <div class="fw-semibold small"><?= htmlspecialchars($b['judul']) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($b['penulis']) ?></div>
          <div class="small">Stok: <?= $b['stok'] ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
