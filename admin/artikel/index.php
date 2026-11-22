<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/koneksi.php';

wajibLogin();

$role = $_SESSION['role'];
$userId = (int) ($_SESSION['id'] ?? 0);

// Daftar artikel tidak menampilkan isi berita, jadi kolom isi_berita tidak diambil.
// Role "user" hanya melihat artikel miliknya sendiri.
$sql = "SELECT artikel.id, artikel.judul, artikel.thumbnail, artikel.tanggal,
               kategori.nama_kategori, users.nama
        FROM artikel
        LEFT JOIN kategori ON artikel.kategori_id = kategori.id
        LEFT JOIN users ON artikel.user_id = users.id"
  . ($role === 'user' ? " WHERE artikel.user_id = :user_id" : "")
  . " ORDER BY artikel.id DESC";

$query = $koneksi->prepare($sql);
$query->execute($role === 'user' ? [':user_id' => $userId] : []);

$artikels = $query->fetchAll();
$totalArtikel = count($artikels);

include "../header.php";
include "../sidebar.php";
?>

<div class="admin-page article-page">

  <!-- Header halaman -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-eyebrow">Konten Website</div>
      <h2>Data Artikel</h2>

      <p>
        <?= $role === 'user'
          ? 'Kelola artikel yang kamu buat.'
          : 'Kelola seluruh artikel yang diterbitkan di NewsPortal.'; ?>
      </p>
    </div>

    <a href="tambah.php" class="admin-primary-button">
      <i class="bi bi-plus-lg"></i>
      Tambah Artikel
    </a>
  </div>

  <!-- Ringkasan -->
  <div class="user-summary-card article-summary-card">
    <div class="user-summary-icon">
      <i class="bi bi-newspaper"></i>
    </div>

    <div>
      <span>
        <?= $role === 'user' ? 'Artikel Saya' : 'Total Artikel'; ?>
      </span>

      <h3><?= $totalArtikel; ?></h3>

      <small>
        <?= $role === 'user'
          ? 'Artikel yang kamu buat'
          : 'Artikel tersimpan di database'; ?>
      </small>
    </div>
  </div>

  <!-- Tabel artikel -->
  <div class="admin-table-card">

    <div class="admin-table-header">
      <div>
        <h5>Daftar Artikel</h5>

        <p>
          <?= $role === 'user'
            ? 'Informasi artikel yang kamu buat.'
            : 'Informasi artikel yang tersedia di website.'; ?>
        </p>
      </div>

      <span class="admin-table-count">
        <?= $totalArtikel; ?> Artikel
      </span>
    </div>

    <div class="table-responsive">
      <table class="table admin-data-table article-data-table align-middle">

        <thead>
          <tr>
            <th width="55">No.</th>
            <th width="125">Thumbnail</th>
            <th>Judul Artikel</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th>Tanggal</th>
            <th width="125">Aksi</th>
          </tr>
        </thead>

        <tbody>

          <?php if ($totalArtikel > 0): ?>

            <?php foreach ($artikels as $index => $row): ?>

              <tr>
                <td class="text-secondary">
                  <?= $index + 1; ?>
                </td>

                <td>
                  <?php if (!empty($row['thumbnail'])): ?>

                    <img
                      src="<?= BASE_URL ?>/uploads/<?= rawurlencode(basename($row['thumbnail'])); ?>"
                      class="article-thumbnail"
                      alt="Thumbnail artikel"
                      loading="lazy">

                  <?php else: ?>

                    <div class="article-thumbnail-placeholder">
                      <i class="bi bi-image"></i>
                    </div>

                  <?php endif; ?>
                </td>

                <td>
                  <div class="article-title-cell">
                    <?= e($row['judul']); ?>
                  </div>
                </td>

                <td>
                  <span class="article-category-badge">
                    <?= e($row['nama_kategori'] ?: 'Tanpa Kategori'); ?>
                  </span>
                </td>

                <td>
                  <div class="article-author-cell">
                    <i class="bi bi-person-circle"></i>
                    <?= e($row['nama'] ?: 'Tidak diketahui'); ?>
                  </div>
                </td>

                <td>
                  <span class="article-date-cell">
                    <?= !empty($row['tanggal'])
                      ? e(date('d F Y, H:i', strtotime($row['tanggal'])))
                      : '-'; ?>
                  </span>
                </td>

                <td>
                  <div class="user-action-buttons">

                    <!-- EDIT ARTIKEL -->
                    <a
                      href="edit.php?id=<?= (int)$row['id']; ?>"
                      class="user-action-button action-edit"
                      title="Edit artikel">

                      <i class="bi bi-pencil-square"></i>
                    </a>

                    <!-- HAPUS ARTIKEL -->
                    <form
                      action="hapus.php"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">

                      <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$row['id']; ?>">

                      <button
                        type="submit"
                        class="user-action-button action-delete"
                        title="Hapus artikel">

                        <i class="bi bi-trash3"></i>
                      </button>

                    </form>

                  </div>
                </td>
              </tr>

            <?php endforeach; ?>

          <?php else: ?>

            <tr>
              <td colspan="7">
                <div class="user-empty-state">

                  <i class="bi bi-newspaper"></i>

                  <h5>Belum Ada Artikel</h5>

                  <p>
                    <?= $role === 'user'
                      ? 'Kamu belum memiliki artikel. Silakan tambahkan artikel baru.'
                      : 'Belum ada artikel yang tersimpan. Silakan tambahkan artikel baru.'; ?>
                  </p>

                  <a href="tambah.php" class="admin-primary-button">
                    <i class="bi bi-plus-lg"></i>
                    Tambah Artikel
                  </a>

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