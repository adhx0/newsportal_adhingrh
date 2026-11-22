<?php

const THUMBNAIL_MAKS_BYTE = 5 * 1024 * 1024;
const THUMBNAIL_MAKS_LEBAR = 1280;

/**
 * Validasi dan simpan thumbnail artikel, lalu kembalikan nama file barunya.
 * Gambar yang lebih lebar dari THUMBNAIL_MAKS_LEBAR otomatis diperkecil
 * agar halaman publik lebih ringan.
 *
 * @throws RuntimeException jika file tidak valid.
 */
function simpanThumbnail(array $file, string $folder): string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Thumbnail gagal diunggah.');
  }

  if ($file['size'] <= 0 || $file['size'] > THUMBNAIL_MAKS_BYTE) {
    throw new RuntimeException('Ukuran thumbnail maksimal 5 MB.');
  }

  $ekstensi = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

  if (!isset($ekstensi[$mime])) {
    throw new RuntimeException('Format thumbnail harus JPG, PNG, atau WEBP.');
  }

  $info = getimagesize($file['tmp_name']);

  if ($info === false) {
    throw new RuntimeException('File yang diunggah bukan gambar valid.');
  }

  if (!is_dir($folder) || !is_writable($folder)) {
    throw new RuntimeException('Folder uploads tidak tersedia atau tidak dapat ditulis.');
  }

  $nama = 'artikel_' . bin2hex(random_bytes(16)) . '.' . $ekstensi[$mime];
  $tujuan = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . $nama;

  if (!perkecilGambar($file['tmp_name'], $tujuan, $mime, $info) &&
    !move_uploaded_file($file['tmp_name'], $tujuan)
  ) {
    throw new RuntimeException('Gagal menyimpan thumbnail.');
  }

  return $nama;
}

/**
 * Perkecil gambar ke lebar maksimum. Mengembalikan false bila tidak perlu
 * (sudah kecil / GD tidak ada), sehingga file asli dipindahkan apa adanya.
 */
function perkecilGambar(string $sumber, string $tujuan, string $mime, array $info): bool
{
  if (!extension_loaded('gd') || $info[0] <= THUMBNAIL_MAKS_LEBAR) {
    return false;
  }

  if ($mime === 'image/jpeg') {
    $gambar = @imagecreatefromjpeg($sumber);
  } elseif ($mime === 'image/png') {
    $gambar = @imagecreatefrompng($sumber);
  } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
    $gambar = @imagecreatefromwebp($sumber);
  } else {
    return false;
  }

  if (!$gambar) {
    return false;
  }

  // Foto dari HP menyimpan arah rotasi di EXIF; GD mengabaikannya.
  if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
    $exif = @exif_read_data($sumber);
    $sudut = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;

    if ($sudut !== 0 && ($diputar = imagerotate($gambar, $sudut, 0))) {
      $gambar = $diputar;
    }
  }

  $kecil = imagescale($gambar, THUMBNAIL_MAKS_LEBAR);

  if (!$kecil) {
    return false;
  }

  if ($mime !== 'image/jpeg') {
    imagealphablending($kecil, false);
    imagesavealpha($kecil, true);
  }

  if ($mime === 'image/jpeg') {
    return imagejpeg($kecil, $tujuan, 82);
  }

  return $mime === 'image/png'
    ? imagepng($kecil, $tujuan, 6)
    : imagewebp($kecil, $tujuan, 82);
}

/** Hapus file di folder uploads (aman dari path traversal). */
function hapusFileUpload(?string $nama, string $folder): void
{
  if (empty($nama)) {
    return;
  }

  $file = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR . basename(str_replace('\\', '/', $nama));

  if (is_file($file) && !unlink($file)) {
    error_log('Gagal menghapus file: ' . $file);
  }
}
