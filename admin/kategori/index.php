<?php
session_start();

require_once __DIR__ . '/../../config/base_url.php';

// VALIDASI LOGIN
if (!isset($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/auth/login.php");
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

// AMBIL DATA KATEGORI
$query = $koneksi->query(
  "SELECT id, nama_kategori
     FROM kategori
     ORDER BY id DESC"
);

$kategori = $query->fetchAll(PDO::FETCH_ASSOC);

$totalKategori = count($kategori);

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page category-page">

  <!-- HEADER HALAMAN -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Manajemen Konten</div>
      <h2>Data Kategori</h2>
      <p>Kelola kategori untuk mengelompokkan artikel NewsPortal.</p>
    </div>

    <a href="tambah.php" class="admin-primary-button">
      <i class="bi bi-plus-lg"></i>
      Tambah Kategori
    </a>
  </div>

  <!-- RINGKASAN -->
  <div class="category-summary-card">
    <div class="category-summary-icon">
      <i class="bi bi-tags"></i>
    </div>

    <div>
      <span>Total Kategori</span>
      <h3><?= $totalKategori; ?></h3>
    </div>
  </div>

  <!-- TABEL KATEGORI -->
  <div class="admin-table-card">

    <div class="admin-table-header">
      <div>
        <h5>Daftar Kategori</h5>
        <p>Daftar kategori yang tersedia di website.</p>
      </div>

      <span class="admin-table-count">
        <?= $totalKategori; ?> Kategori
      </span>
    </div>

    <div class="table-responsive">
      <table class="table admin-data-table category-data-table">

        <thead>
          <tr>
            <th width="80">No</th>
            <th>Nama Kategori</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>

        <tbody>

          <?php if (!empty($kategori)): ?>

            <?php $no = 1; ?>

            <?php foreach ($kategori as $row): ?>

              <tr>
                <td>
                  <span class="category-number">
                    <?= $no++; ?>
                  </span>
                </td>

                <td>
                  <div class="category-name-cell">
                    <div class="category-name-icon">
                      <i class="bi bi-tag"></i>
                    </div>

                    <span>
                      <?= e($row['nama_kategori']); ?>
                    </span>
                  </div>
                </td>

                <td>
                  <div class="user-action-buttons">

                    <a
                      href="edit.php?id=<?= (int)$row['id']; ?>"
                      class="user-action-button edit"
                      title="Edit kategori">
                      <i class="bi bi-pencil-square"></i>
                    </a>

                    <form
                      action="hapus.php"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">

                      <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$row['id']; ?>">

                      <button
                        type="submit"
                        class="user-action-button delete"
                        title="Hapus kategori">
                        <i class="bi bi-trash"></i>
                      </button>

                    </form>

                  </div>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php else: ?>

            <tr>
              <td colspan="3">
                <div class="category-empty-state">
                  <i class="bi bi-folder2-open"></i>
                  <h6>Belum Ada Kategori</h6>
                  <p>
                    Silakan tambahkan kategori pertama
                    untuk mulai mengelompokkan artikel.
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