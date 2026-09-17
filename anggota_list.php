<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tambah') {
    $stmt = $pdo->prepare("INSERT INTO anggota (kode_anggota, nama, alamat, no_hp) VALUES (?, ?, ?, ?)");
    $stmt->execute([trim($_POST['kode_anggota']), trim($_POST['nama']), trim($_POST['alamat']), trim($_POST['no_hp'])]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anggota berhasil ditambahkan.'];
    header('Location: anggota_list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE anggota SET kode_anggota=?, nama=?, alamat=?, no_hp=? WHERE id=?");
    $stmt->execute([trim($_POST['kode_anggota']), trim($_POST['nama']), trim($_POST['alamat']), trim($_POST['no_hp']), $_POST['id']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anggota berhasil diupdate.'];
    header('Location: anggota_list.php');
    exit;
}

if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM anggota WHERE id = ?");
        $stmt->execute([$_GET['hapus']]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anggota berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal hapus: anggota ini masih punya riwayat peminjaman.'];
    }
    header('Location: anggota_list.php');
    exit;
}

$anggota = $pdo->query("SELECT * FROM anggota ORDER BY nama ASC")->fetchAll();
$page_title = 'Anggota';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Data Anggota</h3>
  <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-lg"></i> Tambah Anggota</button>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>#</th><th>Kode</th><th>Nama</th><th>Alamat</th><th>No HP</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        <?php if (!$anggota): ?>
          <tr><td colspan="6" class="text-center text-muted">Belum ada anggota</td></tr>
        <?php else: foreach ($anggota as $a): ?>
          <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['kode_anggota']) ?></td>
            <td><?= htmlspecialchars($a['nama']) ?></td>
            <td><?= htmlspecialchars($a['alamat']) ?></td>
            <td><?= htmlspecialchars($a['no_hp']) ?></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $a['id'] ?>"><i class="bi bi-pencil"></i></button>
              <a href="anggota_list.php?hapus=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus anggota ini?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>

          <div class="modal fade" id="modalEdit<?= $a['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" class="modal-content">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <div class="modal-header"><h5 class="modal-title">Edit Anggota</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                  <div class="mb-2"><label class="form-label">Kode Anggota</label><input type="text" name="kode_anggota" class="form-control" value="<?= htmlspecialchars($a['kode_anggota']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($a['nama']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Alamat</label><input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($a['alamat']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">No HP</label><input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($a['no_hp']) ?>" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-dark">Simpan</button></div>
              </form>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-header"><h5 class="modal-title">Tambah Anggota</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Kode Anggota</label><input type="text" name="kode_anggota" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Alamat</label><input type="text" name="alamat" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">No HP</label><input type="text" name="no_hp" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-dark">Simpan</button></div>
    </form>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
