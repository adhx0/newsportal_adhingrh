<?php

require_once __DIR__ . "/config/base_url.php";
require_once __DIR__ . "/config/koneksi.php";

// FUNCTION ESCAPE HTML
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// AMBIL PARAMETER
$id_kategori = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$keyword = trim($_GET['keyword'] ?? '');

// JIKA ID TIDAK ADA, TAMPILKAN SEMUA KATEGORI
if (!$id_kategori || $id_kategori < 1) {

  $stmt_kategori = $koneksi->query(
    "SELECT * FROM kategori ORDER BY id DESC"
  );

  $daftar_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

  include __DIR__ . "/includes/header.php";
  include __DIR__ . "/includes/navbar.php";
?>

  <main class="category-page">

    <!-- HEADER HALAMAN -->
    <div class="category-page-header">
      <span class="category-eyebrow">Jelajahi Berita</span>

      <h1>Semua Kategori</h1>

      <p>
        Temukan berbagai informasi dan berita berdasarkan
        kategori yang kamu minati.
      </p>
    </div>

    <!-- DAFTAR KATEGORI -->
    <div class="category-grid">

      <?php if (!empty($daftar_kategori)) : ?>

        <?php foreach ($daftar_kategori as $row) : ?>

          <div class="category-card">

            <div class="category-card-content">

              <div class="category-card-icon">
                <?= e(strtoupper(substr(trim($row['nama_kategori']), 0, 1))); ?>
              </div>

              <h2>
                <?= e($row['nama_kategori']); ?>
              </h2>

              <a
                href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$row['id']; ?>"
                class="category-card-link">

                Lihat Artikel
                <span>&rarr;</span>

              </a>

            </div>

          </div>

        <?php endforeach; ?>

      <?php else : ?>

        <div class="category-empty">
          <h3>Belum Ada Kategori</h3>
          <p>Belum ada kategori berita yang tersedia saat ini.</p>
        </div>

      <?php endif; ?>

    </div>

  </main>

<?php
  include __DIR__ . "/includes/footer.php";
  exit;
}

// AMBIL DATA KATEGORI
$stmt_kategori = $koneksi->prepare(
  "SELECT * FROM kategori WHERE id = :id LIMIT 1"
);

$stmt_kategori->execute([
  ':id' => $id_kategori
]);

$kategori = $stmt_kategori->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
  http_response_code(404);
  die("Kategori tidak ditemukan.");
}

// PAGINATION
$limit = 6;

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;

$offset = ($page - 1) * $limit;

// HITUNG TOTAL ARTIKEL
$stmt_total = $koneksi->prepare(
  "SELECT COUNT(*)
   FROM artikel
   WHERE kategori_id = :kategori_id
   AND judul LIKE :keyword"
);

$stmt_total->execute([
  ':kategori_id' => $id_kategori,
  ':keyword' => '%' . $keyword . '%'
]);

$total_data = (int) $stmt_total->fetchColumn();

$total_page = (int) ceil($total_data / $limit);

// VALIDASI HALAMAN
if ($total_page > 0 && $page > $total_page) {
  $page = $total_page;
  $offset = ($page - 1) * $limit;
}

// AMBIL DATA ARTIKEL
$stmt_artikel = $koneksi->prepare(
  "SELECT artikel.*,
          kategori.nama_kategori,
          users.nama
   FROM artikel
   LEFT JOIN kategori
      ON artikel.kategori_id = kategori.id
   LEFT JOIN users
      ON artikel.user_id = users.id
   WHERE artikel.kategori_id = :kategori_id
   AND artikel.judul LIKE :keyword
   ORDER BY artikel.id DESC
   LIMIT :limit OFFSET :offset"
);

$stmt_artikel->bindValue(':kategori_id', $id_kategori, PDO::PARAM_INT);
$stmt_artikel->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
$stmt_artikel->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_artikel->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt_artikel->execute();

$daftar_artikel = $stmt_artikel->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/navbar.php";
?>

