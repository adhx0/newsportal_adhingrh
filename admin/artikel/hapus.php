
<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// CEK LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// URL halaman artikel
$artikelUrl = BASE_URL . '/admin/artikel/index.php';

// Lokasi folder uploads
$folderUpload = __DIR__ . '/../../uploads/';

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
  // AMBIL DATA THUMBNAIL
  $query = $koneksi->prepare(
    "SELECT thumbnail FROM artikel WHERE id = :id"
  );

  $query->execute([
    ':id' => $id
  ]);

  $artikel = $query->fetch(PDO::FETCH_ASSOC);

  // CEK ARTIKEL
  if (!$artikel) {
    header("Location: " . $artikelUrl . "?pesan=tidak_ditemukan");
    exit;
  }

  // HAPUS DATA ARTIKEL
  $hapus = $koneksi->prepare(
    "DELETE FROM artikel WHERE id = :id"
  );

  $hapus->execute([
    ':id' => $id
  ]);

  // HAPUS THUMBNAIL JIKA DATA BERHASIL DIHAPUS
  if (!empty($artikel['thumbnail'])) {
    $namaFile = basename($artikel['thumbnail']);
    $lokasi = $folderUpload . $namaFile;

    if (is_file($lokasi)) {
      if (!unlink($lokasi)) {
        error_log("Gagal menghapus thumbnail: " . $lokasi);
      }
    }
  }

  header("Location: " . $artikelUrl . "?pesan=hapus");
  exit;
} catch (PDOException $e) {
  error_log($e->getMessage());

  header("Location: " . $artikelUrl . "?pesan=gagal");
  exit;
}
