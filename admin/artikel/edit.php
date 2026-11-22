<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../config/koneksi.php';

wajibLogin();

const URL_DAFTAR_ARTIKEL = '/admin/artikel/index.php';

$folderUpload = __DIR__ . '/../../uploads/';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'id_tidak_valid']);
}

$stmt = $koneksi->prepare(
  "SELECT id, user_id, judul, isi_berita, thumbnail, kategori_id FROM artikel WHERE id = :id"
);
$stmt->execute([':id' => $id]);
$artikel = $stmt->fetch();

// Role "user" hanya boleh mengedit artikel miliknya sendiri.
if (
  !$artikel ||
  ($_SESSION['role'] === 'user' && (int) $artikel['user_id'] !== (int) ($_SESSION['id'] ?? 0))
) {
  redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'tidak_ditemukan']);
}

$kategori = $koneksi->query(
  "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC"
)->fetchAll();

$pesan = '';
$thumbnailBaru = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $judul = trim($_POST['judul'] ?? '');
  $isi = $_POST['isi_berita'] ?? '';
  $kategoriId = filter_input(INPUT_POST, 'kategori_id', FILTER_VALIDATE_INT);

  try {

    if ($judul === '' || !validasiIsiArtikel($isi) || !$kategoriId) {
      throw new RuntimeException('Lengkapi semua data artikel.');
    }

    $cekKategori = $koneksi->prepare("SELECT 1 FROM kategori WHERE id = :id");
    $cekKategori->execute([':id' => $kategoriId]);

    if (!$cekKategori->fetchColumn()) {
      throw new RuntimeException('Kategori yang dipilih tidak tersedia.');
    }

    $slug = buatSlug($judul);

    if ($slug === '') {
      throw new RuntimeException('Judul artikel tidak dapat dibuat menjadi slug.');
    }

    // Thumbnail baru bersifat opsional; jika kosong, thumbnail lama dipertahankan.
    $thumbnail = $artikel['thumbnail'];

    if (($_FILES['thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
      $thumbnailBaru = simpanThumbnail($_FILES['thumbnail'], $folderUpload);
      $thumbnail = $thumbnailBaru;
    }

    // media_url tidak diubah.
    $koneksi->prepare(
      "UPDATE artikel
       SET judul = :judul, slug = :slug, isi_berita = :isi_berita,
           thumbnail = :thumbnail, kategori_id = :kategori_id
       WHERE id = :id"
    )->execute([
      ':judul'       => $judul,
      ':slug'        => $slug,
      ':isi_berita'  => $isi,
      ':thumbnail'   => $thumbnail,
      ':kategori_id' => $kategoriId,
      ':id'          => $id,
    ]);

    // Hapus thumbnail lama setelah berhasil diganti.
    if ($thumbnailBaru !== null) {
      hapusFileUpload($artikel['thumbnail'], $folderUpload);
    }

    redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'edit']);
  } catch (Throwable $error) {

    error_log('Gagal memperbarui artikel: ' . $error->getMessage());

    // Hapus thumbnail baru jika proses gagal.
    hapusFileUpload($thumbnailBaru, $folderUpload);

    $pesan = $error instanceof PDOException
      ? 'Artikel gagal diperbarui. Periksa kembali data artikel.'
      : $error->getMessage();

    // Pertahankan input pengguna di form.
    $artikel['judul'] = $judul;
    $artikel['isi_berita'] = $isi;
    $artikel['kategori_id'] = $kategoriId;
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
      <h2>Edit Artikel</h2>
      <p>Perbarui informasi dan konten artikel NewsPortal.</p>
    </div>

    <a href="index.php" class="admin-secondary-button">
      <i class="bi bi-arrow-left"></i>
      Kembali
    </a>
  </div>

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
            class="form-control admin-form-control"><?= e($artikel['isi_berita']); ?></textarea>

          <small class="admin-form-help">
            Gunakan tombol Media Embed pada toolbar untuk menambahkan video YouTube atau MP4.
          </small>
        </div>

        <!-- Thumbnail lama -->
        <div class="admin-form-group">
          <label>Thumbnail Saat Ini</label>

          <?php if (!empty($artikel['thumbnail'])): ?>

            <div class="article-current-thumbnail">
              <img
                src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($artikel['thumbnail'])); ?>"
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