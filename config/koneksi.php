<?php

$host = 'localhost';
$port = '3306';
$db   = 'newsportal_db';
$user = 'root';
$pass = '';

try {
  $koneksi = new PDO(
    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  // Detail error hanya ke log server, jangan ditampilkan ke pengunjung.
  error_log('Koneksi database gagal: ' . $e->getMessage());
  http_response_code(500);
  exit('Terjadi kesalahan pada server.');
}
