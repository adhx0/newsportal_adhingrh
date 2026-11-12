<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['PHP_SELF'];

$role = $_SESSION['role'] ?? '';

$isSuperAdmin = $role === 'super_admin';
$isAdmin = $role === 'admin';
$isUser = $role === 'user';

// URL Dashboard
$dashboardUrl = $isSuperAdmin
  ? '/newsportal/admin/dashboard.php'
  : '/newsportal/admin/index.php';

// Status menu Dashboard
$dashboardActive = (
  $currentPage === 'dashboard.php' ||
  ($currentPage === 'index.php' && strpos($currentPath, '/admin/') !== false
    && strpos($currentPath, '/artikel/') === false
    && strpos($currentPath, '/kategori/') === false)
);

// Label role
if ($isSuperAdmin) {
  $roleLabel = 'Super Admin';
} elseif ($isAdmin) {
  $roleLabel = 'Admin';
} elseif ($isUser) {
  $roleLabel = 'User';
} else {
  $roleLabel = 'Tidak Diketahui';
}
?>

<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">

    <!-- BRAND -->
    <div class="sidebar-brand">
      <div class="sidebar-brand-icon">
        <i class="bi bi-newspaper"></i>
      </div>

      <div class="sidebar-brand-text">
        <h3>NEWS<span>PORTAL</span></h3>
        <small>Administration Panel</small>
      </div>
    </div>

    <!-- NAVIGATION -->
    <div class="sidebar-menu">

      <span class="sidebar-label">MENU UTAMA</span>

      <ul class="nav flex-column">

        <!-- DASHBOARD -->
        <li class="nav-item">
          <a href="<?= $dashboardUrl; ?>"
            class="sidebar-link <?= $dashboardActive ? 'active' : ''; ?>">

            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <!-- KELOLA USER -->
        <?php if ($isSuperAdmin) : ?>
          <li class="nav-item">
            <a href="/newsportal/admin/user.php"
              class="sidebar-link <?= $currentPage === 'user.php' ? 'active' : ''; ?>">

              <i class="bi bi-people"></i>
              <span>Kelola User</span>
            </a>
          </li>
        <?php endif; ?>

        <!-- KATEGORI -->
        <?php if ($isSuperAdmin || $isAdmin) : ?>
          <li class="nav-item">
            <a href="/newsportal/admin/kategori/index.php"
              class="sidebar-link <?= strpos($currentPath, '/kategori/') !== false ? 'active' : ''; ?>">

              <i class="bi bi-folder"></i>
              <span>Kategori</span>
            </a>
          </li>
        <?php endif; ?>

        <!-- ARTIKEL -->
        <li class="nav-item">
          <a href="/newsportal/admin/artikel/index.php"
            class="sidebar-link <?= strpos($currentPath, '/artikel/') !== false ? 'active' : ''; ?>">

            <i class="bi bi-file-earmark-text"></i>
            <span>Artikel</span>
          </a>
        </li>

      </ul>
    </div>

    <!-- SIDEBAR FOOTER -->
    <div class="sidebar-footer">

      <div class="sidebar-user">
        <div class="sidebar-user-icon">
          <i class="bi bi-person-circle"></i>
        </div>

        <div class="sidebar-user-info">
          <strong>
            <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
          </strong>

          <small>
            <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?>
          </small>
        </div>
      </div>

      <a href="/newsportal/admin/logout.php" class="sidebar-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </a>

    </div>

  </aside>

  <!-- MAIN CONTENT -->
  <main class="admin-content">