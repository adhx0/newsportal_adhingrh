<?php
session_start();

// VALIDASI LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: ../../auth/login.php");
  exit;
}

require_once "../../config/koneksi.php";

// FUNGSI ESCAPE OUTPUT
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

$pesan = '';
$nama = '';

// PROSES SIMPAN KATEGORI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nama = trim($_POST['nama_kategori'] ?? '');

  try {

    // VALIDASI INPUT
    if ($nama === '') {
      throw new Exception("Nama kategori wajib diisi.");
    }

    // BATAS PANJANG NAMA
    if (mb_strlen($nama) > 100) {
      throw new Exception("Nama kategori maksimal 100 karakter.");
    }

    // CEK KATEGORI DUPLIKAT
    $cek = $koneksi->prepare(
      "SELECT id
             FROM kategori
             WHERE nama_kategori ILIKE :nama
             LIMIT 1"
    );

    $cek->execute([
      ':nama' => $nama
    ]);

    if ($cek->fetch(PDO::FETCH_ASSOC)) {
      throw new Exception("Nama kategori sudah tersedia.");
    }

    // SIMPAN KATEGORI
    $simpan = $koneksi->prepare(
      "INSERT INTO kategori (nama_kategori)
             VALUES (:nama_kategori)"
    );

    $simpan->execute([
      ':nama_kategori' => $nama
    ]);

    header("Location: index.php?pesan=tambah");
    exit;
  } catch (PDOException $e) {

    error_log(
      "Gagal menambahkan kategori: " . $e->getMessage()
    );

    $pesan = "Kategori gagal ditambahkan. Silakan coba kembali.";
  } catch (Exception $e) {

    $pesan = $e->getMessage();
  }
}

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page category-form-page">

  <!-- HEADER HALAMAN -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Manajemen Konten</div>
      <h2>Tambah Kategori</h2>
      <p>Tambahkan kategori baru untuk mengelompokkan artikel.</p>
    </div>

    <a href="index.php" class="admin-secondary-button">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

  <!-- FORM KATEGORI -->
  <div class="admin-form-card">

    <div class="admin-form-card-header">
      <div class="admin-form-card-icon">
        <i class="bi bi-tag"></i>
      </div>

      <div>
        <h5>Informasi Kategori</h5>
        <p>Masukkan nama kategori yang ingin ditambahkan.</p>
      </div>
    </div>

    <div class="admin-form-card-body">

      <?php if ($pesan !== ''): ?>
        <div class="admin-notification error">
          <i class="bi bi-exclamation-circle"></i>
          <?= e($pesan); ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <div class="admin-form-group">

          <label for="nama_kategori">
            Nama Kategori
          </label>

          <input
            type="text"
            name="nama_kategori"
            id="nama_kategori"
            class="form-control admin-form-control"
            placeholder="Contoh: Teknologi"
            value="<?= e($nama); ?>"
            maxlength="100"
            required
            autofocus>

          <small class="admin-form-help">
            Gunakan nama kategori yang singkat dan mudah dipahami.
          </small>

        </div>

        <div class="admin-form-actions">

          <button
            type="submit"
            class="admin-primary-button">

            <i class="bi bi-save"></i>
            Simpan Kategori

          </button>

          <a
            href="index.php"
            class="admin-secondary-button">

            Batal

          </a>

        </div>

      </form>

    </div>

  </div>

</div>

<?php include "../footer.php"; ?>