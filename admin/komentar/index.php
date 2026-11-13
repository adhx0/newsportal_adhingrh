<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// VALIDASI LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}

// VALIDASI HAK AKSES
$role = $_SESSION['role'];

if (!in_array($role, ['super_admin', 'admin'], true)) {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

require_once __DIR__ . '/../../config/koneksi.php';

// FUNGSI ESCAPE OUTPUT
function e($value)
{
  return htmlspecialchars(
    (string)($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
}

// TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// AMBIL DATA KOMENTAR
$query = $koneksi->query(
  "SELECT
        komentar.id,
        komentar.nama,
        komentar.email,
        komentar.komentar,
        komentar.tanggal,
        artikel.judul AS judul_artikel
    FROM komentar
    LEFT JOIN artikel
        ON komentar.artikel_id = artikel.id
    ORDER BY komentar.id DESC"
);

$komentarList = $query->fetchAll(PDO::FETCH_ASSOC);

$totalKomentar = count($komentarList);

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page comment-page">

  <!-- HEADER HALAMAN -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Manajemen Konten</div>
      <h2>Kelola Komentar</h2>
      <p>Kelola komentar yang dikirimkan pengunjung pada artikel NewsPortal.</p>
    </div>
  </div>

  <!-- RINGKASAN -->
  <div class="user-summary-card article-summary-card">
    <div class="user-summary-icon">
      <i class="bi bi-chat-left-text"></i>
    </div>

    <div>
      <span>Total Komentar</span>
      <h3><?= $totalKomentar; ?></h3>
      <small>Komentar tersimpan di database</small>
    </div>
  </div>

  <!-- TABEL KOMENTAR -->
  <div class="admin-table-card">

    <div class="admin-table-header">
      <div>
        <h5>Daftar Komentar</h5>
        <p>Daftar komentar dari seluruh artikel yang tersedia.</p>
      </div>

      <span class="admin-table-count">
        <?= $totalKomentar; ?> Komentar
      </span>
    </div>

    <div class="table-responsive">
      <table class="table admin-data-table align-middle">

        <thead>
          <tr>
            <th width="55">No.</th>
            <th width="180">Nama Pengunjung</th>
            <th>Isi Komentar</th>
            <th>Artikel</th>
            <th width="160">Tanggal</th>
            <th width="90">Aksi</th>
          </tr>
        </thead>

        <tbody>

          <?php if ($totalKomentar > 0): ?>

            <?php foreach ($komentarList as $index => $row): ?>

              <tr>
                <td class="text-secondary">
                  <?= $index + 1; ?>
                </td>

                <!-- NAMA PENGUNJUNG -->
                <td>
                  <div class="article-author-cell">
                    <i class="bi bi-person-circle"></i>
                    <?= e($row['nama'] ?: 'Anonim'); ?>
                  </div>

                  <?php if (!empty($row['email'])): ?>
                    <small class="text-secondary">
                      <?= e($row['email']); ?>
                    </small>
                  <?php endif; ?>
                </td>

                <!-- ISI KOMENTAR -->
                <td>
                  <div style="min-width: 180px; max-width: 350px; white-space: normal;">
                    <?= nl2br(e($row['komentar'])); ?>
                  </div>
                </td>

                <!-- ARTIKEL -->
                <td>
                  <span class="article-category-badge">
                    <?= e($row['judul_artikel'] ?: 'Artikel tidak tersedia'); ?>
                  </span>
                </td>

                <!-- TANGGAL -->
                <td>
                  <span class="article-date-cell">
                    <?= e($row['tanggal']); ?>
                  </span>
                </td>

                <!-- AKSI -->
                <td>
                  <div class="user-action-buttons">

                    <form
                      action="hapus.php"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus komentar ini?');">

                      <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$row['id']; ?>">

                      <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']); ?>">

                      <button
                        type="submit"
                        class="user-action-button delete"
                        title="Hapus komentar">

                        <i class="bi bi-trash"></i>
                      </button>

                    </form>

                  </div>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php else: ?>

            <tr>
              <td colspan="6">
                <div class="user-empty-state">

                  <i class="bi bi-chat-left-text"></i>

                  <h5>Belum Ada Komentar</h5>

                  <p>
                    Belum ada komentar yang dikirimkan
                    oleh pengunjung website.
                  </p>

                </div>
              </td>
            </tr>

          <?php endif; ?>

        </tbody>

      </table>
    </div>
  </div>

</div>

<?php include "../footer.php"; ?>