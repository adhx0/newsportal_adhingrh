
<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// VALIDASI LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

// VALIDASI HAK AKSES
$role = $_SESSION['role'];

if (!in_array($role, ['super_admin', 'admin'], true)) {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

// HANYA MENERIMA REQUEST POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: " . BASE_URL . "/admin/komentar/index.php");
  exit;
}

// VALIDASI CSRF
$csrfToken = $_POST['csrf_token'] ?? '';

if (
  empty($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
  header("Location: " . BASE_URL . "/admin/komentar/index.php");
  exit;
}

// VALIDASI ID KOMENTAR
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
  header("Location: " . BASE_URL . "/admin/komentar/index.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

try {

  // HAPUS KOMENTAR
  $query = $koneksi->prepare(
    "DELETE FROM komentar WHERE id = :id"
  );

  $query->execute([
    ':id' => $id
  ]);

  header("Location: " . BASE_URL . "/admin/komentar/index.php");
  exit;
} catch (PDOException $e) {

  // Jika terjadi kesalahan database
  error_log("Gagal menghapus komentar: " . $e->getMessage());

  header("Location: " . BASE_URL . "/admin/komentar/index.php");
  exit;
}
