<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

if ($_SESSION['role'] !== 'super_admin') {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$totalArtikel = $koneksi->query(
  "SELECT COUNT(*) FROM artikel"
)->fetchColumn();

$totalKategori = $koneksi->query(
  "SELECT COUNT(*) FROM kategori"
)->fetchColumn();

$totalKomentar = $koneksi->query(
  "SELECT COUNT(*) FROM komentar"
)->fetchColumn();

$totalUser = $koneksi->query(
  "SELECT COUNT(*) FROM users"
)->fetchColumn();

include "header.php";
include "sidebar.php";
?>

<div class="admin-dashboard">

  <div class="dashboard-header">
    <div>
      <span class="dashboard-eyebrow">ADMINISTRASI</span>
      <h1>Dashboard</h1>
      <p>Selamat datang di panel administrasi NewsPortal.</p>
    </div>

    <div class="dashboard-date">
      <i class="bi bi-calendar3"></i>
      <?= date('d F Y'); ?>
    </div>
  </div>

  <div class="dashboard-section-heading">
    <h2>Ringkasan Statistik</h2>
    <p>Informasi keseluruhan data portal berita.</p>
  </div>

  <div class="dashboard-stats">

    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-blue">
        <i class="bi bi-newspaper"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalArtikel; ?></h3>
        <p>Total Artikel</p>
      </div>
    </div>

    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-purple">
        <i class="bi bi-grid"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalKategori; ?></h3>
        <p>Total Kategori</p>
      </div>
    </div>

    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-orange">
        <i class="bi bi-chat-left-text"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalKomentar; ?></h3>
        <p>Total Komentar</p>
      </div>
    </div>

    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-green">
        <i class="bi bi-people"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalUser; ?></h3>
        <p>Total Pengguna</p>
      </div>
    </div>

  </div>

  <div class="dashboard-welcome">
    <div class="dashboard-welcome-icon">
      <i class="bi bi-info-circle"></i>
    </div>

    <div>
      <h3>Informasi Dashboard</h3>
      <p>
        Gunakan menu navigasi untuk mengelola artikel,
        kategori, komentar, dan pengguna NewsPortal.
      </p>
    </div>
  </div>

</div>

<?php include "footer.php"; ?>