
<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

// Hanya Super Admin yang boleh reset password
if ($_SESSION['role'] !== 'super_admin') {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . BASE_URL . "/admin/user.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

// Validasi CSRF
$csrfToken = $_POST['csrf_token'] ?? '';

if (
  empty($_SESSION['csrf_token']) ||
  !is_string($csrfToken) ||
  !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

// Ambil input
$id = filter_var(
  $_POST['id'] ?? null,
  FILTER_VALIDATE_INT
);

$passwordBaru = $_POST['password_baru'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

// Validasi ID
if (!$id || $id <= 0) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

// Validasi input password
if (
  !is_string($passwordBaru) ||
  !is_string($konfirmasiPassword) ||
  $passwordBaru === '' ||
  $konfirmasiPassword === ''
) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

// Minimal 8 karakter
if (strlen($passwordBaru) < 8) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

// Maksimal panjang password
if (strlen($passwordBaru) > 4096) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

// Validasi konfirmasi password
if ($passwordBaru !== $konfirmasiPassword) {
  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}

try {

  // Periksa apakah pengguna tersedia
  $cekUser = $koneksi->prepare(
    "SELECT id FROM users WHERE id = :id LIMIT 1"
  );

  $cekUser->execute([
    ':id' => $id
  ]);

  if (!$cekUser->fetch(PDO::FETCH_ASSOC)) {
    header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
    exit;
  }

  // Hash password baru
  $passwordHash = password_hash(
    $passwordBaru,
    PASSWORD_DEFAULT
  );

  if ($passwordHash === false) {
    throw new RuntimeException("Gagal mengenkripsi password.");
  }

  // Update password pengguna
  $query = $koneksi->prepare(
    "UPDATE users
        SET password = :password
        WHERE id = :id"
  );

  $query->execute([
    ':password' => $passwordHash,
    ':id' => $id
  ]);

  // Berhasil
  header("Location: " . BASE_URL . "/admin/user.php?pesan=reset");
  exit;
} catch (Throwable $e) {

  // Catat error untuk pemeriksaan administrator
  error_log("Reset password error: " . $e->getMessage());

  header("Location: " . BASE_URL . "/admin/user.php?pesan=gagal");
  exit;
}
