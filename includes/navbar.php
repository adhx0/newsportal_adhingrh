<?php
require_once __DIR__ . '/../config/base_url.php';

$halamanAktif = basename($_SERVER['PHP_SELF'] ?? '');
$kataKunci = trim((string)($_GET['search'] ?? ''));
$searchTerbuka = $kataKunci !== '';
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

        <!-- Pencarian -->
        <li class="nav-item navbar-search-item">

          <button
            type="button"
            class="nav-link navbar-search-toggle"
            data-bs-toggle="collapse"
            data-bs-target="#navbarSearch"
            aria-expanded="<?= $searchTerbuka ? 'true' : 'false'; ?>"
            aria-controls="navbarSearch"
            aria-label="Buka pencarian">
            <i class="bi bi-search"></i>
          </button>

          <div
            class="collapse navbar-search-collapse <?= $searchTerbuka ? 'show' : ''; ?>"
            id="navbarSearch">

            <form
              action="<?= BASE_URL ?>/index.php"
              method="GET"
              class="navbar-search-form">

              <input
                type="search"
                name="search"
                class="form-control"
                placeholder="Cari berita..."
                value="<?= htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="100"
                aria-label="Cari berita">

              <button
                type="submit"
                class="btn btn-primary"
                aria-label="Cari">
                <i class="bi bi-search"></i>
              </button>

            </form>
          </div>
        </li>

        <!-- Login -->
        <li class="nav-item">
          <a
            class="nav-link"
            href="<?= BASE_URL ?>/auth/login.php">
            Login
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>