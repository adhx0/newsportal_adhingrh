<?php

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/koneksi.php';

const BATAS_PENCARIAN = 30;

// Kolom kartu berita. isi_berita dipotong di SQL supaya artikel utuh tidak ikut ditarik.
const KOLOM_KARTU = "a.id, a.judul, a.thumbnail, a.tanggal, a.kategori_id,
                     SUBSTRING(a.isi_berita, 1, 1000) AS isi_berita,
                     k.nama_kategori, u.nama";

const JOIN_KARTU = "FROM artikel a
                    LEFT JOIN kategori k ON a.kategori_id = k.id
                    LEFT JOIN users u ON a.user_id = u.id";

$kataKunci = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
$modePencarian = $kataKunci !== '';

$hasilPencarian = [];
$hero = null;
$beritaTerbaru = [];
$sectionKategori = [];

if ($modePencarian) {

  // Hasil pencarian (dibatasi agar halaman tetap ringan).
  $query = $koneksi->prepare(
    "SELECT " . KOLOM_KARTU . " " . JOIN_KARTU . "
     WHERE a.judul LIKE :judul OR a.isi_berita LIKE :isi
     ORDER BY a.id DESC
     LIMIT " . BATAS_PENCARIAN
  );

  $pola = polaLike($kataKunci);
  $query->execute([':judul' => $pola, ':isi' => $pola]);
  $hasilPencarian = $query->fetchAll();
} else {

  // Berita utama + 6 berita terbaru dalam satu query.
  $beritaTerbaru = $koneksi->query(
    "SELECT " . KOLOM_KARTU . " " . JOIN_KARTU . "
     ORDER BY a.id DESC
     LIMIT 7"
  )->fetchAll();

  $hero = array_shift($beritaTerbaru);

  // 3 berita terbaru per kategori dalam satu query (tanpa query berulang per kategori).
  // Membutuhkan MySQL 8+ / MariaDB 10.2+ (window function).
  $baris = $koneksi->query(
    "SELECT " . KOLOM_KARTU . "
     FROM (
       SELECT id,
              ROW_NUMBER() OVER (PARTITION BY kategori_id ORDER BY id DESC) AS urutan
       FROM artikel
       WHERE kategori_id IS NOT NULL
     ) r
     JOIN artikel a ON a.id = r.id
     JOIN kategori k ON a.kategori_id = k.id
     LEFT JOIN users u ON a.user_id = u.id
     WHERE r.urutan <= 3
     ORDER BY k.nama_kategori, k.id, a.id DESC"
  );

  foreach ($baris as $row) {
    $sectionKategori[$row['kategori_id']]['nama'] = $row['nama_kategori'];
    $sectionKategori[$row['kategori_id']]['artikel'][] = $row;
  }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="news-container container py-4 py-md-5">

  <?php if ($modePencarian): ?>

    <!-- HASIL PENCARIAN -->
    <section class="mb-5">

      <div class="mb-4">
        <h2 class="section-title mb-1">Hasil Pencarian</h2>

        <p class="section-subtitle mb-0">
          Hasil pencarian untuk <strong>"<?= e($kataKunci) ?>"</strong>
        </p>

        <p class="text-secondary mt-2 mb-0">
          <?= count($hasilPencarian) >= BATAS_PENCARIAN
            ? 'Menampilkan ' . BATAS_PENCARIAN . ' berita terbaru yang cocok.'
            : 'Ditemukan ' . count($hasilPencarian) . ' berita.' ?>
        </p>
      </div>

      <?php if ($hasilPencarian): ?>

        <div class="row g-4">
          <?php foreach ($hasilPencarian as $row): ?>
            <?php renderNewsCard($row); ?>
          <?php endforeach; ?>
        </div>

      <?php else: ?>

        <div class="empty-state">
          <i class="bi bi-search"></i>
          <h5>Berita Tidak Ditemukan</h5>

          <p class="mb-3">
            Maaf, tidak ada berita yang cocok dengan kata kunci "<?= e($kataKunci) ?>".
          </p>

          <p class="text-secondary mb-3">
            Coba gunakan kata kunci lain atau periksa kembali ejaan pencarian.
          </p>

          <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Kembali ke Home</a>
        </div>

      <?php endif; ?>

    </section>

  <?php else: ?>

    <!-- BERITA UTAMA -->
    <?php if ($hero): ?>
      <section class="mb-5">

        <div class="mb-3">
          <h2 class="section-title mt-1 mb-1">Berita Utama</h2>
          <p class="section-subtitle mb-0">Informasi pilihan terbaru dari News Portal.</p>
        </div>

        <div class="hero-card shadow-sm">
          <div class="row g-0">

            <div class="col-lg-6">
              <img
                src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($hero['thumbnail'] ?? '')) ?>"
                class="hero-image"
                alt="<?= e($hero['judul']) ?>"
                fetchpriority="high"
                decoding="async">
            </div>

            <div class="col-lg-6 d-flex align-items-center">
              <div class="hero-content">

                <div class="mb-3">
                  <span class="category-label"><?= e($hero['nama_kategori'] ?? 'Umum') ?></span>
                  <span class="news-meta ms-2"><?= e(waktu_lalu($hero['tanggal'])) ?></span>
                </div>

                <h1 class="hero-title mb-3"><?= e($hero['judul']) ?></h1>

                <p class="news-description mb-4">
                  <?= e(potongTeks($hero['isi_berita'], 220)) ?>...
                </p>

                <a
                  href="<?= BASE_URL ?>/detail.php?id=<?= (int) $hero['id'] ?>"
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
          <h2 class="section-title mb-1">Berita Terbaru</h2>
          <p class="section-subtitle mb-0">Ikuti informasi dan berita terkini.</p>
        </div>

        <a href="<?= BASE_URL ?>/kategori.php" class="btn-read">Semua Berita →</a>
      </div>

      <div class="row g-4">

        <?php foreach ($beritaTerbaru as $row): ?>
          <?php renderNewsCard($row); ?>
        <?php endforeach; ?>

        <?php if (!$beritaTerbaru && !$hero): ?>
          <div class="col-12">
            <div class="empty-state">
              <h5>Belum Ada Berita</h5>
              <p class="mb-0">Artikel berita belum tersedia saat ini.</p>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </section>

    <!-- BERITA PER KATEGORI -->
    <?php foreach ($sectionKategori as $idKategori => $section): ?>
      <section class="category-section mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 class="section-title mb-1"><?= e($section['nama']) ?></h2>
            <p class="section-subtitle mb-0">Berita pilihan kategori ini.</p>
          </div>

          <a
            href="<?= BASE_URL ?>/kategori.php?id=<?= (int) $idKategori ?>"
            class="btn btn-outline-primary btn-sm">
            Lihat Semua →
          </a>
        </div>

        <div class="row g-4">
          <?php foreach ($section['artikel'] as $row): ?>
            <?php renderNewsCard($row, 100); ?>
          <?php endforeach; ?>
        </div>

      </section>
    <?php endforeach; ?>

  <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
