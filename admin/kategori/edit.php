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

// VALIDASI ID KATEGORI
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: index.php?pesan=id_tidak_valid");
  exit;
}

// AMBIL DATA KATEGORI
$query = $koneksi->prepare(
  "SELECT id, nama_kategori
     FROM kategori
     WHERE id = :id"
);

$query->execute([
  ':id' => $id
]);

$data = $query->fetch(PDO::FETCH_ASSOC);

// CEK KATEGORI
if (!$data) {
  header("Location: index.php?pesan=tidak_ditemukan");
  exit;
}

$pesan = '';
$nama = $data['nama_kategori'];

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nama = trim($_POST['nama_kategori'] ?? '');

  try {

    // VALIDASI INPUT
    if ($nama === '') {
      throw new Exception("Nama kategori wajib diisi.");
    }

    if (mb_strlen($nama) > 100) {
      throw new Exception("Nama kategori maksimal 100 karakter.");
    }

    // CEK DUPLIKAT, KECUALI KATEGORI YANG SEDANG DIEDIT
    $cek = $koneksi->prepare(
      "SELECT id
             FROM kategori
             WHERE nama_kategori ILIKE :nama
             AND id <> :id
             LIMIT 1"
    );

    $cek->execute([
      ':nama' => $nama,
      ':id' => $id
    ]);

    if ($cek->fetch(PDO::FETCH_ASSOC)) {
      throw new Exception("Nama kategori sudah digunakan.");
    }

    // UPDATE DATA KATEGORI
    $update = $koneksi->prepare(
      "UPDATE kategori
             SET nama_kategori = :nama
             WHERE id = :id"
    );

    $update->execute([
      ':nama' => $nama,
      ':id' => $id
    ]);

    header("Location: index.php?pesan=update");
    exit;
  } catch (PDOException $e) {

    error_log(
      "Gagal memperbarui kategori: " . $e->getMessage()
    );

    $pesan = "Kategori gagal diperbarui. Silakan coba kembali.";
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
      <h2>Edit Kategori</h2>
      <p>Perbarui informasi kategori NewsPortal.</p>
    </div>

    <a href="index.php" class="admin-secondary-button">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

  <!-- FORM EDIT KATEGORI -->
  <div class="admin-form-card">

    <div class="admin-form-card-header">
      <div class="admin-form-card-icon">
        <i class="bi bi-pencil-square"></i>
      </div>

      <div>
        <h5>Informasi Kategori</h5>
        <p>Ubah nama kategori sesuai kebutuhan.</p>
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
            placeholder="Masukkan nama kategori"
            value="<?= e($nama); ?>"
            maxlength="100"
            required
            autofocus>

          <small class="admin-form-help">
            Maksimal 100 karakter.
          </small>

        </div>

        <div class="admin-form-actions">

          <button
            type="submit"
            class="admin-primary-button">

            <i class="bi bi-save"></i>
            Update Kategori

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