<main class="category-page">

  <!-- BREADCRUMB -->
  <div class="breadcrumb-news">
    <a href="<?= BASE_URL ?>/index.php">Beranda</a>
    <span>/</span>
    <a href="<?= BASE_URL ?>/kategori.php">Kategori</a>
    <span>/</span>
    <span><?= e($kategori['nama_kategori']); ?></span>
  </div>

  <!-- HEADER KATEGORI -->
  <div class="category-detail-header">

    <div>
      <span class="category-eyebrow">Kategori Berita</span>

      <h1><?= e($kategori['nama_kategori']); ?></h1>

      <p>
        Temukan berita dan informasi terbaru dalam kategori ini.
      </p>
    </div>

    <a href="<?= BASE_URL ?>/kategori.php" class="category-back-link">
      &larr; Semua Kategori
    </a>

  </div>

  <!-- PENCARIAN ARTIKEL -->
  <form method="GET" class="category-search">

    <input
      type="hidden"
      name="id"
      value="<?= (int)$id_kategori; ?>">

    <input
      type="text"
      name="keyword"
      class="category-search-input"
      placeholder="Cari berita dalam kategori ini..."
      value="<?= e($keyword); ?>">

    <button type="submit" class="category-search-button">
      Cari Artikel
    </button>

    <?php if ($keyword !== '') : ?>
      <a
        href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$id_kategori; ?>"
        class="category-reset-search">
        Reset
      </a>
    <?php endif; ?>

  </form>

  <!-- INFORMASI HASIL -->
  <div class="category-result-info">
    <p>
      Menampilkan
      <strong><?= $total_data; ?></strong>
      artikel
      <?php if ($keyword !== '') : ?>
        untuk pencarian
        <strong>"<?= e($keyword); ?>"</strong>
      <?php endif; ?>
    </p>
  </div>

  <!-- DAFTAR ARTIKEL -->
  <div class="news-grid">

    <?php if (!empty($daftar_artikel)) : ?>

      <?php foreach ($daftar_artikel as $row) : ?>

        <article class="news-card">

          <a
            href="<?= BASE_URL ?>/detail.php?id=<?= (int)$row['id']; ?>"
            class="news-image-link">

            <?php if (!empty($row['thumbnail'])) : ?>

              <img
                src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($row['thumbnail'])); ?>"
                class="news-image"
                alt="<?= e($row['judul']); ?>">

            <?php else : ?>

              <div class="news-image-placeholder">
                Tidak ada gambar
              </div>

            <?php endif; ?>

          </a>

          <div class="news-card-body">

            <span class="category-label">
              <?= e($row['nama_kategori'] ?? 'Umum'); ?>
            </span>

            <h2 class="news-title">
              <a href="<?= BASE_URL ?>/detail.php?id=<?= (int)$row['id']; ?>">
                <?= e($row['judul']); ?>
              </a>
            </h2>

            <p class="news-description">
              <?= e(mb_substr(
                strip_tags($row['isi_berita']),
                0,
                120
              )); ?>...
            </p>

            <div class="news-meta">
              <span>
                <?= e($row['nama'] ?? '-'); ?>
              </span>

              <span>
                <?= date('d M Y', strtotime($row['tanggal'])); ?>
              </span>
            </div>

            <a
              href="<?= BASE_URL ?>/detail.php?id=<?= (int)$row['id']; ?>"
              class="btn-read">
              Baca Selengkapnya &rarr;
            </a>

          </div>

        </article>

      <?php endforeach; ?>

    <?php else : ?>

      <div class="category-empty">
        <h3>Artikel Tidak Ditemukan</h3>

        <p>
          Maaf, belum ada artikel yang sesuai dengan pencarian kamu.
        </p>

        <?php if ($keyword !== '') : ?>
          <a href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$id_kategori; ?>">
            Lihat semua artikel
          </a>
        <?php endif; ?>
      </div>

    <?php endif; ?>

  </div>

  <!-- PAGINATION -->
  <?php if ($total_page > 1) : ?>

    <nav class="category-pagination" aria-label="Navigasi halaman">

      <ul class="pagination">

        <?php if ($page > 1) : ?>

          <li class="page-item">
            <a
              class="page-link"
              href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$id_kategori; ?>&keyword=<?= urlencode($keyword); ?>&page=<?= $page - 1; ?>">
              &laquo; Sebelumnya
            </a>
          </li>

        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_page; $i++) : ?>

          <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">

            <a
              class="page-link"
              href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$id_kategori; ?>&keyword=<?= urlencode($keyword); ?>&page=<?= $i; ?>">
              <?= $i; ?>
            </a>

          </li>

        <?php endfor; ?>

        <?php if ($page < $total_page) : ?>

          <li class="page-item">
            <a
              class="page-link"
              href="<?= BASE_URL ?>/kategori.php?id=<?= (int)$id_kategori; ?>&keyword=<?= urlencode($keyword); ?>&page=<?= $page + 1; ?>">
              Selanjutnya &raquo;
            </a>
          </li>

        <?php endif; ?>

      </ul>

    </nav>

  <?php endif; ?>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>