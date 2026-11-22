<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

wajibLogin(['super_admin']);

const URL_USER = '/admin/user.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');

if (
  !$id || $id < 1 || $nama === '' ||
  !filter_var($email, FILTER_VALIDATE_EMAIL) ||
  !in_array($role, ROLE_SEMUA, true)
) {
  redirect(URL_USER, ['pesan' => 'gagal']);
}

try {
  $koneksi->prepare(
    "UPDATE users SET nama = :nama, email = :email, role = :role WHERE id = :id"
  )->execute([
    ':nama'  => $nama,
    ':email' => $email,
    ':role'  => $role,
    ':id'    => $id,
  ]);

  redirect(URL_USER, ['pesan' => 'edit']);
} catch (PDOException $e) {
  error_log('Gagal memperbarui pengguna: ' . $e->getMessage());
  redirect(URL_USER, ['pesan' => 'gagal']);
}
