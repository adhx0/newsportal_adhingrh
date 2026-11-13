
<?php

$host = "localhost";
$port = "3306";
$db   = "newsportal_db";
$user = "root";
$pass = "";

try {

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

    $koneksi = new PDO($dsn, $user, $pass);

    $koneksi->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $koneksi->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $e) {

    die("ERROR DATABASE: " . $e->getMessage());
}
