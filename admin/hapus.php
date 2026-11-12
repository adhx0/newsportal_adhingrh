
<?php
session_start();

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Hanya Super Admin yang boleh menghapus user
if ($_SESSION['role'] !== 'super_admin') {
  header("Location: index.php");
  exit;
}

require_once "../config/koneksi.php";

// Ambil ID dari POST
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Mencegah Super Admin menghapus akun sendiri
if (isset($_SESSION['id']) && (int) $_SESSION['id'] === $id) {
  header("Location: user.php?pesan=gagal");
  exit;
}

try {

  $query = $koneksi->prepare(
    "DELETE FROM users WHERE id = :id"
  );

  $query->execute([
    ':id' => $id
  ]);

  if ($query->rowCount() > 0) {
    header("Location: user.php?pesan=hapus");
  } else {
    header("Location: user.php?pesan=gagal");
  }

  exit;
} catch (PDOException $e) {

  error_log(
    "Gagal menghapus pengguna: " . $e->getMessage()
  );

  header("Location: user.php?pesan=gagal");
  exit;
}
