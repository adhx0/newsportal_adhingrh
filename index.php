<?php

require_once __DIR__ . "/config/base_url.php";
require_once __DIR__ . "/config/koneksi.php";

/* FUNGSI ESCAPE OUTPUT */
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

/* FUNGSI WAKTU */
function waktu_lalu($datetime)
{
  if (empty($datetime)) {
    return '-';
  }

  $timestamp = strtotime($datetime);

  if ($timestamp === false) {
    return '-';
  }

  $selisih = time() - $timestamp;

  if ($selisih < 0) {
    return date('d M Y', $timestamp);
  } elseif ($selisih < 60) {
    return $selisih . " detik yang lalu";
  } elseif ($selisih < 3600) {
    return floor($selisih / 60) . " menit yang lalu";
  } elseif ($selisih < 86400) {
    return floor($selisih / 3600) . " jam yang lalu";
  } elseif ($selisih < 604800) {
    return floor($selisih / 86400) . " hari yang lalu";
  } elseif ($selisih < 2592000) {
    return floor($selisih / 604800) . " minggu yang lalu";
  } elseif ($selisih < 31536000) {
    return floor($selisih / 2592000) . " bulan yang lalu";
  }

  return floor($selisih / 31536000) . " tahun yang lalu";
}

/* HERO NEWS */
$query_hero = $koneksi->query(
  "SELECT artikel.*,
            kategori.nama_kategori
     FROM artikel
     LEFT JOIN kategori
        ON artikel.kategori_id = kategori.id
     ORDER BY artikel.id DESC
     LIMIT 1"
);

$hero = $query_hero->fetch(PDO::FETCH_ASSOC);

/* BERITA TERBARU */
if ($hero) {
  $query_terbaru = $koneksi->prepare(
    "SELECT artikel.*,
                kategori.nama_kategori,
                users.nama
         FROM artikel
         LEFT JOIN kategori
            ON artikel.kategori_id = kategori.id
         LEFT JOIN users
            ON artikel.user_id = users.id
         WHERE artikel.id <> :hero_id
         ORDER BY artikel.id DESC
         LIMIT 6"
  );

  $query_terbaru->execute([
    ':hero_id' => $hero['id']
  ]);
} else {
  $query_terbaru = $koneksi->query(
    "SELECT artikel.*,
                kategori.nama_kategori,
                users.nama
         FROM artikel
         LEFT JOIN kategori
            ON artikel.kategori_id = kategori.id
         LEFT JOIN users
            ON artikel.user_id = users.id
         ORDER BY artikel.id DESC
         LIMIT 6"
  );
}

/* KATEGORI */
$query_kategori = $koneksi->query(
  "SELECT *
     FROM kategori
     ORDER BY nama_kategori ASC"
);

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/navbar.php";

?>

