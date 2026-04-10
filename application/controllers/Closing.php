<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[AllowDynamicProperties]
class Closing extends CI_Controller {

    // Peta bulan untuk nama Indonesia
    private $bulan_map = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function __construct() {
        parent::__construct();
        // Load database library
        $this->load->database();
    }

    /**
     * Tampilkan halaman UI Tutup Buku
     */
    public function index() {
        // Cek login
        if (empty($this->session->userdata('logged_in'))) {
            redirect('login');
        }

        // Siapkan variabel untuk layout
        $d['judul'] = "Tutup Buku";
        $d['title'] = "Tutup Buku";
        
        $d['user'] = (object) [
            'nama_lengkap' => $this->session->userdata('nama_lengkap'),
            'level'        => $this->session->userdata('level'),
            'email'        => $this->session->userdata('username') . '@accounting.test'
        ];

        // Buat list tahun (5 tahun ke belakang + 1 tahun ke depan)
        $current_year = date('Y');
        $d['list_tahun'] = range($current_year - 5, $current_year + 1);

        $d['content'] = 'closing/index';
        $this->load->view('templates/main', $d);
    }

    /**
     * Endpoint untuk eksekusi tutup bulan.
     * Menerima input POST dari form AJAX.
     */
    public function tutup_bulan() {
        
        // 1. Ambil dan Validasi Input POST
        $month = $this->input->post('bulan');
        $year = $this->input->post('tahun');
        $sisa_print = $this->input->post('sisa_print');
        $sisa_oven = $this->input->post('sisa_oven');

        if (empty($month) || empty($year) || $sisa_print === NULL || $sisa_oven === NULL || 
            !is_numeric($month) || !is_numeric($year) || !is_numeric($sisa_print) || !is_numeric($sisa_oven)) {
            
            return $this->json_response(400, [
                'status' => 'error', 
                'message' => 'Parameter bulan, tahun, sisa_print, dan sisa_oven wajib diisi dan harus berupa angka.'
            ]);
        }

        // 2. Persiapan Variabel
        $nama_bulan = $this->bulan_map[(int)$month];
        $tgl_jurnal = date('Y-m-t', strtotime("$year-$month-01")); // Tanggal akhir bulan
        $username = 'cranam21';

        // 3. Cek Duplikat
        // Kita cek berdasarkan salah satu transaksi statis, misal Biaya ATM
        $ket_check = "Tutup Bulan: Biaya ATM Bank Bulan " . $nama_bulan;
        $this->db->where('ket', $ket_check);
        $this->db->where('YEAR(tgl_jurnal)', $year);
        $this->db->where('MONTH(tgl_jurnal)', $month);
        $exists = $this->db->get('jurnal_umum')->row();

        if ($exists) {
            return $this->json_response(409, [
                'status' => 'error', 
                'message' => 'Jurnal tutup bulan untuk ' . $nama_bulan . ' ' . $year . ' sudah ada. Eksekusi dibatalkan.'
            ]);
        }

        // 4. Get Nomor Jurnal Berikutnya
        $this->db->select_max('no_jurnal');
        $last_jurnal = $this->db->get('jurnal_umum')->row()->no_jurnal;
        $next_jurnal = $last_jurnal + 1;

        // 5. Get Nilai Piutang CV Damai Jaya (no_rek 112)
        // Saldo Piutang = SUM(debet) - SUM(kredit)
        $this->db->select('SUM(debet) - SUM(kredit) as saldo_piutang');
        $this->db->where('no_rek', '112');
        // Hanya hitung piutang s/d tanggal jurnal
        $this->db->where('tgl_jurnal <=', $tgl_jurnal); 
        $piutang_result = $this->db->get('jurnal_umum')->row();
        $nilai_piutang = ($piutang_result && $piutang_result->saldo_piutang > 0) ? $piutang_result->saldo_piutang : 0;

        
        // 6. Mulai Database Transaction
        $this->db->trans_start();

        // --- Transaksi 1: Penyusutan Printer ---
        // Sesuai Refinement 2: Hanya jika sisa > 0
        if ($sisa_print > 0) {
            $ket_print = "Tutup Bulan: Penyusutan Printer $nama_bulan (Sisa:" . $sisa_print . "x)";
            // Sesuai Refinement 6: Beban (514) di Debet, Akumulasi (116) di Kredit
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal, $ket_print, 'PRINTER02',
                '514',  // no_rek Debet (Beban Peny. Printer)
                '116',  // no_rek Kredit (Akum. Peny. Printer)
                100000, // Nilai
                $username
            );
            $next_jurnal++; // Increment no_jurnal untuk transaksi berikutnya
        }

