<?php

session_start();

require_once "../config/koneksi.php";

$error = "";

if (isset($_POST['login'])) {

  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  try {

    $query = $koneksi->prepare(
      "SELECT * FROM users WHERE email = :email LIMIT 1"
    );

    $query->execute([
      ':email' => $email
    ]);

    $data = $query->fetch(PDO::FETCH_ASSOC);

    if ($data) {

      if (password_verify($password, $data['password'])) {

        session_regenerate_id(true);

        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        if ($data['role'] === 'super_admin') {

          header("Location: ../admin/dashboard.php");
          exit;
        } elseif ($data['role'] === 'admin') {

          header("Location: ../admin/index.php");
          exit;
        } elseif ($data['role'] === 'user') {

          header("Location: ../admin/index.php");
          exit;
        } else {

          session_unset();
          session_destroy();
          $error = "Role akun tidak memiliki akses.";
        }
      } else {
        $error = "Password yang kamu masukkan salah.";
      }
    } else {
      $error = "Email tidak ditemukan.";
    }
  } catch (PDOException $e) {

    error_log($e->getMessage());

    $error = "Terjadi kesalahan saat mengakses database.";
  }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login Admin | NewsPortal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="../assets/css/style.css?v=2">

</head>

<body class="login-page">

  <main class="login-wrapper">

    <div class="login-card">

      <div class="login-brand">

        <div class="login-brand-icon">
          <i class="bi bi-newspaper"></i>
        </div>

        <span>News<span>Portal</span></span>

      </div>

      <div class="login-heading">

        <h1>Selamat Datang</h1>

        <p>
          Silakan masuk untuk mengakses dashboard NewsPortal.
        </p>

      </div>

      <?php if (!empty($error)) : ?>

        <div class="login-alert">
          <i class="bi bi-exclamation-circle-fill"></i>

          <span>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </div>

      <?php endif; ?>

      <form method="POST">

        <div class="login-form-group">

          <label for="email">Alamat Email</label>

          <div class="login-input-wrapper">

            <i class="bi bi-envelope"></i>

            <input
              type="email"
              id="email"
              name="email"
              class="login-input"
              placeholder="nama@email.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
              autocomplete="username"
              required>

          </div>

        </div>

        <div class="login-form-group">

          <label for="password">Password</label>

          <div class="login-input-wrapper">

            <i class="bi bi-lock"></i>

            <input
              type="password"
              id="password"
              name="password"
              class="login-input"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required>

            <button
              type="button"
              class="login-toggle-password"
              id="togglePassword"
              aria-label="Tampilkan password">

              <i class="bi bi-eye" id="passwordIcon"></i>

            </button>

          </div>

        </div>

        <button
          type="submit"
          name="login"
          class="login-button">

          <span>Masuk ke Dashboard</span>

          <i class="bi bi-arrow-right"></i>

        </button>

      </form>

      <div class="login-back">

        <a href="../index.php">
          <i class="bi bi-arrow-left"></i>
          Kembali ke Beranda
        </a>

      </div>

    </div>

    <div class="login-copyright">
      &copy; <?= date('Y'); ?> NewsPortal. All rights reserved.
    </div>

  </main>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    togglePassword.addEventListener('click', function() {

      const isPassword = passwordInput.type === 'password';

      passwordInput.type = isPassword ? 'text' : 'password';

      passwordIcon.classList.toggle('bi-eye', !isPassword);
      passwordIcon.classList.toggle('bi-eye-slash', isPassword);

      togglePassword.setAttribute(
        'aria-label',
        isPassword ? 'Sembunyikan password' : 'Tampilkan password'
      );

    });
  </script>

</body>

</html>