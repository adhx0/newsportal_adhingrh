<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

if ($_SESSION['role'] !== 'super_admin') {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

require_once __DIR__ . '/../config/koneksi.php';

function e($value)
{
  return htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// Ambil data pengguna
$user = $koneksi->query(
  "SELECT * FROM users ORDER BY id DESC"
);

$users = $user->fetchAll(PDO::FETCH_ASSOC);

// ID pengguna yang sedang login
$currentUserId = (int) ($_SESSION['id'] ?? 0);

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Notifikasi
$notifikasi = [
  'tambah' => 'Data pengguna berhasil ditambahkan.',
  'edit'   => 'Data pengguna berhasil diperbarui.',
  'hapus'  => 'Data pengguna berhasil dihapus.',
  'reset'  => 'Password pengguna berhasil direset.',
  'gagal'  => 'Data pengguna gagal diproses.'
];

$pesan = $_GET['pesan'] ?? '';
$pesanValid = isset($notifikasi[$pesan]);

include "header.php";
include "sidebar.php";
?>

<div class="admin-page user-page">

  <!-- PAGE HEADER -->
  <div class="admin-page-header">
    <div>
      <span class="admin-page-eyebrow">MANAJEMEN PENGGUNA</span>
      <h1>Kelola User</h1>
      <p>Kelola akun dan hak akses pengguna NewsPortal.</p>
    </div>

    <button
      type="button"
      class="admin-primary-button"
      data-bs-toggle="modal"
      data-bs-target="#modalTambah">

      <i class="bi bi-plus-lg"></i>
      Tambah User
    </button>
  </div>

  <!-- NOTIFIKASI -->
  <?php if ($pesanValid) : ?>
    <div
      class="admin-notification <?= $pesan === 'gagal' ? 'notification-error' : 'notification-success'; ?>"
      role="alert">

      <i class="bi <?= $pesan === 'gagal' ? 'bi-exclamation-circle' : 'bi-check-circle'; ?>"></i>

      <span><?= e($notifikasi[$pesan]); ?></span>

      <button
        type="button"
        class="notification-close"
        onclick="this.closest('.admin-notification').remove()"
        aria-label="Close">

        <i class="bi bi-x-lg"></i>
      </button>

    </div>
  <?php endif; ?>

  <!-- USER SUMMARY -->
  <div class="user-summary-card">
    <div class="user-summary-icon">
      <i class="bi bi-people"></i>
    </div>

    <div>
      <span>Total Pengguna</span>
      <h2><?= count($users); ?></h2>
    </div>
  </div>

  <!-- USER TABLE -->
  <div class="admin-table-card">

    <div class="admin-table-header">
      <div>
        <h2>Daftar Pengguna</h2>
        <p>Informasi seluruh akun yang terdaftar.</p>
      </div>

      <span class="admin-table-count">
        <?= count($users); ?> Pengguna
      </span>
    </div>

    <div class="table-responsive">
      <table class="table admin-data-table align-middle">

        <thead>
          <tr>
            <th width="7%">No</th>
            <th>Nama</th>
            <th>Email</th>
            <th width="15%">Role</th>
            <th width="18%">Aksi</th>
          </tr>
        </thead>

        <tbody>

          <?php if (count($users) > 0) : ?>

            <?php foreach ($users as $index => $u) : ?>

              <?php
              $userId = (int) $u['id'];
              $isCurrentUser = ($userId === $currentUserId);
              $role = $u['role'];
              ?>

              <tr>
                <td class="user-number">
                  <?= $index + 1; ?>
                </td>

                <td>
                  <div class="user-name-cell">
                    <div class="user-avatar">
                      <?= e(strtoupper(mb_substr($u['nama'], 0, 1))); ?>
                    </div>

                    <span><?= e($u['nama']); ?></span>
                  </div>
                </td>

                <td class="user-email">
                  <?= e($u['email']); ?>
                </td>

                <td>
                  <?php if ($role === 'super_admin') : ?>

                    <span class="user-role-badge role-super_admin">
                      <i class="bi bi-shield-check"></i>
                      Super Admin
                    </span>

                  <?php elseif ($role === 'admin') : ?>

                    <span class="user-role-badge role-admin">
                      <i class="bi bi-person"></i>
                      Admin
                    </span>

                  <?php elseif ($role === 'user') : ?>

                    <span class="user-role-badge role-user">
                      <i class="bi bi-person-circle"></i>
                      User
                    </span>

                  <?php else : ?>

                    <span class="user-role-badge">
                      Tidak Diketahui
                    </span>

                  <?php endif; ?>
                </td>

                <td>
                  <div class="user-action-buttons">

                    <!-- TOMBOL EDIT -->
                    <button
                      type="button"
                      class="user-action-button action-edit"
                      data-bs-toggle="modal"
                      data-bs-target="#edit<?= $userId; ?>"
                      title="Edit pengguna">

                      <i class="bi bi-pencil-square"></i>
                    </button>

                    <!-- TOMBOL RESET PASSWORD -->
                    <button
                      type="button"
                      class="user-action-button action-edit"
                      data-bs-toggle="modal"
                      data-bs-target="#reset<?= $userId; ?>"
                      title="Reset password">

                      <i class="bi bi-key"></i>
                    </button>

                    <!-- TOMBOL HAPUS -->
                    <?php if (!$isCurrentUser) : ?>

                      <form
                        action="hapus.php"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                        style="display: inline;">

                        <input
                          type="hidden"
                          name="id"
                          value="<?= $userId; ?>">

                        <button
                          type="submit"
                          class="user-action-button action-delete"
                          title="Hapus pengguna">

                          <i class="bi bi-trash3"></i>
                        </button>

                      </form>

                    <?php else : ?>

                      <button
                        type="button"
                        class="user-action-button action-delete"
                        disabled
                        title="Tidak dapat menghapus akun sendiri">

                        <i class="bi bi-trash3"></i>
                      </button>

                    <?php endif; ?>

                  </div>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php else : ?>

            <tr>
              <td colspan="5">
                <div class="user-empty-state">
                  <i class="bi bi-people"></i>
                  <h3>Belum Ada Pengguna</h3>
                  <p>Belum ada akun pengguna yang terdaftar.</p>
                </div>
              </td>
            </tr>

          <?php endif; ?>

        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ==================================
     MODAL EDIT USER
================================== -->

<?php foreach ($users as $u) : ?>

  <div
    class="modal fade admin-user-modal"
    id="edit<?= (int) $u['id']; ?>"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

      <div class="modal-content">

        <form action="update.php" method="POST">

          <div class="modal-header">

            <div>
              <h5 class="modal-title">Edit Pengguna</h5>
              <p>Perbarui informasi akun pengguna.</p>
            </div>

            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close">
            </button>

          </div>

          <div class="modal-body">

            <input
              type="hidden"
              name="id"
              value="<?= (int) $u['id']; ?>">

            <div class="admin-form-group">
              <label>Nama Lengkap</label>

              <div class="admin-input-wrapper">
                <i class="bi bi-person"></i>

                <input
                  type="text"
                  name="nama"
                  class="admin-form-control"
                  value="<?= e($u['nama']); ?>"
                  required>
              </div>
            </div>

            <div class="admin-form-group">
              <label>Email</label>

              <div class="admin-input-wrapper">
                <i class="bi bi-envelope"></i>

                <input
                  type="email"
                  name="email"
                  class="admin-form-control"
                  value="<?= e($u['email']); ?>"
                  required>
              </div>
            </div>

            <div class="admin-form-group">
              <label>Role Pengguna</label>

              <select name="role" class="form-select admin-form-select" required>

                <option
                  value="admin"
                  <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>
                  Admin
                </option>

                <option
                  value="super_admin"
                  <?= $u['role'] === 'super_admin' ? 'selected' : ''; ?>>
                  Super Admin
                </option>

                <option
                  value="user"
                  <?= $u['role'] === 'user' ? 'selected' : ''; ?>>
                  User
                </option>

              </select>
            </div>

          </div>

          <div class="modal-footer">

            <button
              type="button"
              class="admin-secondary-button"
              data-bs-dismiss="modal">
              Batal
            </button>

            <button type="submit" class="admin-primary-button">
              <i class="bi bi-check-lg"></i>
              Simpan Perubahan
            </button>

          </div>

        </form>

      </div>
    </div>
  </div>

<?php endforeach; ?>

<!-- ==================================
     MODAL RESET PASSWORD
================================== -->

<?php foreach ($users as $u) : ?>

  <div
    class="modal fade admin-user-modal"
    id="reset<?= (int) $u['id']; ?>"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

      <div class="modal-content">

        <form action="reset-password.php" method="POST">

          <div class="modal-header">

            <div>
              <h5 class="modal-title">Reset Password</h5>
              <p>
                Ubah password untuk akun
                <strong><?= e($u['nama']); ?></strong>.
              </p>
            </div>

            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close">
            </button>

          </div>

          <div class="modal-body">

            <input
              type="hidden"
              name="id"
              value="<?= (int) $u['id']; ?>">

            <input
              type="hidden"
              name="csrf_token"
              value="<?= e($csrfToken); ?>">

            <div class="admin-form-group">
              <label>Password Baru</label>

              <div class="admin-input-wrapper">
                <i class="bi bi-lock"></i>

                <input
                  type="password"
                  name="password_baru"
                  class="admin-form-control"
                  placeholder="Masukkan password baru"
                  minlength="8"
                  required>
              </div>

              <small>Minimal 8 karakter.</small>
            </div>

            <div class="admin-form-group">
              <label>Konfirmasi Password Baru</label>

              <div class="admin-input-wrapper">
                <i class="bi bi-shield-lock"></i>

                <input
                  type="password"
                  name="konfirmasi_password"
                  class="admin-form-control"
                  placeholder="Ulangi password baru"
                  minlength="8"
                  required>
              </div>
            </div>

          </div>

          <div class="modal-footer">

            <button
              type="button"
              class="admin-secondary-button"
              data-bs-dismiss="modal">
              Batal
            </button>

            <button type="submit" class="admin-primary-button">
              <i class="bi bi-key"></i>
              Reset Password
            </button>

          </div>

        </form>

      </div>
    </div>
  </div>

<?php endforeach; ?>

<!-- ==================================
     MODAL TAMBAH USER
================================== -->

<div
  class="modal fade admin-user-modal"
  id="modalTambah"
  tabindex="-1"
  aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

      <form action="simpan.php" method="POST">

        <div class="modal-header">

          <div>
            <h5 class="modal-title">Tambah Pengguna</h5>
            <p>Tambahkan akun baru ke NewsPortal.</p>
          </div>

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close">
          </button>

        </div>

        <div class="modal-body">

          <div class="admin-form-group">
            <label>Nama Lengkap</label>

            <div class="admin-input-wrapper">
              <i class="bi bi-person"></i>

              <input
                type="text"
                name="nama"
                class="admin-form-control"
                placeholder="Masukkan nama lengkap"
                required>
            </div>
          </div>

          <div class="admin-form-group">
            <label>Email</label>

            <div class="admin-input-wrapper">
              <i class="bi bi-envelope"></i>

              <input
                type="email"
                name="email"
                class="admin-form-control"
                placeholder="nama@email.com"
                required>
            </div>
          </div>

          <div class="admin-form-group">
            <label>Password</label>

            <div class="admin-input-wrapper">
              <i class="bi bi-lock"></i>

              <input
                type="password"
                name="password"
                class="admin-form-control"
                placeholder="Masukkan password"
                required>
            </div>
          </div>

          <div class="admin-form-group">
            <label>Role Pengguna</label>

            <select name="role" class="form-select admin-form-select" required>
              <option value="admin">Admin</option>
              <option value="super_admin">Super Admin</option>
              <option value="user">User</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">

          <button
            type="button"
            class="admin-secondary-button"
            data-bs-dismiss="modal">
            Batal
          </button>

          <button type="submit" class="admin-primary-button">
            <i class="bi bi-plus-lg"></i>
            Simpan Pengguna
          </button>

        </div>

      </form>

    </div>
  </div>
</div>

<?php include "footer.php"; ?>