<?php

// Session hanya dibuka bila perlu (kirim komentar atau pesan flash dari redirect).
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_COOKIE[session_name()])) {
  session_start();
}

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/media.php';
require_once __DIR__ . '/config/koneksi.php';

/**
 * Validasi dan simpan komentar / balasan.
 *
 * @return array{0: bool, 1: string} [berhasil, pesan]
 */
function prosesKomentar(PDO $db, int $artikelId): array
{
  $balasan = isset($_POST['kirim_balasan']);
  $nama = trim($_POST['nama'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $isi = trim($_POST['komentar'] ?? '');
  $parentId = $balasan ? filter_var($_POST['parent_id'] ?? '', FILTER_VALIDATE_INT) : null;

  if ($nama === '' || $email === '' || $isi === '') {
    return [false, 'Semua kolom komentar wajib diisi.'];
  }

  if ($balasan && (!$parentId || $parentId < 1)) {
    return [false, 'Komentar tujuan tidak valid.'];
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return [false, 'Format email tidak valid.'];
  }

  if (mb_strlen($nama) > 100 || mb_strlen($email) > 100) {
    return [false, 'Nama atau email melebihi batas karakter.'];
  }

  if (mb_strlen($isi) > 5000) {
    return [false, 'Komentar maksimal 5000 karakter.'];
  }

  try {
    // Balasan hanya boleh ditujukan ke komentar utama pada artikel ini.
    if ($balasan) {
      $cek = $db->prepare(
        "SELECT 1 FROM komentar
         WHERE id = :parent_id AND artikel_id = :artikel_id AND parent_id IS NULL
         LIMIT 1"
      );
      $cek->execute([':parent_id' => $parentId, ':artikel_id' => $artikelId]);

      if (!$cek->fetchColumn()) {
        return [false, 'Komentar tidak ditemukan atau tidak dapat dibalas.'];
      }
    }

    $db->prepare(
      "INSERT INTO komentar (artikel_id, parent_id, nama, email, komentar)
       VALUES (:artikel_id, :parent_id, :nama, :email, :komentar)"
    )->execute([
      ':artikel_id' => $artikelId,
      ':parent_id'  => $parentId,
      ':nama'       => $nama,
      ':email'      => $email,
      ':komentar'   => $isi,
    ]);

    return [true, $balasan ? 'Balasan berhasil dikirim.' : 'Komentar berhasil dikirim.'];
  } catch (PDOException $error) {
    error_log($error->getMessage());

    return [false, 'Komentar gagal dikirim. Silakan coba kembali.'];
  }
}

/** Form komentar utama (parent null) atau form balasan. */
function renderFormKomentar(string $namaTombol, string $label, string $placeholder, int $baris, ?int $parentId = null): void
{
?>
  <form method="POST">
    <?php if ($parentId !== null): ?>
      <input type="hidden" name="parent_id" value="<?= $parentId ?>">
    <?php endif; ?>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Nama</label>
        <input
          type="text"
          name="nama"
          class="form-control"
          placeholder="Masukkan nama kamu"
          maxlength="100"
          required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input
          type="email"
          name="email"
          class="form-control"
          placeholder="nama@email.com"
          maxlength="100"
          required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label"><?= e($label) ?></label>
      <textarea
        name="komentar"
        class="form-control"
        rows="<?= $baris ?>"
        maxlength="5000"
        placeholder="<?= e($placeholder) ?>"
        required></textarea>
    </div>

    <button type="submit" name="<?= e($namaTombol) ?>" class="btn-news">
      <?= $parentId !== null ? 'Kirim Balasan' : 'Kirim Komentar' ?>
      <i class="bi bi-send"></i>
    </button>

    <?php if ($parentId !== null): ?>
      <button
        type="button"
        class="btn btn-light btn-sm ms-2"
        onclick="toggleReply(<?= $parentId ?>)">
        Batal
      </button>
    <?php endif; ?>
  </form>
<?php
}

// Validasi ID artikel.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  http_response_code(404);
  exit('Artikel tidak ditemukan.');
}

// Ambil artikel (hanya kolom yang dipakai halaman ini).
$queryArtikel = $koneksi->prepare(
  "SELECT a.id, a.judul, a.isi_berita, a.thumbnail, a.media_url, a.tanggal,
          k.nama_kategori, u.nama
   FROM artikel a
   LEFT JOIN kategori k ON a.kategori_id = k.id
   LEFT JOIN users u ON a.user_id = u.id
   WHERE a.id = :id
   LIMIT 1"
);
$queryArtikel->execute([':id' => $id]);
$data = $queryArtikel->fetch();

if (!$data) {
  http_response_code(404);
  exit('Artikel tidak ditemukan.');
}

// POST-Redirect-GET agar komentar tidak terkirim ulang saat halaman di-refresh.
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  (isset($_POST['kirim']) || isset($_POST['kirim_balasan']))
) {
  [$berhasil, $pesan] = prosesKomentar($koneksi, $id);

  $_SESSION['detail_flash'] = ['pesan' => $pesan, 'berhasil' => $berhasil];

  redirect('/detail.php', ['id' => $id], 303);
}

// Ambil pesan flash lalu segera lepas kunci session.
$flash = $_SESSION['detail_flash'] ?? null;
unset($_SESSION['detail_flash']);

if (session_status() === PHP_SESSION_ACTIVE) {
  session_write_close();
}

$pesan = $flash['pesan'] ?? '';
$statusBerhasil = $flash['berhasil'] ?? false;

// Media dalam isi artikel.
$isiArtikel = renderMediaEmbed((string) $data['isi_berita']);
$punyaOembed = stripos((string) $data['isi_berita'], '<oembed') !== false;

