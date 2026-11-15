<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

// VALIDASI SESSION
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

$role = $_SESSION['role'];
$userId = (int) ($_SESSION['id'] ?? 0);

// VALIDASI ROLE
if (!in_array($role, ['super_admin', 'admin', 'user'], true)) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

// HITUNG TOTAL ARTIKEL
if ($role === 'user') {

  // User hanya melihat artikel miliknya sendiri
  $queryArtikel = $koneksi->prepare(
    "SELECT COUNT(*) FROM artikel WHERE user_id = :user_id"
  );

  $queryArtikel->execute([
    ':user_id' => $userId
  ]);

  $totalArtikel = $queryArtikel->fetchColumn();
} else {

  // Admin dan super admin melihat seluruh artikel
  $totalArtikel = $koneksi->query(
    "SELECT COUNT(*) FROM artikel"
  )->fetchColumn();
}

// HITUNG TOTAL KATEGORI
$totalKategori = $koneksi->query(
  "SELECT COUNT(*) FROM kategori"
)->fetchColumn();

// STATISTIK KHUSUS SUPER ADMIN
if ($role === 'super_admin') {

  $totalKomentar = $koneksi->query(
    "SELECT COUNT(*) FROM komentar"
  )->fetchColumn();

  $totalUser = $koneksi->query(
    "SELECT COUNT(*) FROM users"
  )->fetchColumn();
}

// TAMPILAN
include "header.php";
include "sidebar.php";
?>

<div class="admin-dashboard">

  <!-- HEADER DASHBOARD -->
  <div class="dashboard-header">

    <div>
      <span class="dashboard-eyebrow">ADMINISTRASI</span>

      <h1>
        <?php
        if ($role === 'super_admin') {
          echo 'Dashboard Super Admin';
        } elseif ($role === 'admin') {
          echo 'Dashboard Admin';
        } else {
          echo 'Dashboard User';
        }
        ?>
      </h1>

      <p>
        <?php
        if ($role === 'super_admin') {
          echo 'Selamat datang di panel administrasi NewsPortal.';
        } elseif ($role === 'admin') {
          echo 'Selamat datang di panel pengelolaan NewsPortal.';
        } else {
          echo 'Selamat datang di panel pengelolaan artikel NewsPortal.';
        }
        ?>
      </p>
    </div>

    <?php include __DIR__ . "/calendar.php"; ?>

  </div>

  <!-- JUDUL STATISTIK -->
  <div class="dashboard-section-heading">

    <h2>Ringkasan Statistik</h2>

    <p>
      <?php
      if ($role === 'super_admin') {
        echo 'Informasi keseluruhan data portal berita.';
      } elseif ($role === 'admin') {
        echo 'Informasi data artikel dan kategori portal berita.';
      } else {
        echo 'Informasi artikel yang kamu buat.';
      }
      ?>
    </p>

  </div>

  <!-- KARTU STATISTIK -->
  <div class="dashboard-stats <?= $role !== 'super_admin' ? 'dashboard-stats-admin' : ''; ?>">

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

    <?php if ($role === 'super_admin'): ?>

      <!-- TOTAL KOMENTAR -->
      <div class="dashboard-stat-card">

        <div class="stat-icon stat-icon-orange">
          <i class="bi bi-chat-left-text"></i>
        </div>

        <div class="stat-card-info">
          <h3><?= (int) $totalKomentar; ?></h3>
          <p>Total Komentar</p>
        </div>

      </div>

      <!-- TOTAL PENGGUNA -->
      <div class="dashboard-stat-card">

        <div class="stat-icon stat-icon-green">
          <i class="bi bi-people"></i>
        </div>

        <div class="stat-card-info">
          <h3><?= (int) $totalUser; ?></h3>
          <p>Total Pengguna</p>
        </div>

      </div>

    <?php endif; ?>

  </div>

  <!-- INFORMASI DASHBOARD -->
  <div class="dashboard-welcome">

    <div class="dashboard-welcome-icon">
      <i class="bi bi-info-circle"></i>
    </div>

    <div>

      <h3>Informasi Dashboard</h3>

      <p>
        <?php
        if ($role === 'super_admin') {
          echo 'Gunakan menu navigasi untuk mengelola artikel, kategori, komentar, dan pengguna NewsPortal.';
        } elseif ($role === 'admin') {
          echo 'Kelola artikel dan kategori untuk memastikan informasi yang ditampilkan di NewsPortal selalu terorganisir dan diperbarui.';
        } else {
          echo 'Kamu dapat membuat, melihat, mengedit, dan menghapus artikel milikmu sendiri.';
        }
        ?>
      </p>

    </div>

  </div>

</div>

<?php include "footer.php"; ?>