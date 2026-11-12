
<?php
session_start();

// CEK LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: ../../auth/login.php");
  exit;
}

require_once "../../config/koneksi.php";

// HANYA MENERIMA REQUEST POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: index.php");
  exit;
}

// VALIDASI ID ARTIKEL
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: index.php?pesan=id_tidak_valid");
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
    header("Location: index.php?pesan=tidak_ditemukan");
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
    $lokasi = "../../uploads/" . $namaFile;

    if (is_file($lokasi)) {
      if (!unlink($lokasi)) {
        error_log("Gagal menghapus thumbnail: " . $lokasi);
      }
    }
  }

  header("Location: index.php?pesan=hapus");
  exit;
} catch (PDOException $e) {
  error_log($e->getMessage());

  header("Location: index.php?pesan=gagal");
  exit;
}
