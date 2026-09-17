<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

// Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tambah') {
    $nama = trim($_POST['nama_kategori']);
    if ($nama !== '') {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->execute([$nama]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil ditambahkan.'];
    }
    header('Location: kategori_list.php');
    exit;
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $nama = trim($_POST['nama_kategori']);
    $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
    $stmt->execute([$nama, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil diupdate.'];
    header('Location: kategori_list.php');
    exit;
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal hapus: kategori ini masih dipakai oleh buku.'];
    }
    header('Location: kategori_list.php');
    exit;
}

$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

$page_title = 'Kategori';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Data Kategori</h3>
  <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTambah">
    <i class="bi bi-plus-lg"></i> Tambah Kategori
  </button>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>#</th><th>Nama Kategori</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        <?php if (!$kategori): ?>
          <tr><td colspan="3" class="text-center text-muted">Belum ada kategori</td></tr>
        <?php else: foreach ($kategori as $k): ?>
          <tr>
            <td><?= $k['id'] ?></td>
            <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
            <td class="text-end">
              <a href="kategori_detail.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> Detail
              </a>
              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id'] ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <a href="kategori_list.php?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Yakin hapus kategori ini?')">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit<?= $k['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" class="modal-content">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                <div class="modal-header"><h5 class="modal-title">Edit Kategori</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <label class="form-label">Nama Kategori</label>
                  <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($k['nama_kategori']) ?>" required>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-dark">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-header"><h5 class="modal-title">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nama Kategori</label>
        <input type="text" name="nama_kategori" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-dark">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
