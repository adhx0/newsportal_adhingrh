<?php

$host = "aws-1-ap-southeast-2.pooler.supabase.com";
$port = "5432";
$db   = "postgres";
$user = "postgres.awxfqgkjbmkwnlorjqqu";
$pass = "newsportal_db1234";

try {

    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

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
