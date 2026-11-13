
<?php
session_start();

require_once __DIR__ . '/../config/base_url.php';

// Kosongkan seluruh data session
$_SESSION = [];

// Hapus session
session_destroy();

// Kembali ke halaman login
header("Location: " . BASE_URL . "/auth/login.php");
exit;
?>
