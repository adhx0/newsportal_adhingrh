
<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

// Hanya Super Admin yang boleh menghapus user
if ($_SESSION['role'] !== 'super_admin') {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

// URL halaman pengelolaan user
$userUrl = BASE_URL . '/admin/user.php';

// Ambil ID dari POST
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  header("Location: " . $userUrl . "?pesan=gagal");
  exit;
}

// Mencegah Super Admin menghapus akun sendiri
if (isset($_SESSION['id']) && (int) $_SESSION['id'] === $id) {
  header("Location: " . $userUrl . "?pesan=gagal");
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
    header("Location: " . $userUrl . "?pesan=hapus");
  } else {
    header("Location: " . $userUrl . "?pesan=gagal");
  }

  exit;
} catch (PDOException $e) {

  error_log(
    "Gagal menghapus pengguna: " . $e->getMessage()
  );

  header("Location: " . $userUrl . "?pesan=gagal");
  exit;
}
