<?php

include "config/koneksi.php";

// VALIDASI ID ARTIKEL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  http_response_code(404);
  die("Artikel tidak ditemukan.");
}

// FUNCTION ESCAPE HTML
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// MENGAMBIL DATA ARTIKEL
$query = $koneksi->prepare(
  "SELECT artikel.*,
            kategori.nama_kategori,
            users.nama
     FROM artikel
     LEFT JOIN kategori
        ON artikel.kategori_id = kategori.id
     LEFT JOIN users
        ON artikel.user_id = users.id
     WHERE artikel.id = :id
     LIMIT 1"
);

$query->execute([':id' => $id]);
$data = $query->fetch(PDO::FETCH_ASSOC);

// JIKA ARTIKEL TIDAK DITEMUKAN
if (!$data) {
  http_response_code(404);
  die("Artikel tidak ditemukan.");
}

// NOTIFIKASI KOMENTAR
$pesan = "";

// PROSES KIRIM KOMENTAR
if (isset($_POST['kirim'])) {

  $nama = trim($_POST['nama'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $isiKomentar = trim($_POST['komentar'] ?? '');

  if ($nama !== '' && $email !== '' && $isiKomentar !== '') {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $pesan = "Format email tidak valid.";
    } else {
      try {
        $stmt = $koneksi->prepare(
          "INSERT INTO komentar
                        (artikel_id, nama, email, komentar)
                     VALUES
                        (:artikel_id, :nama, :email, :komentar)"
        );

        $stmt->execute([
          ':artikel_id' => $id,
          ':nama' => $nama,
          ':email' => $email,
          ':komentar' => $isiKomentar
        ]);

        $pesan = "Komentar berhasil dikirim.";
      } catch (PDOException $error) {
        error_log($error->getMessage());
        $pesan = "Komentar gagal dikirim. Silakan coba kembali.";
      }
    }
  } else {
    $pesan = "Semua kolom komentar wajib diisi.";
  }
}

// MENGAMBIL DATA KOMENTAR
$stmt_komentar = $koneksi->prepare(
  "SELECT *
     FROM komentar
     WHERE artikel_id = :artikel_id
     ORDER BY id DESC"
);

$stmt_komentar->execute([':artikel_id' => $id]);

$daftar_komentar = $stmt_komentar->fetchAll(PDO::FETCH_ASSOC);

include "includes/header.php";
include "includes/navbar.php";

?>

<main class="detail-wrapper">

  <!-- BREADCRUMB -->
  <div class="breadcrumb-news">
    <a href="index.php">Beranda</a>
    <span>/</span>
    <span>Detail Berita</span>
  </div>

  <!-- ARTIKEL -->
  <article class="article-container">

    <div class="article-header">

      <span class="article-category">
        <?= e($data['nama_kategori'] ?? 'Umum'); ?>
      </span>

      <h1 class="article-title">
        <?= e($data['judul']); ?>
      </h1>

      <div class="article-meta">
        <span>
          <i class="bi bi-person-circle"></i>
          <?= e($data['nama'] ?? '-'); ?>
        </span>

        <span class="meta-separator">•</span>

        <span>
          <i class="bi bi-calendar3"></i>
          <?= date('d M Y', strtotime($data['tanggal'])); ?>
        </span>
      </div>

    </div>

    <!-- GAMBAR ARTIKEL -->
    <?php if (!empty($data['thumbnail'])) : ?>
      <div class="article-image-wrapper">
        <img
          src="uploads/<?= e($data['thumbnail']); ?>"
          class="article-image"
          alt="<?= e($data['judul']); ?>">
      </div>
    <?php endif; ?>

    <!-- ISI ARTIKEL -->
    <div class="article-content">
      <?= $data['isi_berita']; ?>
    </div>

    <!-- TOMBOL KEMBALI -->
    <div class="article-footer">
      <a href="index.php" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Beranda
      </a>
    </div>

  </article>

  <!-- BAGIAN KOMENTAR -->
  <section class="comment-section">

    <div class="comment-heading">
      <div>
        <h2>Komentar Pembaca</h2>
        <p>
          <?= count($daftar_komentar); ?> komentar pada berita ini
        </p>
      </div>
    </div>

    <!-- FORM KOMENTAR -->
    <div class="comment-form-card">

      <h3>Tulis Komentar</h3>
      <p class="comment-form-subtitle">
        Bagikan pendapat atau tanggapan kamu mengenai berita ini.
      </p>

      <?php if ($pesan !== '') : ?>
        <div class="alert <?= strpos($pesan, 'berhasil') !== false ? 'alert-success' : 'alert-danger'; ?>">
          <?= e($pesan); ?>
        </div>
      <?php endif; ?>

      <form method="POST">

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
              maxlength="150"
              required>
          </div>

        </div>

        <div class="mb-3">
          <label class="form-label">Komentar</label>
          <textarea
            name="komentar"
            class="form-control"
            rows="4"
            placeholder="Tulis komentar kamu di sini..."
            required></textarea>
        </div>

        <button
          type="submit"
          name="kirim"
          class="btn-news">
          Kirim Komentar
          <i class="bi bi-send"></i>
        </button>

      </form>

    </div>

    <!-- DAFTAR KOMENTAR -->
    <div class="comment-list">

      <?php if (count($daftar_komentar) > 0) : ?>

        <?php foreach ($daftar_komentar as $k) : ?>

          <div class="comment-item">

            <div class="comment-avatar">
              <?= e(strtoupper(substr(trim($k['nama']), 0, 1))); ?>
            </div>

            <div class="comment-body">

              <div class="comment-info">
                <h4><?= e($k['nama']); ?></h4>

                <span>
                  <?= date(
                    'd M Y, H:i',
                    strtotime($k['tanggal'])
                  ); ?>
                </span>
              </div>

              <p class="comment-text">
                <?= nl2br(e($k['komentar'])); ?>
              </p>

            </div>

          </div>

        <?php endforeach; ?>

      <?php else : ?>

        <div class="empty-comments">
          <i class="bi bi-chat-square-text"></i>
          <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
        </div>

      <?php endif; ?>

    </div>

  </section>

</main>

<?php include "includes/footer.php"; ?>