        // --- Transaksi 2: Penyusutan Oven ---
        // Sesuai Refinement 2: Hanya jika sisa > 0
        if ($sisa_oven > 0) {
            $ket_oven = "Tutup Bulan: Penyusutan Oven $nama_bulan (Sisa:" . $sisa_oven . "x)";
            // Sesuai Refinement 6: Beban (514) di Debet, Akumulasi (116) di Kredit
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal, $ket_oven, 'OVEN01',
                '514',  // no_rek Debet (Beban Peny. Oven)
                '116',  // no_rek Kredit (Akum. Peny. Oven)
                40000,  // Nilai
                $username
            );
            $next_jurnal++;
        }

        // --- Transaksi 3: Biaya ATM Bank ---
        $ket_atm = "Tutup Bulan: Biaya ATM Bank Bulan " . $nama_bulan;
        // Sesuai Refinement 6: Beban (513) di Debet, Kas/Bank (118) di Kredit
        $this->_insert_jurnal_pasangan(
            $next_jurnal, $tgl_jurnal, $ket_atm, '1',
            '513',  // no_rek Debet (Beban ATM)
            '118',  // no_rek Kredit (Kas/Bank)
            50000,  // Nilai
            $username
        );
        $next_jurnal++;

        // --- Transaksi 4: Bayar Bulanan CV.Damai Jaya ---
        $ket_bayar_damai = "Tutup Bulan: Bayar Biaya Bulanan Ke CV.Damai Jaya " . $nama_bulan;
        // Sesuai Refinement 6: Beban (513) di Debet, Kas/Bank (118) di Kredit
        $this->_insert_jurnal_pasangan(
            $next_jurnal, $tgl_jurnal, $ket_bayar_damai, '1',
            '513',  // no_rek Debet (Beban)
            '118',  // no_rek Kredit (Kas/Bank)
            300000, // Nilai
            $username
        );
        $next_jurnal++;
        
        // --- Transaksi 5: Terima Piutang Damai Jaya ---
        // Transaksi ini hanya berjalan jika ada saldo piutang
        if ($nilai_piutang > 0) {
            $ket_piutang = "Tutup Bulan: Terima pembayaran piutang damai jaya " . $nama_bulan;
            // Transaksi ini sudah benar di gambar (Ref 6 tidak berlaku): Kas (118) Debet, Piutang (112) Kredit
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal, $ket_piutang, '1',
                '118',  // no_rek Debet (Kas/Bank)
                '112',  // no_rek Kredit (Piutang)
                $nilai_piutang, // Nilai dari saldo piutang
                $username
            );
            // $next_jurnal++; // Tidak perlu karena ini transaksi terakhir
        }

        // 7. Selesaikan Database Transaction
        $this->db->trans_complete();

        // 8. Berikan Respon
        if ($this->db->trans_status() === FALSE) {
            return $this->json_response(500, [
                'status' => 'error', 
                'message' => 'Terjadi kesalahan database. Transaksi dibatalkan.'
            ]);
        } else {
            return $this->json_response(200, [
                'status' => 'success', 
                'message' => 'Jurnal tutup bulan untuk ' . $nama_bulan . ' ' . $year . ' berhasil dibuat.'
            ]);
        }
    }

    /**
     * Helper function untuk insert 2 baris (Debet & Kredit)
     * Menggunakan NOW() untuk tgl_insert
     */
    private function _insert_jurnal_pasangan($no_jurnal, $tgl_jurnal, $ket, $no_bukti, $rek_debet, $rek_kredit, $nilai, $username) {
        
        // Baris Debet
        $data_debet = [
            'no_jurnal' => $no_jurnal,
            'tgl_jurnal' => $tgl_jurnal,
            'no_rek' => $rek_debet,
            'ket' => $ket,
            'debet' => $nilai,
            'kredit' => 0,
            'no_bukti' => $no_bukti,
            'username' => $username,
        ];
        // Set tgl_insert ke NOW()
        $this->db->set('tgl_insert', 'NOW()', FALSE); // FALSE agar NOW() tidak di-escape
        $this->db->insert('jurnal_umum', $data_debet);

        // Baris Kredit
        $data_kredit = [
            'no_jurnal' => $no_jurnal,
            'tgl_jurnal' => $tgl_jurnal,
            'no_rek' => $rek_kredit,
            'ket' => $ket,
            'debet' => 0,
            'kredit' => $nilai,
            'no_bukti' => $no_bukti,
            'username' => $username,
        ];
        // Set tgl_insert ke NOW()
        $this->db->set('tgl_insert', 'NOW()', FALSE);
        $this->db->insert('jurnal_umum', $data_kredit);
    }

    /**
     * Helper function untuk mengirim respon JSON
     */
    private function json_response($status_code, $data) {
        $this->output
             ->set_status_header($status_code)
             ->set_content_type('application/json', 'utf-8')
             ->set_output(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint untuk eksekusi tutup tahun.
     */
    public function tutup_tahun() {
        // Cek login
        if (empty($this->session->userdata('logged_in'))) {
            return $this->json_response(401, ['status' => 'error', 'message' => 'Unauthorized']);
        }

        $tahun = $this->input->post('tahun');
        
        if (empty($tahun)) {
            return $this->json_response(400, [
                'status' => 'error', 
                'message' => 'Parameter tahun wajib diisi.'
            ]);
        }
        
        $username = $this->session->userdata('username') ? $this->session->userdata('username') : 'cranam21';

        // 1. Cek Duplikat
        // Cek apakah jurnal penutup untuk tahun ini sudah ada (via ket LIKE 'Menutup Pendapatan%')
        $this->db->where('ket LIKE', 'Menutup Pendapatan%');
        $this->db->where('tgl_jurnal', $tahun . '-12-31');
        $exists = $this->db->get('jurnal_umum')->row();

        if ($exists) {
            return $this->json_response(409, [
                'status' => 'error', 
                'message' => 'Jurnal tutup tahun untuk periode ' . $tahun . ' sudah ada. Eksekusi dibatalkan.'
            ]);
        }

        // 2. Tentukan nomor jurnal awal
        // Berdasarkan screenshot, format no_jurnal biasanya YYYY-MM + iterasi atau YYMM + iterasi.
        // Kita hitung dari no_jurnal terbesar di dalam tabel
        $this->db->select_max('no_jurnal');
        $row_max = $this->db->get('jurnal_umum')->row();
        
        if (!empty($row_max->no_jurnal)) {
            $next_jurnal = $row_max->no_jurnal + 1;
        } else {
            $next_jurnal = date('y') . date('m') . '00001';
        }

        $tgl_jurnal_penutup = $tahun . '-12-31';
        $tgl_jurnal_saldo_awal = ($tahun + 1) . '-01-01';

        // 3. Mulai Database Transaction
        $this->db->trans_start();

        // --- TAHAP 1: JURNAL PENUTUP ---
        
        // A. Menutup Pendapatan (Kepala 4)
        // Mengecualikan 421 (Ikhtisar Laba Rugi)
        $q_pendapatan = "SELECT no_rek, (SUM(kredit) - SUM(debet)) as saldo FROM jurnal_umum WHERE no_rek LIKE '4%' AND no_rek != '421' AND YEAR(tgl_jurnal) = '$tahun' GROUP BY no_rek HAVING saldo > 0";
        $pendapatan_list = $this->db->query($q_pendapatan)->result();
        
        $total_pendapatan = 0;
        foreach ($pendapatan_list as $row) {
            $nilai = $row->saldo;
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal_penutup, "Menutup Pendapatan $tahun", 1,
                $row->no_rek, // Debet: Pendapatan (di-nol-kan)
                '421',        // Kredit: Ikhtisar Laba Rugi
                $nilai,
                $username
            );
            $next_jurnal++;
            $total_pendapatan += $nilai;
        }

        // B. Menutup Beban (Kepala 5)
        $q_beban = "SELECT no_rek, (SUM(debet) - SUM(kredit)) as saldo FROM jurnal_umum WHERE no_rek LIKE '5%' AND YEAR(tgl_jurnal) = '$tahun' GROUP BY no_rek HAVING saldo > 0";
        $beban_list = $this->db->query($q_beban)->result();
        
        $total_beban = 0;
        foreach ($beban_list as $row) {
            $nilai = $row->saldo;
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal_penutup, "Menutup Akun beban $tahun", 1,
                '421',        // Debet: Ikhtisar Laba Rugi
                $row->no_rek, // Kredit: Beban (di-nol-kan)
                $nilai,
                $username
            );
            $next_jurnal++;
            $total_beban += $nilai;
        }

        // C. Menutup Ikhtisar Laba Rugi (421) ke Modal (311)
        $laba = $total_pendapatan - $total_beban;
        if ($laba > 0) {
            // Laba: Ikhtisar (Debet), Modal (Kredit)
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal_penutup, "Menutup Akun Ikhtisar Laba Rugi", 1,
                '421', // Debet
                '311', // Kredit Modal bertambah
                $laba,
                $username
            );
            $next_jurnal++;
        } elseif ($laba < 0) {
            // Rugi: Modal (Debet), Ikhtisar (Kredit)
            $this->_insert_jurnal_pasangan(
                $next_jurnal, $tgl_jurnal_penutup, "Menutup Akun Ikhtisar Laba Rugi", 1,
                '311', // Debet Modal berkurang
                '421', // Kredit
                abs($laba),
                $username
            );
            $next_jurnal++;
        }

        // --- TAHAP 2: SETUP SALDO AWAL (Akun Riil Harta, Hutang, Modal) ---
        
        // Ambil daftar unik akun riil yang ada transaksinya di tahun tersebut, atau punya saldo awal
        // Kita loop semua rekening kepala 1, 2, 3
        $rekening_riil = $this->db->query("SELECT no_rek FROM rekening WHERE no_rek LIKE '1%' OR no_rek LIKE '2%' OR no_rek LIKE '3%' ORDER BY no_rek ASC")->result();
        
        $next_jurnal_awal = $next_jurnal; // Gunakan satu no_jurnal yang sama untuk semua baris Saldo Awal (sesuai screenshot user)
        $added_row = false;

        foreach ($rekening_riil as $rek) {
            $no_rek = $rek->no_rek;
            
            // 1. Ambil dari tabel saldo_awal (jika user pernah input manual periode sebelumnya)
            // Sistem app_model menganggap periode adalah tahun.
            $q_sa = $this->db->query("SELECT debet, kredit FROM saldo_awal WHERE no_rek = '$no_rek' AND periode = '$tahun'")->row();
            $sa_debet = $q_sa ? $q_sa->debet : 0;
            $sa_kredit = $q_sa ? $q_sa->kredit : 0;
            
            // 2. Ambil mutasi dari jurnal_umum tahun transaksi berjalan
            // Termasuk Jurnal Penutup yang baru saja diinsert DALAM transaction ini! (InnoDB handles this correctly)
            $q_ju = $this->db->query("SELECT SUM(debet) as sum_debet, SUM(kredit) as sum_kredit FROM jurnal_umum WHERE no_rek = '$no_rek' AND YEAR(tgl_jurnal) = '$tahun'")->row();
            $ju_debet = $q_ju ? $q_ju->sum_debet : 0;
            $ju_kredit = $q_ju ? $q_ju->sum_kredit : 0;

            // 3. Kalkulasi Net Saldo Akhir
            $total_debet = $sa_debet + $ju_debet;
            $total_kredit = $sa_kredit + $ju_kredit;
            $saldo_bersih = $total_debet - $total_kredit;

            if ($saldo_bersih == 0) continue; // Skip jika 0

            $debet_final = 0;
            $kredit_final = 0;

            $prefix = substr($no_rek, 0, 1);
            if ($prefix == '1') {
                // Harta bersaldo normal Debet
                if ($saldo_bersih > 0) {
                    $debet_final = $saldo_bersih;
                } else {
                    $kredit_final = abs($saldo_bersih);
                }
            } else {
                // Hutang (2) dan Modal (3) bersaldo normal Kredit
                // Jika Positif (Debet lebih besar), taruh di Debet
                // Jika Negatif (Kredit lebih besar), taruh di Kredit secara nominal absolut
                if ($saldo_bersih < 0) {
                    $kredit_final = abs($saldo_bersih);
                } else {
                    $debet_final = $saldo_bersih;
                }
            }

            // Insert single row ke jurnal_umum untuk Data Akhir Next Year
            $data_awal = [
                'no_jurnal' => $next_jurnal_awal,
                'tgl_jurnal' => $tgl_jurnal_saldo_awal,
                'no_rek' => $no_rek,
                'ket' => "Data Akhir $tahun",
                'debet' => $debet_final,
                'kredit' => $kredit_final,
                'no_bukti' => 1,
                'username' => $username,
            ];
            $this->db->set('tgl_insert', 'NOW()', FALSE);
            $this->db->insert('jurnal_umum', $data_awal);
            $added_row = true;
        }

        if ($added_row) {
            $next_jurnal++;
        }

        // Commit Transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->json_response(500, [
                'status' => 'error', 
                'message' => 'Terjadi kesalahan database. Proses tutup tahun dibatalkan.'
            ]);
        } else {
            return $this->json_response(200, [
                'status' => 'success', 
                'message' => 'Tutup tahun ' . $tahun . ' berhasil diproses.'
            ]);
        }
    }
}