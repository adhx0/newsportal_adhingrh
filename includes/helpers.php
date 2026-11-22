<?php

/**
 * Helper bersama untuk halaman publik dan admin.
 * Cukup: require_once __DIR__ . '/includes/helpers.php';
 */

require_once __DIR__ . '/../config/base_url.php';

// Guard: beberapa halaman admin lama masih punya e() sendiri.
if (!function_exists('e')) {
  /** Escape output HTML. */
  function e($value): string
  {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  }
}

/** URL aset dengan versi dari waktu modifikasi file (cache-busting otomatis). */
function assetUrl(string $path): string
{
  $path = ltrim($path, '/');
  $file = __DIR__ . '/../' . $path;

  return BASE_URL . '/' . $path . '?v=' . (is_file($file) ? filemtime($file) : 1);
}

/** Redirect ke path di dalam aplikasi, lalu hentikan script. */
function redirect(string $path, array $query = [], int $status = 302): void
{
  $url = BASE_URL . $path . ($query ? '?' . http_build_query($query) : '');

  header('Location: ' . $url, true, $status);
  exit;
}

/** Ubah teks menjadi pola LIKE yang aman (karakter % dan _ dianggap biasa). */
function polaLike(string $kata): string
{
  return '%' . addcslashes($kata, '%_\\') . '%';
}

/** Potong teks polos (tanpa HTML) berdasarkan jumlah karakter. */
function potongTeks($text, int $length = 110): string
{
  return mb_substr(trim(strip_tags((string) ($text ?? ''))), 0, $length);
}

/** Format tanggal, "-" jika tidak valid. */
function formatTanggal($tanggal, string $format = 'd M Y'): string
{
  $timestamp = strtotime((string) ($tanggal ?? ''));

  return $timestamp !== false ? date($format, $timestamp) : '-';
}

/** Waktu relatif, mis. "3 jam yang lalu". */
function waktu_lalu($datetime): string
{
  if (empty($datetime) || ($timestamp = strtotime($datetime)) === false) {
    return '-';
  }

  $selisih = time() - $timestamp;

  if ($selisih < 0) {
    return date('d M Y', $timestamp);
  }

  $satuan = [
    31536000 => 'tahun',
    2592000  => 'bulan',
    604800   => 'minggu',
    86400    => 'hari',
    3600     => 'jam',
    60       => 'menit',
  ];

  foreach ($satuan as $detik => $nama) {
    if ($selisih >= $detik) {
      return floor($selisih / $detik) . " $nama yang lalu";
    }
  }

  return "$selisih detik yang lalu";
}

/** Slug dari judul. */
function buatSlug(string $judul): string
{
  return trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($judul)), '-');
}

/** Isi artikel dianggap valid jika ada teks atau embed media. */
function validasiIsiArtikel($isi): bool
{
  $teks = html_entity_decode(
    strip_tags(str_replace('&nbsp;', ' ', (string) $isi)),
    ENT_QUOTES | ENT_HTML5,
    'UTF-8'
  );

  return trim($teks) !== '' ||
    preg_match('/<oembed\b[^>]*\burl\s*=\s*(["\']).+?\1/i', (string) $isi) === 1;
}

/** Kartu berita (beranda, hasil pencarian, section kategori). Gambar di-lazy-load. */
function renderNewsCard(array $row, int $length = 110): void
{
  $judul = $row['judul'] ?? '';
  $thumbnail = rawurlencode(basename($row['thumbnail'] ?? ''));
  $id = (int) ($row['id'] ?? 0);
  $kategori = $row['nama_kategori'] ?? 'Umum';
?>
  <div class="col-md-6 col-lg-4">
    <article class="news-card h-100">

      <img
        src="<?= BASE_URL ?>/uploads/<?= $thumbnail ?>"
        class="news-image"
        alt="<?= e($judul) ?>"
        loading="lazy"
        decoding="async">

      <div class="card-body d-flex flex-column">

        <?php if ($kategori !== ''): ?>
          <div class="mb-3">
            <span class="category-label"><?= e($kategori) ?></span>
          </div>
        <?php endif; ?>

        <h3 class="news-title mb-2"><?= e($judul) ?></h3>

        <div class="news-meta mb-3">
          <?= e($row['nama'] ?? '-') ?>
          <span class="mx-1">·</span>
          <?= e(waktu_lalu($row['tanggal'] ?? '')) ?>
        </div>

        <p class="news-description mb-4">
          <?= e(potongTeks($row['isi_berita'] ?? '', $length)) ?>...
        </p>

        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/detail.php?id=<?= $id ?>" class="btn-read">
            Baca Selengkapnya →
          </a>
        </div>

      </div>
    </article>
  </div>
<?php
}
