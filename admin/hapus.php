<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

wajibLogin(['super_admin']);

const URL_USER = '/admin/user.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// ID harus valid dan Super Admin tidak boleh menghapus akunnya sendiri.
if (!$id || $id < 1 || (int) ($_SESSION['id'] ?? 0) === $id) {
  redirect(URL_USER, ['pesan' => 'gagal']);
}

try {
  $query = $koneksi->prepare("DELETE FROM users WHERE id = :id");
  $query->execute([':id' => $id]);

  redirect(URL_USER, ['pesan' => $query->rowCount() > 0 ? 'hapus' : 'gagal']);
} catch (PDOException $e) {
  error_log('Gagal menghapus pengguna: ' . $e->getMessage());
  redirect(URL_USER, ['pesan' => 'gagal']);
}