// Media URL lama dipakai hanya bila isi artikel tidak punya embed.
$videoLama = $punyaOembed
  ? ''
  : renderVideo($data['media_url'], (string) $data['judul'], 'mb-4');

// Komentar: ambil sekali, kelompokkan menjadi komentar utama dan balasan.
$queryKomentar = $koneksi->prepare(
  "SELECT id, parent_id, nama, komentar, tanggal
   FROM komentar
   WHERE artikel_id = :artikel_id
   ORDER BY id DESC"
);
$queryKomentar->execute([':artikel_id' => $id]);
$semuaKomentar = $queryKomentar->fetchAll();

$daftarKomentar = [];
$daftarBalasan = [];

foreach ($semuaKomentar as $komentar) {
  if ($komentar['parent_id'] === null) {
    $daftarKomentar[] = $komentar;
  } else {
    $daftarBalasan[(int) $komentar['parent_id']][] = $komentar;
  }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="detail-wrapper">

  <!-- Breadcrumb -->
  <div class="breadcrumb-news">
    <a href="<?= BASE_URL ?>/index.php">Beranda</a>
    <span>/</span>
    <span>Detail Berita</span>
  </div>

  <!-- Artikel -->
  <article class="article-container">

    <div class="article-header">
      <span class="article-category"><?= e($data['nama_kategori'] ?? 'Umum') ?></span>

      <h1 class="article-title"><?= e($data['judul']) ?></h1>

      <div class="article-meta">
        <span><i class="bi bi-person-circle"></i> <?= e($data['nama'] ?? '-') ?></span>
        <span class="meta-separator">•</span>
        <span><i class="bi bi-calendar3"></i> <?= e(formatTanggal($data['tanggal'])) ?></span>
      </div>
    </div>

    <?php if (!empty($data['thumbnail'])): ?>
      <div class="article-image-wrapper">
        <img
          src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($data['thumbnail'])) ?>"
          class="article-image"
          alt="<?= e($data['judul']) ?>"
          fetchpriority="high"
          decoding="async">
      </div>
    <?php endif; ?>

    <?= $videoLama ?>

    <div class="article-content">
      <?= $isiArtikel ?>
    </div>

    <div class="article-footer">
      <a href="<?= BASE_URL ?>/index.php" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Beranda
      </a>
    </div>

  </article>

  <!-- Komentar -->
  <section class="comment-section">

    <div class="comment-heading">
      <div>
        <h2>Komentar Pembaca</h2>
        <p><?= count($semuaKomentar) ?> komentar pada berita ini</p>
      </div>
    </div>

    <?php if ($pesan !== ''): ?>
      <div class="alert <?= $statusBerhasil ? 'alert-success' : 'alert-danger' ?>">
        <?= e($pesan) ?>
      </div>
    <?php endif; ?>

    <div class="comment-form-card">
      <h3>Tulis Komentar</h3>

      <p class="comment-form-subtitle">
        Bagikan pendapat atau tanggapan kamu mengenai berita ini.
      </p>

      <?php renderFormKomentar('kirim', 'Komentar', 'Tulis komentar kamu di sini...', 4); ?>
    </div>

    <div class="comment-list">

      <?php foreach ($daftarKomentar as $k): ?>
        <?php $komentarId = (int) $k['id']; ?>

        <div class="comment-item">

          <div class="comment-main">

            <div class="comment-avatar">
              <?= e(strtoupper(mb_substr(trim($k['nama']), 0, 1))) ?>
            </div>

            <div class="comment-body">
              <div class="comment-info">
                <h4><?= e($k['nama']) ?></h4>
                <span><?= e(formatTanggal($k['tanggal'], 'd M Y, H:i')) ?></span>
              </div>

              <p class="comment-text"><?= nl2br(e($k['komentar'])) ?></p>

              <button
                type="button"
                class="comment-reply-button"
                onclick="toggleReply(<?= $komentarId ?>)">
                <i class="bi bi-reply"></i>
                Balas
              </button>
            </div>

          </div>

          <?php if (!empty($daftarBalasan[$komentarId])): ?>
            <div class="comment-replies">
              <?php foreach ($daftarBalasan[$komentarId] as $balasan): ?>
                <div class="comment-reply-item">

                  <div class="comment-reply-avatar">
                    <?= e(strtoupper(mb_substr(trim($balasan['nama']), 0, 1))) ?>
                  </div>

                  <div class="comment-reply-body">
                    <div class="comment-reply-info">
                      <h4><?= e($balasan['nama']) ?></h4>
                      <span><?= e(formatTanggal($balasan['tanggal'], 'd M Y, H:i')) ?></span>
                    </div>

                    <p class="comment-reply-text"><?= nl2br(e($balasan['komentar'])) ?></p>
                  </div>

                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="comment-reply-form" id="reply-form-<?= $komentarId ?>">
            <h5>
              <i class="bi bi-reply"></i>
              Balas komentar <?= e($k['nama']) ?>
            </h5>

            <?php renderFormKomentar('kirim_balasan', 'Balasan', 'Tulis balasan kamu...', 3, $komentarId); ?>
          </div>

        </div>
      <?php endforeach; ?>

      <?php if (!$daftarKomentar): ?>
        <div class="empty-comments">
          <i class="bi bi-chat-square-text"></i>
          <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
        </div>
      <?php endif; ?>

    </div>

  </section>

</main>

<script>
  function toggleReply(id) {
    const form = document.getElementById('reply-form-' + id);

    if (!form) return;

    form.classList.toggle('active');

    if (form.classList.contains('active')) {
      form.querySelector('input[name="nama"]')?.focus();
    }
  }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
