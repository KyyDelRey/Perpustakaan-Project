<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';
require 'includes/upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'tambah') {
    try {
        $foto = upload_foto_buku($_FILES['foto'] ?? null);
        $stmt = $pdo->prepare("INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun, stok, sinopsis, foto, kategori_id) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['kode_buku']), trim($_POST['judul']), trim($_POST['penulis']),
            trim($_POST['penerbit']), $_POST['tahun'], $_POST['stok'],
            trim($_POST['sinopsis']) ?: null, $foto, $_POST['kategori_id']
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil ditambahkan.'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    header('Location: buku_list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    try {
        $foto_baru = upload_foto_buku($_FILES['foto'] ?? null);

        if ($foto_baru) {
            hapus_foto_buku($_POST['foto_lama'] ?? null);
            $foto = $foto_baru;
        } else {
            $foto = $_POST['foto_lama'] ?: null;
        }

        $stmt = $pdo->prepare("UPDATE buku SET kode_buku=?, judul=?, penulis=?, penerbit=?, tahun=?, stok=?, sinopsis=?, foto=?, kategori_id=? WHERE id=?");
        $stmt->execute([
            trim($_POST['kode_buku']), trim($_POST['judul']), trim($_POST['penulis']),
            trim($_POST['penerbit']), $_POST['tahun'], $_POST['stok'],
            trim($_POST['sinopsis']) ?: null, $foto, $_POST['kategori_id'], $_POST['id']
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil diupdate.'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal: ' . $e->getMessage()];
    }
    header('Location: buku_list.php');
    exit;
}

if (isset($_GET['hapus'])) {
    try {
        $cek = $pdo->prepare("SELECT foto FROM buku WHERE id = ?");
        $cek->execute([$_GET['hapus']]);
        $fotoLama = $cek->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM buku WHERE id = ?");
        $stmt->execute([$_GET['hapus']]);
        hapus_foto_buku($fotoLama ?: null);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal hapus: buku ini masih punya riwayat peminjaman.'];
    }
    header('Location: buku_list.php');
    exit;
}

$buku = $pdo->query("
    SELECT b.*, k.nama_kategori FROM buku b
    JOIN kategori k ON k.id = b.kategori_id
    ORDER BY b.judul ASC
")->fetchAll();
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

$page_title = 'Buku';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Data Buku</h3>
  <?php if ($kategori): ?>
  <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-lg"></i> Tambah Buku</button>
  <?php else: ?>
  <span class="text-muted">Tambah kategori dulu sebelum bisa input buku</span>
  <?php endif; ?>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th></th><th>Kode</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        <?php if (!$buku): ?>
          <tr><td colspan="7" class="text-center text-muted">Belum ada buku</td></tr>
        <?php else: foreach ($buku as $b): ?>
          <tr>
            <td style="width:50px;">
              <?php if ($b['foto']): ?>
                <img src="assets/uploads/buku/<?= htmlspecialchars($b['foto']) ?>" class="rounded" style="width:40px;height:55px;object-fit:cover;">
              <?php else: ?>
                <div class="bg-secondary bg-opacity-10 rounded d-flex align-items-center justify-content-center" style="width:40px;height:55px;">
                  <i class="bi bi-book text-secondary"></i>
                </div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b['kode_buku']) ?></td>
            <td><?= htmlspecialchars($b['judul']) ?></td>
            <td><?= htmlspecialchars($b['penulis']) ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($b['nama_kategori']) ?></span></td>
            <td><?= $b['stok'] ?></td>
            <td class="text-end">
              <a href="buku_detail.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Detail</a>
              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $b['id'] ?>"><i class="bi bi-pencil"></i></button>
              <a href="buku_list.php?hapus=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus buku ini?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>

          <div class="modal fade" id="modalEdit<?= $b['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" class="modal-content" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($b['foto'] ?? '') ?>">
                <div class="modal-header"><h5 class="modal-title">Edit Buku</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                  <div class="mb-2"><label class="form-label">Kode Buku</label><input type="text" name="kode_buku" class="form-control" value="<?= htmlspecialchars($b['kode_buku']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Judul</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($b['judul']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($b['penulis']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control" value="<?= htmlspecialchars($b['penerbit']) ?>" required></div>
                  <div class="mb-2"><label class="form-label">Tahun</label><input type="number" name="tahun" class="form-control" value="<?= $b['tahun'] ?>" min="1900" max="2100" required></div>
                  <div class="mb-2"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" value="<?= $b['stok'] ?>" min="0" required></div>
                  <div class="mb-2"><label class="form-label">Kategori</label>
                    <select name="kategori_id" class="form-select" required>
                      <?php foreach ($kategori as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $k['id']==$b['kategori_id']?'selected':'' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-2"><label class="form-label">Sinopsis</label><textarea name="sinopsis" class="form-control" rows="3"><?= htmlspecialchars($b['sinopsis'] ?? '') ?></textarea></div>
                  <div class="mb-2">
                    <label class="form-label">Foto Sampul</label>
                    <?php if ($b['foto']): ?>
                      <div class="mb-1"><img src="assets/uploads/buku/<?= htmlspecialchars($b['foto']) ?>" style="width:60px;height:85px;object-fit:cover;" class="rounded border"></div>
                    <?php endif; ?>
                    <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp,.avif">
                    <small class="text-muted">Kosongkan kalau tidak ingin ganti foto. Maks 2MB.</small>
                  </div>
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
    <form method="POST" class="modal-content" enctype="multipart/form-data">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-header"><h5 class="modal-title">Tambah Buku</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Kode Buku</label><input type="text" name="kode_buku" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Judul</label><input type="text" name="judul" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Tahun</label><input type="number" name="tahun" class="form-control" value="<?= date('Y') ?>" min="1900" max="2100" required></div>
        <div class="mb-2"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" value="1" min="0" required></div>
        <div class="mb-2"><label class="form-label">Kategori</label>
          <select name="kategori_id" class="form-select" required>
            <?php foreach ($kategori as $k): ?>
              <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Sinopsis</label><textarea name="sinopsis" class="form-control" rows="3" placeholder="Ringkasan cerita/isi buku (opsional)"></textarea></div>
        <div class="mb-2">
          <label class="form-label">Foto Sampul</label>
          <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp,.avif">
          <small class="text-muted">Format JPG/PNG/WEBP/AVIF, maks 2MB (opsional).</small>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-dark">Simpan</button></div>
    </form>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
