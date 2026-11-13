
<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// VALIDASI LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// URL HALAMAN KATEGORI
$kategoriUrl = BASE_URL . '/admin/kategori/index.php';

// HANYA MENERIMA REQUEST POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . $kategoriUrl);
  exit;
}

// VALIDASI ID KATEGORI
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: " . $kategoriUrl . "?pesan=id_tidak_valid");
  exit;
}

try {

  // CEK DATA KATEGORI
  $cek = $koneksi->prepare(
    "SELECT id FROM kategori WHERE id = :id"
  );

  $cek->execute([
    ':id' => $id
  ]);

  if (!$cek->fetch(PDO::FETCH_ASSOC)) {
    header("Location: " . $kategoriUrl . "?pesan=tidak_ditemukan");
    exit;
  }

  // HAPUS KATEGORI
  $hapus = $koneksi->prepare(
    "DELETE FROM kategori WHERE id = :id"
  );

  $hapus->execute([
    ':id' => $id
  ]);

  header("Location: " . $kategoriUrl . "?pesan=hapus");
  exit;
} catch (PDOException $e) {

  error_log(
    "Gagal menghapus kategori: " . $e->getMessage()
  );

  header("Location: " . $kategoriUrl . "?pesan=gagal");
  exit;
}
