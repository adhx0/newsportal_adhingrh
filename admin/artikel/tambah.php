<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../config/koneksi.php';

wajibLogin();

$folderUpload = __DIR__ . '/../../uploads/';

$daftarKategori = $koneksi->query(
  "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC"
)->fetchAll();

$pesan = '';
$thumbnailBaru = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $judul = trim($_POST['judul'] ?? '');
  $isi = $_POST['isi_berita'] ?? '';
  $kategoriId = filter_input(INPUT_POST, 'kategori_id', FILTER_VALIDATE_INT);
  $userId = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT);

  try {

    if ($judul === '' || !validasiIsiArtikel($isi) || !$kategoriId || !$userId) {
      throw new RuntimeException('Lengkapi semua data artikel.');
    }

    $cekKategori = $koneksi->prepare("SELECT 1 FROM kategori WHERE id = :id");
    $cekKategori->execute([':id' => $kategoriId]);

    if (!$cekKategori->fetchColumn()) {
      throw new RuntimeException('Kategori yang dipilih tidak tersedia.');
    }

    if (($_FILES['thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      throw new RuntimeException('Thumbnail wajib diunggah.');
    }

    $slug = buatSlug($judul);

    if ($slug === '') {
      throw new RuntimeException('Judul artikel tidak dapat dibuat menjadi slug.');
    }

    $thumbnailBaru = simpanThumbnail($_FILES['thumbnail'], $folderUpload);

    $koneksi->prepare(
      "INSERT INTO artikel (judul, slug, isi_berita, thumbnail, media_url, kategori_id, user_id)
       VALUES (:judul, :slug, :isi_berita, :thumbnail, NULL, :kategori_id, :user_id)"
    )->execute([
      ':judul'       => $judul,
      ':slug'        => $slug,
      ':isi_berita'  => $isi,
      ':thumbnail'   => $thumbnailBaru,
      ':kategori_id' => $kategoriId,
      ':user_id'     => $userId,
    ]);

    redirect('/admin/artikel/index.php', ['pesan' => 'tambah']);
  } catch (Throwable $error) {

    error_log('Gagal menambahkan artikel: ' . $error->getMessage());

    // Hapus thumbnail yang sudah tersimpan jika proses gagal.
    hapusFileUpload($thumbnailBaru, $folderUpload);

    $pesan = $error instanceof PDOException
      ? 'Artikel gagal disimpan. Periksa kembali data artikel.'
      : $error->getMessage();
  }
}

$pakaiEditor = true; // muat CKEditor di footer

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page article-form-page">

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

      <form method="POST" enctype="multipart/form-data" id="formArtikel">

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

          <small class="admin-form-help">
            Gunakan tombol Media Embed pada toolbar untuk menambahkan video YouTube atau MP4.
          </small>
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

    let previewUrl = null;

    input.addEventListener('change', function() {

      const file = this.files[0];

      if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
      }

      preview.style.display = 'none';
      image.src = '';

      if (!file) return;

      const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
      ];

      if (!allowedTypes.includes(file.type)) {
        alert('Format file harus JPG, PNG, atau WEBP.');
        this.value = '';
        return;
      }

      if (file.size <= 0 || file.size > 5 * 1024 * 1024) {
        alert('Ukuran thumbnail maksimal 5 MB.');
        this.value = '';
        return;
      }

      previewUrl = URL.createObjectURL(file);
      image.src = previewUrl;
      preview.style.display = 'block';
    });

    window.addEventListener('beforeunload', function() {
      if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
      }
    });

  });
</script>

<?php include "../footer.php"; ?>