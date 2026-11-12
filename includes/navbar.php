<?php
$halamanAktif = basename($_SERVER['PHP_SELF'] ?? '');
?>

<nav class="navbar navbar-expand-lg navbar-light main-navbar sticky-top">
  <div class="container">

    <a class="navbar-brand" href="/newsportal/index.php">
      <span class="brand-icon">N</span>
      <span>NEWS PORTAL</span>
    </a>

    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarNav"
      aria-controls="navbarNav"
      aria-expanded="false"
      aria-label="Toggle navigation">

      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">

        <li class="nav-item">
          <a
            class="nav-link <?= $halamanAktif === 'index.php' ? 'active' : ''; ?>"
            href="/newsportal/index.php">
            Home
          </a>
        </li>

        <li class="nav-item">
          <a
            class="nav-link <?= $halamanAktif === 'kategori.php' ? 'active' : ''; ?>"
            href="/newsportal/kategori.php">
            Kategori
          </a>
        </li>

        <li class="nav-item">
          <a
            class="nav-link <?= $halamanAktif === 'tentang.php' ? 'active' : ''; ?>"
            href="/newsportal/tentang.php">
            Tentang
          </a>
        </li>

        <li class="nav-item ms-lg-3">
          <a href="/newsportal/auth/login.php" class="navbar-login">
            Login
          </a>
        </li>

      </ul>
    </div>

  </div>
</nav>