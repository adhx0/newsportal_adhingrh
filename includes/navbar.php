<?php
require_once __DIR__ . '/../config/base_url.php';

$halamanAktif = basename($_SERVER['PHP_SELF'] ?? '');
?>

<nav class="navbar navbar-expand-lg navbar-light main-navbar sticky-top">
  <div class="container">

    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
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
            href="<?= BASE_URL ?>/index.php">
            Home
          </a>
        </li>

        <li class="nav-item">
          <a
            class="nav-link <?= $halamanAktif === 'kategori.php' ? 'active' : ''; ?>"
            href="<?= BASE_URL ?>/kategori.php">
            Kategori
          </a>
        </li>

        <li class="nav-item">
          <a
            class="nav-link <?= $halamanAktif === 'tentang.php' ? 'active' : ''; ?>"
            href="<?= BASE_URL ?>/tentang.php">
            Tentang
          </a>
        </li>

        <li class="nav-item ms-lg-3">
          <a href="<?= BASE_URL ?>/auth/login.php" class="navbar-login">
            Login
          </a>
        </li>

      </ul>
    </div>

  </div>
</nav>