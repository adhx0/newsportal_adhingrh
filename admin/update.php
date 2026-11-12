
<?php
session_start();

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Hanya Super Admin yang boleh mengedit user
if ($_SESSION['role'] !== 'super_admin') {
  header("Location: index.php");
  exit;
}

require_once "../config/koneksi.php";

// Ambil input
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');

// Validasi input
if (
  !$id ||
  $id < 1 ||
  $nama === '' ||
  $email === '' ||
  $role === ''
) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Validasi role
if (!in_array($role, ['admin', 'super_admin', 'user'], true)) {
  header("Location: user.php?pesan=gagal");
  exit;
}

try {

  // Update data pengguna
  $query = $koneksi->prepare(
    "UPDATE users
    SET nama = :nama,
        email = :email,
        role = :role
    WHERE id = :id"
  );

  $query->execute([
    ':nama' => $nama,
    ':email' => $email,
    ':role' => $role,
    ':id' => $id
  ]);

  header("Location: user.php?pesan=edit");
  exit;
} catch (PDOException $e) {

  error_log(
    "Gagal memperbarui pengguna: " . $e->getMessage()
  );

  header("Location: user.php?pesan=gagal");
  exit;
}
