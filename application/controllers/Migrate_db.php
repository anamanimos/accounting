<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migrate_db extends CI_Controller {

    public function index()
    {
        // Hanya izinkan jika user login (opsional) atau matikan jika ingin bisa dieksekusi siapa saja
        if (empty($this->session->userdata('logged_in'))) {
            echo "Akses ditolak. Silakan login terlebih dahulu.";
            return;
        }

        // Script untuk membuat tabel master_harga
        $sql = "CREATE TABLE IF NOT EXISTS `master_harga` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `deskripsi` varchar(255) NOT NULL,
            `harga_jual` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if ($this->db->query($sql)) {
            echo "Tabel 'master_harga' berhasil dibuat atau sudah ada.<br>";
        } else {
            echo "Gagal membuat tabel 'master_harga'. Error: " . $this->db->error()['message'] . "<br>";
        }

        echo "<br><a href='" . base_url('home') . "'>Kembali ke Home</a>";
    }
}
