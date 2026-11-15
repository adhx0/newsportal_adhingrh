
<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// CEK LOGIN DAN ROLE
if (
  !isset($_SESSION['role']) ||
  !in_array($_SESSION['role'], ['super_admin', 'admin', 'user'], true)
) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// URL HALAMAN ARTIKEL
$artikelUrl = BASE_URL . '/admin/artikel/index.php';

// LOKASI FOLDER UPLOADS
$folderUpload = realpath(__DIR__ . '/../../uploads');

// HANYA MENERIMA REQUEST POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . $artikelUrl);
  exit;
}

// VALIDASI ID ARTIKEL
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: " . $artikelUrl . "?pesan=id_tidak_valid");
  exit;
}

try {
  // AMBIL DATA ARTIKEL
  $query = $koneksi->prepare(
    "SELECT thumbnail FROM artikel WHERE id = :id"
  );

  $query->execute([
    ':id' => $id
  ]);

  $artikel = $query->fetch(PDO::FETCH_ASSOC);

  if (!$artikel) {
    header("Location: " . $artikelUrl . "?pesan=tidak_ditemukan");
    exit;
  }

  // SIMPAN NAMA THUMBNAIL
  $thumbnail = $artikel['thumbnail'] ?? '';

  // HAPUS DATA ARTIKEL
  $hapus = $koneksi->prepare(
    "DELETE FROM artikel WHERE id = :id"
  );

  $hapus->execute([
    ':id' => $id
  ]);

  // PASTIKAN DATA BENAR-BENAR TERHAPUS
  if ($hapus->rowCount() > 0) {

    // HAPUS FILE THUMBNAIL
    if (!empty($thumbnail) && $folderUpload !== false) {

      // Ambil nama file saja
      $namaFile = basename(str_replace('\\', '/', $thumbnail));

      $lokasiFile = $folderUpload . DIRECTORY_SEPARATOR . $namaFile;

      // Pastikan file berada di dalam folder uploads
      $realFile = realpath($lokasiFile);

      if (
        $realFile !== false &&
        is_file($realFile) &&
        dirname($realFile) === $folderUpload
      ) {
        if (!unlink($realFile)) {
          error_log("Gagal menghapus thumbnail: " . $realFile);
        }
      }
    }

    header("Location: " . $artikelUrl . "?pesan=hapus");
    exit;
  }

  header("Location: " . $artikelUrl . "?pesan=gagal");
  exit;
} catch (PDOException $e) {

  error_log("Error hapus artikel: " . $e->getMessage());

  header("Location: " . $artikelUrl . "?pesan=gagal");
  exit;
}
