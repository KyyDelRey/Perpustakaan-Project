<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Perpustakaan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="icon" href="assets/icons/logo-buku.png" type="image/x-icon">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><i class="bi bi-book-half"></i> Perpustakaan Masa Kini</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="kategori_list.php"><i class="bi bi-tags"></i> Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="buku_list.php"><i class="bi bi-journal-bookmark"></i> Buku</a></li>
        <li class="nav-item"><a class="nav-link" href="anggota_list.php"><i class="bi bi-people"></i> Anggota</a></li>
        <li class="nav-item"><a class="nav-link" href="peminjaman_list.php"><i class="bi bi-arrow-left-right"></i> Peminjaman</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><span class="nav-link text-light-50"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username'] ?? '') ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
