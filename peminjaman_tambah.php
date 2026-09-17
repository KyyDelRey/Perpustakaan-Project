<?php
require 'config/koneksi.php';
require 'includes/auth_check.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota_id = $_POST['anggota_id'] ?? '';
    $buku_ids   = $_POST['buku_ids'] ?? [];
    $lama_hari  = (int)($_POST['lama_hari'] ?? 7);

    if (!$anggota_id || !$buku_ids) {
        $error = 'Pilih anggota dan minimal 1 buku.';
    } else {
        try {
            $pdo->beginTransaction();

            // cek stok semua buku yang dipilih
            foreach ($buku_ids as $buku_id) {
                $stok = $pdo->prepare("SELECT stok FROM buku WHERE id = ? FOR UPDATE");
                $stok->execute([$buku_id]);
                $row = $stok->fetch();
                if (!$row || $row['stok'] < 1) {
                    throw new Exception('Stok salah satu buku sudah habis.');
                }
            }

            $tgl_pinjam = date('Y-m-d');
            $tgl_tempo  = date('Y-m-d', strtotime("+$lama_hari days"));

            $stmt = $pdo->prepare("INSERT INTO peminjaman (anggota_id, tanggal_pinjam, tanggal_jatuh_tempo, status, denda) VALUES (?,?,?,'dipinjam',0)");
            $stmt->execute([$anggota_id, $tgl_pinjam, $tgl_tempo]);
            $peminjaman_id = $pdo->lastInsertId();

            $insertDetail = $pdo->prepare("INSERT INTO detail_peminjaman (peminjaman_id, buku_id) VALUES (?, ?)");
            $updateStok   = $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?");

            foreach ($buku_ids as $buku_id) {
                $insertDetail->execute([$peminjaman_id, $buku_id]);
                $updateStok->execute([$buku_id]);
            }

            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Peminjaman berhasil dicatat.'];
            header('Location: peminjaman_list.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$anggota = $pdo->query("SELECT * FROM anggota ORDER BY nama ASC")->fetchAll();
$buku    = $pdo->query("SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC")->fetchAll();

$page_title = 'Pinjam Buku';
require 'includes/header.php';
?>

<h3 class="mb-3">Form Peminjaman Buku</h3>

<div class="card p-4" style="max-width:700px;">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!$anggota): ?>
    <p class="text-muted">Belum ada data anggota. Tambah anggota dulu di menu Anggota.</p>
  <?php elseif (!$buku): ?>
    <p class="text-muted">Tidak ada buku dengan stok tersedia saat ini.</p>
  <?php else: ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Anggota</label>
      <select name="anggota_id" class="form-select" required>
        <option value="">-- Pilih Anggota --</option>
        <?php foreach ($anggota as $a): ?>
          <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama']) ?> (<?= htmlspecialchars($a['kode_anggota']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Lama Peminjaman</label>
      <select name="lama_hari" class="form-select">
        <option value="7">7 hari</option>
        <option value="14">14 hari</option>
        <option value="30">30 hari</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Pilih Buku (bisa lebih dari 1)</label>
      <div class="border rounded p-2" style="max-height:250px; overflow-y:auto;">
        <?php foreach ($buku as $b): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="buku_ids[]" value="<?= $b['id'] ?>" id="buku<?= $b['id'] ?>">
            <label class="form-check-label" for="buku<?= $b['id'] ?>">
              <?= htmlspecialchars($b['judul']) ?> <small class="text-muted">(stok: <?= $b['stok'] ?>)</small>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-dark">Simpan Peminjaman</button>
    <a href="peminjaman_list.php" class="btn btn-outline-secondary">Batal</a>
  </form>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
