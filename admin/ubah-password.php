<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

// VALIDASI LOGIN
if (!isset($_SESSION['id'], $_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

$pesan = '';
$jenisPesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $passwordLama = $_POST['password_lama'] ?? '';
  $passwordBaru = $_POST['password_baru'] ?? '';
  $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';
  $csrfToken = $_POST['csrf_token'] ?? '';

  if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
  ) {
    $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    $jenisPesan = 'danger';
  } elseif (
    $passwordLama === '' ||
    $passwordBaru === '' ||
    $konfirmasiPassword === ''
  ) {
    $pesan = 'Semua kolom password wajib diisi.';
    $jenisPesan = 'danger';
  } elseif (strlen($passwordBaru) < 8) {
    $pesan = 'Password baru minimal 8 karakter.';
    $jenisPesan = 'danger';
  } elseif ($passwordBaru !== $konfirmasiPassword) {
    $pesan = 'Konfirmasi password baru tidak cocok.';
    $jenisPesan = 'danger';
  } elseif ($passwordLama === $passwordBaru) {
    $pesan = 'Password baru tidak boleh sama dengan password lama.';
    $jenisPesan = 'danger';
  } else {

    // AMBIL PASSWORD PENGGUNA SAAT INI
    $query = $koneksi->prepare(
      "SELECT password FROM users WHERE id = :id LIMIT 1"
    );

    $query->execute([
      ':id' => (int) $_SESSION['id']
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($passwordLama, $user['password'])) {

      $pesan = 'Password lama yang kamu masukkan salah.';
      $jenisPesan = 'danger';
    } else {

      // HASH PASSWORD BARU
      $passwordHash = password_hash(
        $passwordBaru,
        PASSWORD_DEFAULT
      );

      // UPDATE PASSWORD
      $update = $koneksi->prepare(
        "UPDATE users
                 SET password = :password
                 WHERE id = :id"
      );

      $update->execute([
        ':password' => $passwordHash,
        ':id' => (int) $_SESSION['id']
      ]);

      $pesan = 'Password berhasil diperbarui.';
      $jenisPesan = 'success';

      // RESET TOKEN CSRF
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
  }
}

// BUAT TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "header.php";
include "sidebar.php";
?>

<div class="admin-page">

  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Pengaturan Akun</div>
      <h2>Ubah Password</h2>
      <p>Perbarui password akun untuk menjaga keamanan akun NewsPortal.</p>
    </div>
  </div>

  <div class="admin-table-card" style="max-width: 650px;">

    <div class="admin-table-header">
      <div>
        <h5>Ganti Password Akun</h5>
        <p>Masukkan password lama dan password baru kamu.</p>
      </div>
    </div>

    <div class="p-4">

      <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= e($jenisPesan); ?>" role="alert">
          <?= e($pesan); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">

        <input
          type="hidden"
          name="csrf_token"
          value="<?= e($_SESSION['csrf_token']); ?>">

        <div class="mb-3">
          <label class="form-label">Password Lama</label>
          <input
            type="password"
            name="password_lama"
            class="form-control"
            placeholder="Masukkan password lama"
            autocomplete="current-password"
            required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password Baru</label>
          <input
            type="password"
            name="password_baru"
            class="form-control"
            placeholder="Minimal 8 karakter"
            minlength="8"
            autocomplete="new-password"
            required>
        </div>

        <div class="mb-4">
          <label class="form-label">Konfirmasi Password Baru</label>
          <input
            type="password"
            name="konfirmasi_password"
            class="form-control"
            placeholder="Ulangi password baru"
            minlength="8"
            autocomplete="new-password"
            required>
        </div>

        <button
          type="submit"
          class="admin-primary-button border-0">

          <i class="bi bi-shield-lock"></i>
          Simpan Password
        </button>

      </form>

    </div>
  </div>

</div>

<?php include "footer.php"; ?>