<main class="news-container container py-4 py-md-5">

  <!-- HERO NEWS -->
  <?php if ($hero) : ?>

    <section class="mb-5">

      <div class="mb-3">
        <h2 class="section-title mt-1 mb-1">
          Berita Utama
        </h2>

        <p class="section-subtitle mb-0">
          Informasi pilihan terbaru dari News Portal.
        </p>
      </div>

      <div class="hero-card shadow-sm">
        <div class="row g-0">

          <div class="col-lg-6">
            <img
              src="<?= BASE_URL ?>/uploads/<?= e(basename($hero['thumbnail'] ?? '')); ?>"
              class="hero-image"
              alt="<?= e($hero['judul']); ?>">
          </div>

          <div class="col-lg-6 d-flex align-items-center">

            <div class="hero-content">

              <div class="mb-3">
                <span class="category-label">
                  <?= e($hero['nama_kategori'] ?? 'Umum'); ?>
                </span>

                <span class="news-meta ms-2">
                  <?= e(waktu_lalu($hero['tanggal'])); ?>
                </span>
              </div>

              <h1 class="hero-title mb-3">
                <?= e($hero['judul']); ?>
              </h1>

              <p class="news-description mb-4">
                <?= e(mb_substr(strip_tags($hero['isi_berita']), 0, 220)); ?>...
              </p>

              <a
                href="detail.php?id=<?= (int)$hero['id']; ?>"
                class="btn btn-primary px-4 py-2">
                Baca Selengkapnya
              </a>

            </div>

          </div>

        </div>
      </div>

    </section>

  <?php endif; ?>


  <!-- BERITA TERBARU -->
  <section class="mb-5">

    <div class="d-flex justify-content-between align-items-end mb-4">

      <div>
        <h2 class="section-title mb-1">
          Berita Terbaru
        </h2>

        <p class="section-subtitle mb-0">
          Ikuti informasi dan berita terkini.
        </p>
      </div>

      <a href="kategori.php" class="btn-read">
        Semua Berita →
      </a>

    </div>

    <div class="row g-4">

      <?php
      $jumlahTerbaru = 0;

      while ($row = $query_terbaru->fetch(PDO::FETCH_ASSOC)) :
        $jumlahTerbaru++;
      ?>

        <div class="col-md-6 col-lg-4">

          <article class="news-card h-100">

            <img
              src="<?= BASE_URL ?>/uploads/<?= e(basename($row['thumbnail'] ?? '')); ?>"
              class="news-image"
              alt="<?= e($row['judul']); ?>">

            <div class="card-body d-flex flex-column">

              <div class="mb-3">
                <span class="category-label">
                  <?= e($row['nama_kategori'] ?? 'Umum'); ?>
                </span>
              </div>

              <h3 class="news-title mb-2">
                <?= e($row['judul']); ?>
              </h3>

              <div class="news-meta mb-3">
                <?= e($row['nama'] ?? '-'); ?>
                <span class="mx-1">·</span>
                <?= e(waktu_lalu($row['tanggal'])); ?>
              </div>

              <p class="news-description mb-4">
                <?= e(mb_substr(strip_tags($row['isi_berita']), 0, 110)); ?>...
              </p>

              <div class="mt-auto">
                <a
                  href="detail.php?id=<?= (int)$row['id']; ?>"
                  class="btn-read">
                  Baca Selengkapnya →
                </a>
              </div>

            </div>

          </article>

        </div>

      <?php endwhile; ?>

      <?php if ($jumlahTerbaru === 0 && !$hero) : ?>

        <div class="col-12">
          <div class="empty-state">
            <h5>Belum Ada Berita</h5>

            <p class="mb-0">
              Artikel berita belum tersedia saat ini.
            </p>
          </div>
        </div>

      <?php endif; ?>

    </div>

  </section>


  <!-- BERITA BERDASARKAN KATEGORI -->
  <?php while ($kat = $query_kategori->fetch(PDO::FETCH_ASSOC)) : ?>

    <?php

    $id_kategori = $kat['id'];

    $stmt_artikel_kategori = $koneksi->prepare(
      "SELECT artikel.*,
                    users.nama
             FROM artikel
             LEFT JOIN users
                ON artikel.user_id = users.id
             WHERE artikel.kategori_id = :id_kategori
             ORDER BY artikel.id DESC
             LIMIT 3"
    );

    $stmt_artikel_kategori->execute([
      ':id_kategori' => $id_kategori
    ]);

    $artikelKategori = $stmt_artikel_kategori->fetchAll(
      PDO::FETCH_ASSOC
    );

    ?>

    <?php if (count($artikelKategori) > 0) : ?>

      <section class="category-section mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

          <div>
            <h2 class="section-title mb-1">
              <?= e($kat['nama_kategori']); ?>
            </h2>

            <p class="section-subtitle mb-0">
              Berita pilihan kategori ini.
            </p>
          </div>

          <a
            href="kategori.php?id=<?= (int)$kat['id']; ?>"
            class="btn btn-outline-primary btn-sm">
            Lihat Semua →
          </a>

        </div>

        <div class="row g-4">

          <?php foreach ($artikelKategori as $a) : ?>

            <div class="col-md-6 col-lg-4">

              <article class="news-card h-100">

                <img
                  src="<?= BASE_URL ?>/uploads/<?= e(basename($a['thumbnail'] ?? '')); ?>"
                  class="news-image"
                  alt="<?= e($a['judul']); ?>">

                <div class="card-body d-flex flex-column">

                  <h3 class="news-title mb-2">
                    <?= e($a['judul']); ?>
                  </h3>

                  <div class="news-meta mb-3">
                    <?= e($a['nama'] ?? '-'); ?>
                    <span class="mx-1">·</span>
                    <?= e(waktu_lalu($a['tanggal'])); ?>
                  </div>

                  <p class="news-description mb-4">
                    <?= e(mb_substr(strip_tags($a['isi_berita']), 0, 100)); ?>...
                  </p>

                  <div class="mt-auto">
                    <a
                      href="detail.php?id=<?= (int)$a['id']; ?>"
                      class="btn-read">
                      Baca Selengkapnya →
                    </a>
                  </div>

                </div>

              </article>

            </div>

          <?php endforeach; ?>

        </div>

      </section>

    <?php endif; ?>

  <?php endwhile; ?>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>