<?php
/**
 * Script untuk membuat tabel wa_pending_image di database produksi.
 * Cara eksekusi:
 * 1. Buka melalui browser (misal: https://domain-anda.com/migrate_wa.php)
 * 2. Atau melalui CLI: php migrate_wa.php
 */

define('BASEPATH', __DIR__);
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

require_once 'application/config/database.php';

$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `wa_pending_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` varchar(100) NOT NULL,
  `image_url` text NOT NULL,
  `sender_jid` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "<h1>Sukses</h1>";
    echo "<p>Tabel <strong>wa_pending_image</strong> berhasil dibuat atau sudah tersedia.</p>";
} else {
    echo "<h1>Gagal</h1>";
    echo "<p>Error saat membuat tabel: " . $conn->error . "</p>";
}

$conn->close();
