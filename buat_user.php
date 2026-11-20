
<?php

require_once __DIR__ . "/config/koneksi.php";

// DATA USER
$nama     = "super_admin";
$email    = "super_admin@gmail.com";
$password = "12345";
$role     = "super_admin";

try {

    // CEK APAKAH EMAIL SUDAH TERDAFTAR
    $cek = $koneksi->prepare(
        "SELECT id FROM users WHERE email = :email"
    );

    $cek->execute([
        ':email' => $email
    ]);

    if ($cek->fetch()) {

        die("User dengan email tersebut sudah terdaftar.");

    }

    // HASH PASSWORD
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // INSERT USER
    $query = $koneksi->prepare("
        INSERT INTO users (nama, email, password, role)
        VALUES (:nama, :email, :password, :role)
    ");

    $query->execute([
        ':nama'     => $nama,
        ':email'    => $email,
        ':password' => $password_hash,
        ':role'     => $role
    ]);

    echo "User super_admin berhasil dibuat!";

    echo "<br><br>";
    echo "Email: " . htmlspecialchars($email);
    echo "<br>";
    echo "Password: " . htmlspecialchars($password);
    echo "<br>";
    echo "Role: " . htmlspecialchars($role);

} catch (PDOException $e) {

    die("Terjadi kesalahan: " . $e->getMessage());

}
