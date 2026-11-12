<?php
session_start();

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: ../../auth/login.php");
  exit;
}

require_once "../../config/koneksi.php";

// Fungsi escape output
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// Validasi ID artikel
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: index.php?pesan=id_tidak_valid");
  exit;
}

// Ambil data artikel
$stmt = $koneksi->prepare(
  "SELECT * FROM artikel WHERE id = :id"
);

$stmt->execute([':id' => $id]);

$artikel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artikel) {
  header("Location: index.php?pesan=tidak_ditemukan");
  exit;
}

// Ambil kategori
$kategoriQuery = $koneksi->query(
  "SELECT id, nama_kategori
     FROM kategori
     ORDER BY nama_kategori ASC"
);

$kategori = $kategoriQuery->fetchAll(PDO::FETCH_ASSOC);

$pesan = '';
$lokasiBaru = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $judul = trim($_POST['judul'] ?? '');
  $isi = $_POST['isi_berita'] ?? '';

  $kategori_id = filter_input(
    INPUT_POST,
    'kategori_id',
    FILTER_VALIDATE_INT
  );

  $gambarLama = $artikel['thumbnail'];
  $gambarBaru = $gambarLama;

  try {

    // Validasi data
    if (
      $judul === '' ||
      trim(strip_tags($isi)) === '' ||
      !$kategori_id
    ) {
      throw new Exception("Lengkapi semua data artikel.");
    }

    // Validasi kategori
    $cekKategori = $koneksi->prepare(
      "SELECT id FROM kategori WHERE id = :id"
    );

    $cekKategori->execute([
      ':id' => $kategori_id
    ]);

    if (!$cekKategori->fetch(PDO::FETCH_ASSOC)) {
      throw new Exception("Kategori yang dipilih tidak tersedia.");
    }

    // Buat slug
    $slug = strtolower($judul);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');

    if ($slug === '') {
      throw new Exception("Judul artikel tidak dapat dibuat menjadi slug.");
    }

    // Cek thumbnail baru
    if (
      isset($_FILES['thumbnail']) &&
      $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

      $file = $_FILES['thumbnail'];

      if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Thumbnail gagal diunggah.");
      }

      // Batas ukuran 5 MB
      $maksimalUkuran = 5 * 1024 * 1024;

      if ($file['size'] <= 0 || $file['size'] > $maksimalUkuran) {
        throw new Exception("Ukuran thumbnail maksimal 5 MB.");
      }

      // Validasi MIME
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

      // Validasi gambar asli
      if (getimagesize($file['tmp_name']) === false) {
        throw new Exception("File yang diunggah bukan gambar valid.");
      }

      // Pastikan folder upload tersedia
      $folderUpload = "../../uploads/";

      if (!is_dir($folderUpload) || !is_writable($folderUpload)) {
        throw new Exception("Folder uploads tidak tersedia atau tidak dapat ditulis.");
      }

      // Buat nama file unik
      $gambarBaru = 'artikel_' .
        bin2hex(random_bytes(16)) .
        '.' . $allowedTypes[$mime];

      $lokasiBaru = $folderUpload . $gambarBaru;

      if (!move_uploaded_file($file['tmp_name'], $lokasiBaru)) {
        throw new Exception("Gagal menyimpan thumbnail baru.");
      }
    }

    // Update database
    $update = $koneksi->prepare(
      "UPDATE artikel SET
                judul = :judul,
                slug = :slug,
                isi_berita = :isi_berita,
                thumbnail = :thumbnail,
                kategori_id = :kategori_id
             WHERE id = :id"
    );

    $update->execute([
      ':judul'       => $judul,
      ':slug'        => $slug,
      ':isi_berita'  => $isi,
      ':thumbnail'   => $gambarBaru,
      ':kategori_id' => $kategori_id,
      ':id'          => $id
    ]);

    // Hapus thumbnail lama setelah update berhasil
    if (
      $gambarBaru !== $gambarLama &&
      !empty($gambarLama)
    ) {
      $fileLama = "../../uploads/" . basename($gambarLama);

      if (is_file($fileLama)) {
        unlink($fileLama);
      }
    }

    header("Location: index.php?pesan=edit");
    exit;
  } catch (Throwable $e) {

    error_log(
      "Gagal memperbarui artikel: " . $e->getMessage()
    );

    // Hapus thumbnail baru jika proses gagal
    if ($lokasiBaru && is_file($lokasiBaru)) {
      unlink($lokasiBaru);
    }

    $pesan = $e instanceof PDOException
      ? "Artikel gagal diperbarui. Periksa kembali data artikel."
      : $e->getMessage();

    // Pertahankan input
    $artikel['judul'] = $judul;
    $artikel['isi_berita'] = $isi;
    $artikel['kategori_id'] = $kategori_id;
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
      <h2>Edit Artikel</h2>
      <p>Perbarui informasi dan konten artikel NewsPortal.</p>
    </div>

    <a href="index.php" class="admin-secondary-button">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

  <!-- Form edit artikel -->
  <div class="admin-form-card">

    <div class="admin-form-card-header">
      <div class="admin-form-card-icon">
        <i class="bi bi-pencil-square"></i>
      </div>

      <div>
        <h5>Informasi Artikel</h5>
        <p>Perbarui informasi artikel di bawah ini.</p>
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
            value="<?= e($artikel['judul']); ?>"
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

            <?php foreach ($kategori as $k): ?>
              <option
                value="<?= (int)$k['id']; ?>"
                <?= ($artikel['kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                <?= e($k['nama_kategori']); ?>
              </option>
            <?php endforeach; ?>

          </select>

          <?php if (empty($kategori)): ?>
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
            class="form-control admin-form-control"
            required><?= e($artikel['isi_berita']); ?></textarea>
        </div>

        <!-- Thumbnail lama -->
        <div class="admin-form-group">
          <label>Thumbnail Saat Ini</label>

          <?php if (!empty($artikel['thumbnail'])): ?>

            <div class="article-current-thumbnail">
              <img
                src="../../uploads/<?= rawurlencode(basename($artikel['thumbnail'])); ?>"
                alt="Thumbnail artikel saat ini">
            </div>

          <?php else: ?>

            <div class="article-no-thumbnail">
              <i class="bi bi-image"></i>
              <span>Belum ada thumbnail.</span>
            </div>

          <?php endif; ?>
        </div>

        <!-- Thumbnail baru -->
        <div class="admin-form-group">
          <label for="thumbnail">Ganti Thumbnail (Opsional)</label>

          <input
            type="file"
            name="thumbnail"
            id="thumbnail"
            class="form-control admin-form-control"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

          <small class="admin-form-help">
            Kosongkan jika tidak ingin mengganti thumbnail.
            Format JPG, PNG, atau WEBP. Maksimal 5 MB.
          </small>

          <div id="thumbnailPreview" class="article-upload-preview">
            <p>Preview thumbnail baru:</p>
            <img id="previewImage" src="" alt="Preview thumbnail baru">
          </div>
        </div>

        <!-- Tombol -->
        <div class="admin-form-actions">
          <button
            type="submit"
            class="admin-primary-button"
            <?= empty($kategori) ? 'disabled' : ''; ?>>
            <i class="bi bi-save"></i>
            Simpan Perubahan
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