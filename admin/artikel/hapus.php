<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/upload.php';
require_once __DIR__ . '/../../config/koneksi.php';

wajibLogin();

const URL_DAFTAR_ARTIKEL = '/admin/artikel/index.php';

// Hanya menerima request POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect(URL_DAFTAR_ARTIKEL);
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
  redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'id_tidak_valid']);
}

try {
  $stmt = $koneksi->prepare("SELECT user_id, thumbnail FROM artikel WHERE id = :id");
  $stmt->execute([':id' => $id]);
  $artikel = $stmt->fetch();

  // Role "user" hanya boleh menghapus artikel miliknya sendiri.
  if (
    !$artikel ||
    ($_SESSION['role'] === 'user' && (int) $artikel['user_id'] !== (int) ($_SESSION['id'] ?? 0))
  ) {
    redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'tidak_ditemukan']);
  }

  $hapus = $koneksi->prepare("DELETE FROM artikel WHERE id = :id");
  $hapus->execute([':id' => $id]);

  if ($hapus->rowCount() > 0) {
    hapusFileUpload($artikel['thumbnail'], __DIR__ . '/../../uploads');
    redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'hapus']);
  }

  redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'gagal']);
} catch (PDOException $e) {
  error_log('Error hapus artikel: ' . $e->getMessage());
  redirect(URL_DAFTAR_ARTIKEL, ['pesan' => 'gagal']);
}
