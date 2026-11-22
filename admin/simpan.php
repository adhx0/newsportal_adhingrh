<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

wajibLogin(['super_admin']);

const URL_USER = '/admin/user.php';

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (
  $nama === '' || $password === '' ||
  !filter_var($email, FILTER_VALIDATE_EMAIL) ||
  !in_array($role, ROLE_SEMUA, true)
) {
  redirect(URL_USER, ['pesan' => 'gagal']);
}

try {
  $koneksi->prepare(
    "INSERT INTO users (nama, email, password, role)
     VALUES (:nama, :email, :password, :role)"
  )->execute([
    ':nama'     => $nama,
    ':email'    => $email,
    ':password' => password_hash($password, PASSWORD_DEFAULT),
    ':role'     => $role,
  ]);

  redirect(URL_USER, ['pesan' => 'tambah']);
} catch (PDOException $e) {
  error_log('Gagal menambahkan pengguna: ' . $e->getMessage());
  redirect(URL_USER, ['pesan' => 'gagal']);
}
