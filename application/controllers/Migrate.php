<?php
class Migrate extends CI_Controller
{
    public function index()
    {
        // Untuk alasan keamanan, Anda bisa menambahkan pengecekan env atau token di sini
        // if (ENVIRONMENT === 'production' && !isset($_GET['token'])) { die('Unauthorized'); }

        $this->load->library('migration');

        if ($this->migration->current() === FALSE)
        {
            show_error($this->migration->error_string());
        }
        else
        {
            echo "Migrasi berhasil dijalankan. Database sudah up-to-date.";
        }
    }
}
