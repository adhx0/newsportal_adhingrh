
<?php
session_start();

// Kosongkan seluruh data session
$_SESSION = [];

// Hapus session
session_destroy();

// Kembali ke halaman login
header("Location: ../auth/login.php");
exit;
?>
