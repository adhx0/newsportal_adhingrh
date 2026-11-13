<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$role = $_SESSION['role'];
$userId = (int) ($_SESSION['id'] ?? 0);

// Hitung total artikel berdasarkan role
if ($role === 'user') {

  $queryArtikel = $koneksi->prepare(
    "SELECT COUNT(*) FROM artikel WHERE user_id = :user_id"
  );

  $queryArtikel->execute([
    ':user_id' => $userId
  ]);

  $totalArtikel = $queryArtikel->fetchColumn();
} else {

  $totalArtikel = $koneksi->query(
    "SELECT COUNT(*) FROM artikel"
  )->fetchColumn();
}

// Hitung total kategori
// User hanya melihat statistik, tidak mengelola kategori
$totalKategori = $koneksi->query(
  "SELECT COUNT(*) FROM kategori"
)->fetchColumn();

include "header.php";
include "sidebar.php";
?>

<div class="admin-dashboard">

  <!-- HEADER DASHBOARD -->
  <div class="dashboard-header">
    <div>
      <span class="dashboard-eyebrow">ADMINISTRASI</span>

      <h1>
        <?= $role === 'user' ? 'Dashboard User' : 'Dashboard Admin'; ?>
      </h1>

      <p>
        <?= $role === 'user'
          ? 'Selamat datang di panel pengelolaan artikel NewsPortal.'
          : 'Selamat datang di panel pengelolaan NewsPortal.'; ?>
      </p>
    </div>

    <div class="dashboard-date">
      <i class="bi bi-calendar3"></i>
      <?= date('d F Y'); ?>
    </div>
  </div>

  <!-- STATISTIK -->
  <div class="dashboard-section-heading">
    <h2>Ringkasan Statistik</h2>

    <p>
      <?= $role === 'user'
        ? 'Informasi artikel yang kamu buat.'
        : 'Informasi data artikel dan kategori portal berita.'; ?>
    </p>
  </div>

  <div class="dashboard-stats dashboard-stats-admin">

    <!-- TOTAL ARTIKEL -->
    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-blue">
        <i class="bi bi-newspaper"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalArtikel; ?></h3>

        <p>
          <?= $role === 'user' ? 'Artikel Saya' : 'Total Artikel'; ?>
        </p>
      </div>
    </div>

    <!-- TOTAL KATEGORI -->
    <div class="dashboard-stat-card">
      <div class="stat-icon stat-icon-purple">
        <i class="bi bi-grid"></i>
      </div>

      <div class="stat-card-info">
        <h3><?= (int) $totalKategori; ?></h3>
        <p>Total Kategori</p>
      </div>
    </div>

  </div>

  <!-- INFORMASI DASHBOARD -->
  <div class="dashboard-welcome">
    <div class="dashboard-welcome-icon">
      <i class="bi bi-info-circle"></i>
    </div>

    <div>
      <h3>Informasi Dashboard</h3>

      <p>
        <?= $role === 'user'
          ? 'Kamu dapat membuat, melihat, mengedit, dan menghapus artikel milikmu sendiri.'
          : 'Kelola artikel dan kategori untuk memastikan informasi yang ditampilkan di NewsPortal selalu terorganisir dan diperbarui.'; ?>
      </p>
    </div>
  </div>

</div>

<?php include "footer.php"; ?>