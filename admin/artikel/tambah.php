<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// Fungsi escape output
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// Ambil kategori
$kategoriQuery = $koneksi->query(
  "SELECT id, nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC"
);

$daftarKategori = $kategoriQuery->fetchAll(PDO::FETCH_ASSOC);

$pesan = '';
$lokasi = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $judul = trim($_POST['judul'] ?? '');
  $isi = $_POST['isi_berita'] ?? '';

  $kategori_id = filter_input(
    INPUT_POST,
    'kategori_id',
    FILTER_VALIDATE_INT
  );

  $user_id = filter_var(
    $_SESSION['id'] ?? null,
    FILTER_VALIDATE_INT
  );

  try {

    // Validasi data artikel
    if (
      $judul === '' ||
      trim(strip_tags($isi)) === '' ||
      !$kategori_id ||
      !$user_id
    ) {
      throw new Exception("Lengkapi semua data artikel.");
    }

    // Pastikan kategori tersedia
    $cekKategori = $koneksi->prepare(
      "SELECT id FROM kategori WHERE id = :id"
    );

    $cekKategori->execute([
      ':id' => $kategori_id
    ]);

    if (!$cekKategori->fetch(PDO::FETCH_ASSOC)) {
      throw new Exception("Kategori yang dipilih tidak tersedia.");
    }

    // Validasi file thumbnail
    if (!isset($_FILES['thumbnail'])) {
      throw new Exception("Thumbnail wajib diunggah.");
    }

    $file = $_FILES['thumbnail'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      throw new Exception("Thumbnail gagal diunggah.");
    }

    // Batas ukuran file 5 MB
    $maksimalUkuran = 5 * 1024 * 1024;

    if ($file['size'] <= 0 || $file['size'] > $maksimalUkuran) {
      throw new Exception("Ukuran thumbnail maksimal 5 MB.");
    }

    // Validasi MIME file
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedTypes = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mime])) {
      throw new Exception(
        "Format thumbnail harus JPG, PNG, atau WEBP."
      );
    }

    // Pastikan file benar-benar gambar
    if (getimagesize($file['tmp_name']) === false) {
      throw new Exception("File yang diunggah bukan gambar valid.");
    }

    // Buat slug dari judul
    $slug = strtolower($judul);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');

    if ($slug === '') {
      throw new Exception("Judul artikel tidak dapat dibuat menjadi slug.");
    }

    // Buat nama thumbnail unik
    $gambar = 'artikel_' .
      bin2hex(random_bytes(16)) .
      '.' . $allowedTypes[$mime];

    // Lokasi folder uploads di root proyek
    $folderUpload = __DIR__ . '/../../uploads/';

    if (!is_dir($folderUpload) || !is_writable($folderUpload)) {
      throw new Exception("Folder uploads tidak tersedia atau tidak dapat ditulis.");
    }

    $lokasi = $folderUpload . $gambar;

    if (!move_uploaded_file($file['tmp_name'], $lokasi)) {
      throw new Exception("Gagal menyimpan thumbnail.");
    }

    // Simpan artikel ke database
    $simpan = $koneksi->prepare(
      "INSERT INTO artikel
                (
                    judul,
                    slug,
                    isi_berita,
                    thumbnail,
                    kategori_id,
                    user_id
                )
             VALUES
                (
                    :judul,
                    :slug,
                    :isi_berita,
                    :thumbnail,
                    :kategori_id,
                    :user_id
                )"
    );

    $simpan->execute([
      ':judul'       => $judul,
      ':slug'        => $slug,
      ':isi_berita'  => $isi,
      ':thumbnail'   => $gambar,
      ':kategori_id' => $kategori_id,
      ':user_id'     => $user_id
    ]);

    header("Location: " . BASE_URL . "/admin/artikel/index.php?pesan=tambah");
    exit;
  } catch (Throwable $e) {

    error_log(
      "Gagal menambahkan artikel: " . $e->getMessage()
    );

    // Hapus thumbnail jika proses database gagal
    if ($lokasi && file_exists($lokasi)) {
      unlink($lokasi);
    }

    $pesan = $e instanceof PDOException
      ? "Artikel gagal disimpan. Periksa kembali data artikel."
      : $e->getMessage();
  }
}

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page article-form-page">

  <!-- Header halaman -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Konten Website</div>
      <h2>Tambah Artikel</h2>
      <p>Buat dan publikasikan artikel baru di NewsPortal.</p>
    </div>

    <a href="index.php" class="admin-secondary-button">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

  <!-- Form artikel -->
  <div class="admin-form-card">

    <div class="admin-form-card-header">
      <div class="admin-form-card-icon">
        <i class="bi bi-file-earmark-plus"></i>
      </div>

      <div>
        <h5>Informasi Artikel</h5>
        <p>Lengkapi informasi artikel di bawah ini.</p>
      </div>
    </div>

    <div class="admin-form-card-body">

      <?php if ($pesan !== ''): ?>
        <div class="admin-notification error">
          <i class="bi bi-exclamation-circle"></i>
          <?= e($pesan); ?>
        </div>
      <?php endif; ?>

      <form
        method="POST"
        enctype="multipart/form-data"
        id="formArtikel">

        <!-- Judul -->
        <div class="admin-form-group">
          <label for="judul">Judul Artikel</label>

          <input
            type="text"
            name="judul"
            id="judul"
            class="form-control admin-form-control"
            placeholder="Masukkan judul artikel"
            value="<?= e($_POST['judul'] ?? ''); ?>"
            maxlength="255"
            required>
        </div>

        <!-- Kategori -->
        <div class="admin-form-group">
          <label for="kategori_id">Kategori</label>

          <select
            name="kategori_id"
            id="kategori_id"
            class="form-select admin-form-select"
            required>

            <option value="">Pilih kategori artikel</option>

            <?php foreach ($daftarKategori as $k): ?>
              <option
                value="<?= (int)$k['id']; ?>"
                <?= (($_POST['kategori_id'] ?? '') == $k['id']) ? 'selected' : ''; ?>>
                <?= e($k['nama_kategori']); ?>
              </option>
            <?php endforeach; ?>

          </select>

          <?php if (empty($daftarKategori)): ?>
            <small class="text-danger">
              Belum ada kategori. Tambahkan kategori terlebih dahulu.
            </small>
          <?php endif; ?>
        </div>

        <!-- Isi berita -->
        <div class="admin-form-group">
          <label for="editor">Isi Berita</label>

          <textarea
            name="isi_berita"
            id="editor"
            rows="12"
            class="form-control admin-form-control"><?= e($_POST['isi_berita'] ?? ''); ?></textarea>
        </div>

        <!-- Thumbnail -->
        <div class="admin-form-group">
          <label for="thumbnail">Thumbnail Artikel</label>

          <input
            type="file"
            name="thumbnail"
            id="thumbnail"
            class="form-control admin-form-control"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            required>

          <small class="admin-form-help">
            Format JPG, PNG, atau WEBP. Ukuran maksimal 5 MB.
          </small>

          <div id="thumbnailPreview" class="article-upload-preview">
            <img id="previewImage" src="" alt="Preview thumbnail">
          </div>
        </div>

        <!-- Tombol -->
        <div class="admin-form-actions">
          <button
            type="submit"
            class="admin-primary-button"
            <?= empty($daftarKategori) ? 'disabled' : ''; ?>>
            <i class="bi bi-save"></i>
            Simpan Artikel
          </button>

          <a href="index.php" class="admin-secondary-button">
            Batal
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('thumbnail');
    const preview = document.getElementById('thumbnailPreview');
    const image = document.getElementById('previewImage');

    input.addEventListener('change', function() {
      const file = this.files[0];

      if (!file) {
        preview.style.display = 'none';
        image.src = '';
        return;
      }

      const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
      ];

      if (!allowedTypes.includes(file.type)) {
        alert('Format file harus JPG, PNG, atau WEBP.');
        this.value = '';
        preview.style.display = 'none';
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran thumbnail maksimal 5 MB.');
        this.value = '';
        preview.style.display = 'none';
        return;
      }

      const reader = new FileReader();

      reader.onload = function(event) {
        image.src = event.target.result;
        preview.style.display = 'block';
      };

      reader.readAsDataURL(file);
    });
  });
</script>

<?php include "../footer.php"; ?>