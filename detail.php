<?php

require_once __DIR__ . "/config/base_url.php";
require_once __DIR__ . "/config/koneksi.php";

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
$status_berhasil = false;

// PROSES KIRIM KOMENTAR DAN BALASAN
if (
  isset($_POST['kirim']) ||
  isset($_POST['kirim_balasan'])
) {

  $nama = trim($_POST['nama'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $isiKomentar = trim($_POST['komentar'] ?? '');

  // MENENTUKAN PARENT ID
  $parentId = 0;

  if (isset($_POST['kirim_balasan'])) {
    $parentId = filter_var(
      $_POST['parent_id'] ?? '',
      FILTER_VALIDATE_INT
    );

    if (!$parentId || $parentId < 1) {
      $parentId = -1;
    }
  }

  if ($nama !== '' && $email !== '' && $isiKomentar !== '') {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

      $pesan = "Format email tidak valid.";
    } elseif (strlen($nama) > 100 || strlen($email) > 100) {

      $pesan = "Nama atau email melebihi batas karakter.";
    } elseif (strlen($isiKomentar) > 5000) {

      $pesan = "Komentar maksimal 5000 karakter.";
    } else {

      try {

        // VALIDASI KOMENTAR INDUK
        if (isset($_POST['kirim_balasan'])) {

          if ($parentId < 1) {
            throw new Exception("Komentar tujuan tidak valid.");
          }

          $cek_parent = $koneksi->prepare(
            "SELECT id
            FROM komentar
            WHERE id = :parent_id
              AND artikel_id = :artikel_id
              AND parent_id IS NULL
            LIMIT 1"
          );

          $cek_parent->execute([
            ':parent_id' => $parentId,
            ':artikel_id' => $id
          ]);

          if (!$cek_parent->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception(
              "Komentar tidak ditemukan atau tidak dapat dibalas."
            );
          }
        }

        // MENYIMPAN KOMENTAR
        $stmt = $koneksi->prepare(
          "INSERT INTO komentar
            (artikel_id, parent_id, nama, email, komentar)
          VALUES
            (:artikel_id, :parent_id, :nama, :email, :komentar)"
        );

        $stmt->execute([
          ':artikel_id' => $id,
          ':parent_id' => (
            isset($_POST['kirim_balasan'])
            ? $parentId
            : null
          ),
          ':nama' => $nama,
          ':email' => $email,
          ':komentar' => $isiKomentar
        ]);

        $status_berhasil = true;

        if (isset($_POST['kirim_balasan'])) {
          $pesan = "Balasan berhasil dikirim.";
        } else {
          $pesan = "Komentar berhasil dikirim.";
        }
      } catch (Exception $error) {

        error_log($error->getMessage());
        $pesan = $error->getMessage();
      } catch (PDOException $error) {

        error_log($error->getMessage());
        $pesan = "Komentar gagal dikirim. Silakan coba kembali.";
      }
    }
  } else {

    $pesan = "Semua kolom komentar wajib diisi.";
  }
}

// MENGAMBIL SELURUH KOMENTAR ARTIKEL
$stmt_komentar = $koneksi->prepare(
  "SELECT *
  FROM komentar
  WHERE artikel_id = :artikel_id
  ORDER BY id DESC"
);

$stmt_komentar->execute([':artikel_id' => $id]);

$semua_komentar = $stmt_komentar->fetchAll(PDO::FETCH_ASSOC);

// MEMISAHKAN KOMENTAR UTAMA DAN BALASAN
$daftar_komentar = [];
$daftar_balasan = [];

foreach ($semua_komentar as $komentar) {

  if ($komentar['parent_id'] === null) {

    $daftar_komentar[] = $komentar;
  } else {

    $parentId = (int)$komentar['parent_id'];

    if (!isset($daftar_balasan[$parentId])) {
      $daftar_balasan[$parentId] = [];
    }

    $daftar_balasan[$parentId][] = $komentar;
  }
}

// TOTAL KOMENTAR TERMASUK BALASAN
$total_komentar = count($semua_komentar);

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/navbar.php";

?>

<style>
  /* MEMASTIKAN KOMENTAR UTAMA TIDAK MENGGUNAKAN FLEX */
  .comment-list .comment-item {
    display: block;
    width: 100%;
    margin-bottom: 25px;
  }

  /* WRAPPER KOMENTAR UTAMA */
  .comment-main {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    width: 100%;
  }

  .comment-main .comment-avatar {
    flex-shrink: 0;
  }

  .comment-main .comment-body {
    flex: 1;
    min-width: 0;
  }

  /* AREA BALASAN */
  .comment-replies {
    display: block;
    width: auto;
    margin-left: 48px;
    margin-top: 18px;
    padding-left: 20px;
    border-left: 2px solid #e5e7eb;
  }

  .comment-reply-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
  }

  .comment-reply-item:last-child {
    margin-bottom: 0;
  }

  .comment-reply-avatar {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 50%;
    background: #e8f0fe;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
  }

  .comment-reply-body {
    flex: 1;
    min-width: 0;
  }

  .comment-reply-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 6px;
  }

  .comment-reply-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
  }

  .comment-reply-info span {
    font-size: 12px;
    color: #888;
  }

  .comment-reply-text {
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 8px;
    overflow-wrap: anywhere;
  }

  /* TOMBOL BALAS */
  .comment-reply-button {
    border: none;
    background: transparent;
    padding: 0;
    color: #2563eb;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }

  .comment-reply-button:hover {
    color: #1d4ed8;
    text-decoration: underline;
  }

  /* FORM BALASAN */
  .comment-reply-form {
    display: none;
    width: auto;
    margin-top: 15px;
    margin-left: 48px;
    padding: 18px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
  }

  .comment-reply-form.active {
    display: block;
  }

  .comment-reply-form h5 {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 15px;
  }

  .comment-reply-form .form-control {
    font-size: 14px;
  }

  .comment-reply-form .btn-news {
    font-size: 13px;
  }

  /* RESPONSIVE */
  @media (max-width: 576px) {

    .comment-replies {
      margin-left: 12px;
      padding-left: 12px;
    }

    .comment-reply-form {
      margin-left: 12px;
      padding: 12px;
    }

    .comment-reply-info {
      gap: 5px;
    }

  }
</style>

<main class="detail-wrapper">

  <!-- BREADCRUMB -->
  <div class="breadcrumb-news">
    <a href="<?= BASE_URL ?>/index.php">Beranda</a>
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
          src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($data['thumbnail'])); ?>"
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

      <a href="<?= BASE_URL ?>/index.php" class="back-link">
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
          <?= $total_komentar; ?> komentar pada berita ini
        </p>

      </div>

    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan !== '') : ?>

      <div class="alert <?= $status_berhasil ? 'alert-success' : 'alert-danger'; ?>">
        <?= e($pesan); ?>
      </div>

    <?php endif; ?>

    <!-- FORM KOMENTAR UTAMA -->
    <div class="comment-form-card">

      <h3>Tulis Komentar</h3>

      <p class="comment-form-subtitle">
        Bagikan pendapat atau tanggapan kamu mengenai berita ini.
      </p>

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
              maxlength="100"
              required>

          </div>

        </div>

        <div class="mb-3">

          <label class="form-label">Komentar</label>

          <textarea
            name="komentar"
            class="form-control"
            rows="4"
            maxlength="5000"
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

            <!-- WRAPPER KOMENTAR UTAMA -->
            <div class="comment-main">

              <div class="comment-avatar">
                <?= e(strtoupper(substr(trim($k['nama'] ?? ''), 0, 1))); ?>
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

                <!-- TOMBOL BALAS -->
                <button
                  type="button"
                  class="comment-reply-button"
                  onclick="toggleReply(<?= (int)$k['id']; ?>)">

                  <i class="bi bi-reply"></i>
                  Balas

                </button>

              </div>

            </div>

            <!-- DAFTAR BALASAN -->
            <?php if (!empty($daftar_balasan[$k['id']])) : ?>

              <div class="comment-replies">

                <?php foreach ($daftar_balasan[$k['id']] as $balasan) : ?>

                  <div class="comment-reply-item">

                    <div class="comment-reply-avatar">
                      <?= e(strtoupper(substr(trim($balasan['nama'] ?? ''), 0, 1))); ?>
                    </div>

                    <div class="comment-reply-body">

                      <div class="comment-reply-info">

                        <h4>
                          <?= e($balasan['nama']); ?>
                        </h4>

                        <span>
                          <?= date(
                            'd M Y, H:i',
                            strtotime($balasan['tanggal'])
                          ); ?>
                        </span>

                      </div>

                      <p class="comment-reply-text">
                        <?= nl2br(e($balasan['komentar'])); ?>
                      </p>

                    </div>

                  </div>

                <?php endforeach; ?>

              </div>

            <?php endif; ?>

            <!-- FORM BALASAN -->
            <div
              class="comment-reply-form"
              id="reply-form-<?= (int)$k['id']; ?>">

              <h5>
                <i class="bi bi-reply"></i>
                Balas komentar <?= e($k['nama']); ?>
              </h5>

              <form method="POST">

                <input
                  type="hidden"
                  name="parent_id"
                  value="<?= (int)$k['id']; ?>">

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

                  <label class="form-label">Balasan</label>

                  <textarea
                    name="komentar"
                    class="form-control"
                    rows="3"
                    maxlength="5000"
                    placeholder="Tulis balasan kamu..."
                    required></textarea>

                </div>

                <button
                  type="submit"
                  name="kirim_balasan"
                  class="btn-news">

                  Kirim Balasan
                  <i class="bi bi-send"></i>

                </button>

                <button
                  type="button"
                  class="btn btn-light btn-sm ms-2"
                  onclick="toggleReply(<?= (int)$k['id']; ?>)">

                  Batal

                </button>

              </form>

            </div>

          </div>

        <?php endforeach; ?>

      <?php else : ?>

        <div class="empty-comments">

          <i class="bi bi-chat-square-text"></i>

          <p>
            Belum ada komentar. Jadilah yang pertama berkomentar!
          </p>

        </div>

      <?php endif; ?>

    </div>

  </section>

</main>

<!-- JAVASCRIPT BALAS KOMENTAR -->
<script>
  function toggleReply(id) {

    const form = document.getElementById('reply-form-' + id);

    if (!form) {
      return;
    }

    form.classList.toggle('active');

    if (form.classList.contains('active')) {

      const input = form.querySelector('input[name="nama"]');

      if (input) {
        input.focus();
      }

    }

  }
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>