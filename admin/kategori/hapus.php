
<?php
session_start();

// VALIDASI LOGIN
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

// VALIDASI ID KATEGORI
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: index.php?pesan=id_tidak_valid");
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
    header("Location: index.php?pesan=tidak_ditemukan");
    exit;
  }

  // HAPUS KATEGORI
  $hapus = $koneksi->prepare(
    "DELETE FROM kategori WHERE id = :id"
  );

  $hapus->execute([
    ':id' => $id
  ]);

  header("Location: index.php?pesan=hapus");
  exit;
} catch (PDOException $e) {

  error_log(
    "Gagal menghapus kategori: " . $e->getMessage()
  );

  header("Location: index.php?pesan=gagal");
  exit;
}
