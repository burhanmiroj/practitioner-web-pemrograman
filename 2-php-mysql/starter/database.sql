-- StokKu - struktur database
-- Cara pakai: mysql -u root -p < database.sql
-- (atau import lewat phpMyAdmin)

CREATE DATABASE IF NOT EXISTS stokku
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stokku;

-- Akun yang boleh login.
-- Password disimpan dalam bentuk hash (bcrypt), bukan teks asli.
-- Akun admin pertama dibuat otomatis oleh index.php saat tabel ini masih kosong.
CREATE TABLE IF NOT EXISTS users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username   VARCHAR(50)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kategori (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama       VARCHAR(100) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_nama (nama)
) ENGINE=InnoDB;

-- stok dan harga bertipe UNSIGNED supaya tidak bisa negatif.
-- Kategori yang masih dipakai produk tidak bisa dihapus (ON DELETE RESTRICT).
CREATE TABLE IF NOT EXISTS produk (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kategori_id INT UNSIGNED NOT NULL,
  nama        VARCHAR(150) NOT NULL,
  gambar      VARCHAR(500) NULL,
  stok        INT UNSIGNED NOT NULL DEFAULT 0,
  harga       INT UNSIGNED NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_produk_kategori (kategori_id),
  KEY idx_produk_nama (nama),
  CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id)
    REFERENCES kategori (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- harga_satuan menyimpan harga saat transaksi, jadi riwayat tetap benar
-- walau harga produk diubah nanti.
-- Produk yang sudah pernah terjual tidak bisa dihapus (ON DELETE RESTRICT).
CREATE TABLE IF NOT EXISTS penjualan (
  id           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  produk_id    INT UNSIGNED    NOT NULL,
  qty          INT UNSIGNED    NOT NULL,
  harga_satuan INT UNSIGNED    NOT NULL,
  total        BIGINT UNSIGNED NOT NULL,
  tanggal      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_penjualan_produk (produk_id),
  KEY idx_penjualan_tanggal (tanggal),
  CONSTRAINT chk_penjualan_qty CHECK (qty > 0),
  CONSTRAINT fk_penjualan_produk FOREIGN KEY (produk_id)
    REFERENCES produk (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
