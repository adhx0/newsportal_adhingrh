CREATE DATABASE IF NOT EXISTS newsportal_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE newsportal_db;

-- ==========================================
-- TABLE: users
-- ==========================================

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),

    PRIMARY KEY (id),
    UNIQUE KEY users_email_key (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================
-- TABLE: kategori
-- ==========================================

CREATE TABLE kategori (
    id INT NOT NULL AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================
-- TABLE: artikel
-- ==========================================

CREATE TABLE artikel (
    id INT NOT NULL AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    isi_berita LONGTEXT NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    media_url VARCHAR(500) DEFAULT NULL,
    kategori_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    tanggal DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),

    PRIMARY KEY (id),

    -- Artikel per kategori, urut terbaru (beranda dan kategori.php)
    KEY idx_artikel_kategori (kategori_id, id),
    -- Artikel milik user (admin/artikel dan dashboard)
    KEY idx_artikel_users (user_id, id),

    CONSTRAINT fk_artikel_kategori
        FOREIGN KEY (kategori_id)
        REFERENCES kategori(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_artikel_users
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ==========================================
-- TABLE: komentar
-- ==========================================

CREATE TABLE komentar (
    id INT NOT NULL AUTO_INCREMENT,
    artikel_id INT DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    nama VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    komentar TEXT DEFAULT NULL,
    tanggal DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),

    PRIMARY KEY (id),

    -- Komentar per artikel, urut terbaru (detail.php)
    KEY idx_komentar_artikel (artikel_id, id),
    KEY idx_komentar_parent (parent_id),

    CONSTRAINT fk_komentar_artikel
        FOREIGN KEY (artikel_id)
        REFERENCES artikel(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_komentar_parent
        FOREIGN KEY (parent_id)
        REFERENCES komentar(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
