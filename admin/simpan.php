
<?php
session_start();

// Validasi login
if (!isset($_SESSION['role'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Hanya Super Admin yang boleh menambahkan user
if ($_SESSION['role'] !== 'super_admin') {
  header("Location: index.php");
  exit;
}

require_once "../config/koneksi.php";

// Ambil input
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$passwordInput = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

// Validasi input kosong
if (
  $nama === '' ||
  $email === '' ||
  $passwordInput === '' ||
  $role === ''
) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Validasi role
if (!in_array($role, ['admin', 'super_admin', 'user'], true)) {
  header("Location: user.php?pesan=gagal");
  exit;
}

// Hash password
$password = password_hash(
  $passwordInput,
  PASSWORD_DEFAULT
);

try {

  // Simpan pengguna
  $query = $koneksi->prepare(
    "INSERT INTO users (nama, email, password, role)
     VALUES (:nama, :email, :password, :role)"
  );

  $query->execute([
    ':nama' => $nama,
    ':email' => $email,
    ':password' => $password,
    ':role' => $role
  ]);

  header("Location: user.php?pesan=tambah");
  exit;
} catch (PDOException $e) {

  // Catat error database
  error_log(
    "Gagal menambahkan pengguna: " . $e->getMessage()
  );

  header("Location: user.php?pesan=gagal");
  exit;
}
