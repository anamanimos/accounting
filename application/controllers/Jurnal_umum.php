<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jurnal_umum extends CI_Controller {

	/**
	 * @author : Deddy Rusdiansyah,S.Kom
	 * @web : http://deddyrusdiansyah.blogspot.com
	 * @keterangan : Controller untuk halaman profil
	 **/
	
	public function index()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
			$cari = $this->input->post('txt_cari');
			if(empty($cari)){
				$where = ' ';
			}else{
				$where = " WHERE no_jurnal LIKE '%$cari%' OR no_rek LIKE '%$cari%'";
			}
			
			$d['prg']= $this->config->item('prg');
			$d['web_prg']= $this->config->item('web_prg');
			
			$d['nama_program']= $this->config->item('nama_program');
			$d['instansi']= $this->config->item('instansi');
			$d['usaha']= $this->config->item('usaha');
			$d['alamat_instansi']= $this->config->item('alamat_instansi');

			
			$d['judul']="Jurnal Umum";
			
			//paging
			$page=$this->uri->segment(3);
			$limit=$this->config->item('limit_data');
			if(!$page):
			$offset = 0;
			else:
			$offset = $page;
			endif;
			
			$text = "SELECT * FROM jurnal_umum $where ";		
			$tot_hal = $this->app_model->manualQuery($text);		
			
			$d['tot_hal'] = $tot_hal->num_rows();
			
			$config['base_url'] = site_url() . '/jurnal_umum/index/';
			$config['total_rows'] = $tot_hal->num_rows();
			$config['per_page'] = $limit;
			$config['uri_segment'] = 3;
			
			// Custom Pagination Styling (Bootstrap 5 / Metronic)
			$config['full_tag_open'] = '<ul class="pagination pagination-circle pagination-outline">';
			$config['full_tag_close'] = '</ul>';
			
			$config['first_link'] = '<i class="ki-outline ki-double-left fs-2"></i>';
			$config['first_tag_open'] = '<li class="page-item m-1">';
			$config['first_tag_close'] = '</li>';
			
			$config['last_link'] = '<i class="ki-outline ki-double-right fs-2"></i>';
			$config['last_tag_open'] = '<li class="page-item m-1">';
			$config['last_tag_close'] = '</li>';
			
			$config['next_link'] = '<i class="ki-outline ki-right fs-2"></i>';
			$config['next_tag_open'] = '<li class="page-item m-1">';
			$config['next_tag_close'] = '</li>';
			
			$config['prev_link'] = '<i class="ki-outline ki-left fs-2"></i>';
			$config['prev_tag_open'] = '<li class="page-item m-1">';
			$config['prev_tag_close'] = '</li>';
			
			$config['cur_tag_open'] = '<li class="page-item active m-1"><a href="#" class="page-link">';
			$config['cur_tag_close'] = '</a></li>';
			
			$config['num_tag_open'] = '<li class="page-item m-1">';
			$config['num_tag_close'] = '</li>';
			
			$config['attributes'] = array('class' => 'page-link');

			$this->pagination->initialize($config);
			$d["paginator"] =$this->pagination->create_links();
			$d['hal'] = $offset;
			

			$text = "SELECT * FROM jurnal_umum $where 
					ORDER BY no_jurnal DESC,tgl_insert DESC 
					LIMIT $limit OFFSET $offset";
			$d['data'] = $this->app_model->manualQuery($text);
			
			$text = "SELECT * FROM rekening ORDER BY no_rek ASC";
			$d['list_rek'] = $this->app_model->manualQuery($text);
			
			// Jika request Ajax dari fitur search/pagination jQuery
			if($this->input->is_ajax_request()){
				$this->load->view('jurnal_umum/ajax_table', $d);
			}else{
				$d['css'] = [];
				$d['js_vendors'] = [];
				
				$d['user'] = (object) [
					'nama_lengkap' => $this->session->userdata('nama_lengkap'),
					'level'        => $this->session->userdata('level'),
					'email'        => $this->session->userdata('username') . '@accounting.test'
				];
				$d['content'] = 'jurnal_umum/view';		
				$this->load->view('templates/main', $d);
			}
		}else{
			header('location:'.base_url());
		}
	}
	
	public function jurnal_auto()
	{
		if (empty($this->session->userdata('logged_in'))) {
			redirect('login');
		}

		$d['judul'] = "Jurnal Auto Prompt";
		$d['title'] = "Jurnal Auto Prompt";
		
		$d['user'] = (object) [
			'nama_lengkap' => $this->session->userdata('nama_lengkap'),
			'level'        => $this->session->userdata('level'),
			'email'        => $this->session->userdata('username') . '@accounting.test'
		];

		$d['content'] = 'jurnal_umum/jurnal_auto';
		$this->load->view('templates/main', $d);
	}

	public function jurnal_auto_preview()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$prompt = $this->input->post('prompt_text');
		if (empty(trim($prompt))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Teks prompt kosong.']));
		}

		$transactions = $this->_parse_prompt_text($prompt);

		if (empty($transactions)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Tidak ada transaksi valid ditemukan dalam teks. Pastikan format mengandung order PE (contoh: Cetak DTF 4700CM = Rp 1.175.000) atau format: [Pelanggan] - [Suplier] - [Deskripsi] - [Ukuran] - [Modal]|[Harga]']));
		}

		// Auto-fetch no_jurnal dan no_bukti dari Database
		$max_jurnal_row = $this->db->query("SELECT MAX(CAST(no_jurnal AS UNSIGNED)) as max_val FROM jurnal_umum")->row();
		$max_jurnal = $max_jurnal_row ? $max_jurnal_row->max_val : null;
		$max_bukti_row = $this->db->query("SELECT MAX(CAST(no_bukti AS UNSIGNED)) as max_val FROM jurnal_umum")->row();
		$max_bukti = $max_bukti_row ? $max_bukti_row->max_val : null;
		
		$current_jurnal = $max_jurnal ? (int)$max_jurnal + 1 : (int)(date('y') . date('m') . '00001');
		$current_bukti = $max_bukti ? (int)$max_bukti + 1 : (int)(date('y') . date('m') . '001');

		// Generate 4 Rows per transaction
		$preview_data = [];
		foreach ($transactions as $trx) {
			$noj = (string) $current_jurnal;
			$nob = (string) $current_bukti;

			// Baris 1: Pendapatan (411) Kredit harga_jual
			$preview_data[] = [
				'no_jurnal' => $noj, 'tgl_jurnal' => $trx['tgl'], 'ket' => $trx['ket'],
				'no_bukti' => $nob, 'no_rek' => '411', 'debet' => 0, 'kredit' => $trx['harga_jual']
			];
			// Baris 2: Piutang (112) Debit harga_jual
			$preview_data[] = [
				'no_jurnal' => $noj, 'tgl_jurnal' => $trx['tgl'], 'ket' => $trx['ket'],
				'no_bukti' => $nob, 'no_rek' => '112', 'debet' => $trx['harga_jual'], 'kredit' => 0
			];
			// Baris 3: Hutang/Kas (213/118) Kredit modal
			$preview_data[] = [
				'no_jurnal' => $noj, 'tgl_jurnal' => $trx['tgl'], 'ket' => $trx['ket'],
				'no_bukti' => $nob, 'no_rek' => $trx['rek_inventory_or_ap'], 'debet' => 0, 'kredit' => $trx['modal']
			];
			// Baris 4: HPP (516) Debit modal
			$preview_data[] = [
				'no_jurnal' => $noj, 'tgl_jurnal' => $trx['tgl'], 'ket' => $trx['ket'],
				'no_bukti' => $nob, 'no_rek' => '516', 'debet' => $trx['modal'], 'kredit' => 0
			];

			$current_jurnal++;
			$current_bukti++;
		}

		return $this->output->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'data' => $preview_data]));
	}

	private function _extract_date($text)
	{
		if (empty($text) || !is_string($text)) return null;

		$text = trim($text);

		$month_map = [
			'januari' => 1, 'jan' => 1, 'january' => 1,
			'februari' => 2, 'feb' => 2, 'february' => 2,
			'maret' => 3, 'mar' => 3, 'march' => 3,
			'april' => 4, 'apr' => 4,
			'mei' => 5, 'may' => 5,
			'juni' => 6, 'jun' => 6, 'june' => 6,
			'juli' => 7, 'jul' => 7, 'july' => 7,
			'agustus' => 8, 'agu' => 8, 'agt' => 8, 'august' => 8, 'aug' => 8,
			'september' => 9, 'sep' => 9, 'sept' => 9,
			'oktober' => 10, 'okt' => 10, 'october' => 10, 'oct' => 10,
			'november' => 11, 'nov' => 11,
			'desember' => 12, 'des' => 12, 'december' => 12, 'dec' => 12
		];

		// 1. Text Month Name match: e.g. "14 Juli 2026", "14-Juli-2026", "14 Jul 26", "Juli 14, 2026"
		if (preg_match('/(\d{1,2})[\s\/\.-]+([a-zA-Z]{3,10})[\s\/\.-]+(\d{2,4})/i', $text, $m)) {
			$day = (int)$m[1];
			$month_name = strtolower($m[2]);
			$year = (int)$m[3];

			if (isset($month_map[$month_name])) {
				$month = $month_map[$month_name];
				if ($year < 100) $year += 2000;
				if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
					return sprintf("%04d-%02d-%02d", $year, $month, $day);
				}
			}
		}

		// 2. Numeric Date match: e.g. "14/07/2026", "14-07-26", "2026-07-14", "14.07.2026", "14 - 07 - 2026"
		if (preg_match('/(?:tgl|tanggal)?\s*[:\.]?\s*(\d{1,4})\s*[\/\.-]\s*(\d{1,2})\s*[\/\.-]\s*(\d{1,4})/i', $text, $m)) {
			$p1 = (int)$m[1];
			$p2 = (int)$m[2];
			$p3 = (int)$m[3];

			if (strlen($m[1]) == 4) {
				// Format YYYY-MM-DD
				$year = $p1;
				$month = $p2;
				$day = $p3;
			} else {
				// Format DD-MM-YYYY or DD-MM-YY
				$day = $p1;
				$month = $p2;
				$year = $p3;
				if ($year < 100) $year += 2000;
			}

			if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12 && $year >= 2000 && $year <= 2099) {
				return sprintf("%04d-%02d-%02d", $year, $month, $day);
			}
		}

		return null;
	}

	private function _parse_pe_text($prompt)
	{
		$lines = explode("\n", str_replace("\r", "", $prompt));
		$current_date = $this->_extract_date($prompt);
		if (!$current_date) {
			$current_date = date('Y-m-d');
		}

		$transactions = [];

		foreach ($lines as $line) {
			$strip_line = trim(str_replace(['*', '_', '~'], '', $line));
			if (empty($strip_line)) continue;

			$line_date = $this->_extract_date($strip_line);
			if ($line_date && !preg_match('/(?:CM|cm|m)\s*(?:=|:)/i', $strip_line)) {
				$current_date = $line_date;
				continue;
			}

			// Pattern: Cetak DTF 557CM = Rp 139.250 or 557CM = Rp 139.250 or Cetak DTF 557 CM = Rp 139.250 or Cetak DTF 557 CM: Rp 139.250
			if (preg_match('/^(.*?)\s*(\d+)\s*(?:CM|cm|m)?\s*(?:=|:)\s*(?:Rp|rp)?\.?\s*([\d\.,]+)/i', $strip_line, $m)) {
				$deskripsi = trim($m[1]);
				$deskripsi = preg_replace('/^[\s\-\*•\d\.\)]+/', '', $deskripsi);
				$deskripsi = trim($deskripsi);
				if (empty($deskripsi)) {
					$deskripsi = 'Cetak DTF';
				}
				$ukuran = trim($m[2]);
				$modal_str = str_replace(['.', ','], '', trim($m[3]));
				$modal = (int) preg_replace('/[^\d]/', '', $modal_str);

				$pelanggan = 'Sevencols';
				$suplier   = 'PE';

				$harga_jual = 0;
				$mh = $this->db->query("SELECT harga_jual FROM master_harga LIMIT 1")->row();
				$harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
				$panjang = (int) $ukuran;
				$harga_jual = $panjang * $harga_per_cm;
				if ($harga_jual === 0 && $modal > 0) {
					$harga_jual = $modal;
				}

				$ket = "$pelanggan - $suplier - $deskripsi - $ukuran";
				$rek_inventory_or_ap = '118';

				$transactions[] = [
					'tgl' => $current_date,
					'ket' => $ket,
					'harga_jual' => $harga_jual,
					'modal' => $modal,
					'rek_inventory_or_ap' => $rek_inventory_or_ap
				];
			}
		}

		return $transactions;
	}

	private function _parse_prompt_text($prompt)
	{
		// 1. First try PE Order format
		$pe_trxs = $this->_parse_pe_text($prompt);
		if (!empty($pe_trxs)) {
			return $pe_trxs;
		}

		// 2. Direct JSON Array Support
		$clean_json = '';
		if (preg_match('/\[\s*\{[\s\S]*\}\s*\]/', $prompt, $matches)) {
			$clean_json = $matches[0];
		} elseif (strpos(trim($prompt), '[') === 0) {
			$clean_json = $prompt;
		}

		if (!empty($clean_json)) {
			$json_data = json_decode($clean_json, true);
			if (is_array($json_data) && count($json_data) > 0) {
				$transactions = [];
				$current_date = date('Y-m-d');
				foreach ($json_data as $item) {
					$raw_tgl   = $item['tanggal'] ?? date('Y-m-d');
					$pelanggan = !empty($item['pelanggan']) ? trim($item['pelanggan']) : 'Sevencols';
					$suplier   = !empty($item['suplier']) ? trim($item['suplier']) : 'Suplier Utama';
					$deskripsi = !empty($item['deskripsi']) ? trim($item['deskripsi']) : 'Nota AI';
					$ukuran    = isset($item['ukuran']) ? trim((string)$item['ukuran']) : '1';
					$modal     = isset($item['modal']) ? (int) preg_replace('/[^\d]/', '', (string)$item['modal']) : 0;

					$item_date = $this->_extract_date($raw_tgl);
					if ($item_date) {
						$current_date = $item_date;
					}

					$harga_jual = 0;
					$mh = $this->db->query("SELECT harga_jual FROM master_harga LIMIT 1")->row();
					$harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
					preg_match_all('/\d+/', $ukuran, $matches_uk);
					$panjang = (!empty($matches_uk[0])) ? (int) end($matches_uk[0]) : 0;
					$harga_jual = $panjang * $harga_per_cm;
					if ($harga_jual === 0 && $modal > 0) {
						$harga_jual = $modal;
					}

					$ket = "$pelanggan - $suplier - $deskripsi - $ukuran";
					$rek_inventory_or_ap = '118';
					if (stripos($suplier, 'luar(p.riyadi)') !== false) {
						$rek_inventory_or_ap = '213';
					}

					$transactions[] = [
						'tgl' => $current_date,
						'ket' => $ket,
						'harga_jual' => $harga_jual,
						'modal' => $modal,
						'rek_inventory_or_ap' => $rek_inventory_or_ap
					];
				}
				if (!empty($transactions)) {
					return $transactions;
				}
			}
		}

		// 3. Fallback to standard line-by-line format
		$lines = explode("\n", str_replace("\r", "", $prompt));
		$current_date = $this->_extract_date($prompt) ?: date('Y-m-d');
		$transactions = [];

		foreach ($lines as $line) {
			$clean_line = trim($line, " \t\n\r\0\x0B*_~\\");
			if (empty($clean_line)) continue;

			$line_date = $this->_extract_date($clean_line);
			if ($line_date) {
				$current_date = $line_date;
				continue;
			}

			if (strpos($line, '-') !== false || strpos($line, '|') !== false) {
				$harga_jual = 0;
				$left_part = $line;

				if (strpos($line, '|') !== false) {
					$parts = explode('|', $line);
					$harga_str = trim($parts[1]);
					$harga_str = str_replace(['.', ','], '', $harga_str);
					$harga_jual = (int) $harga_str;
					$left_part = trim($parts[0]);
				}
				
				$dash_parts = explode('-', $left_part);
				
				if (count($dash_parts) >= 5) {
					$pelanggan = trim($dash_parts[0]);
					$suplier = trim($dash_parts[1]);
					$deskripsi = trim($dash_parts[2]);
					$ukuran = trim($dash_parts[3]);
					$modal_str = trim($dash_parts[4]);
					$modal_str = str_replace(['.', ','], '', $modal_str);
					$modal = (int) $modal_str;
					
					if ($harga_jual === 0) {
						$mh = $this->db->query("SELECT harga_jual FROM master_harga LIMIT 1")->row();
						$harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
						
						preg_match_all('/\d+/', $ukuran, $matches_uk);
						$panjang = (!empty($matches_uk[0])) ? (int) end($matches_uk[0]) : 0;
						
						$harga_jual = $panjang * $harga_per_cm;
						
						if ($harga_jual === 0 && $modal > 0) {
							$harga_jual = $modal;
						}
					}

					$ket = "$pelanggan - $suplier - $deskripsi - $ukuran";
					$rek_inventory_or_ap = '118';
					if (stripos($suplier, 'luar(p.riyadi)') !== false) {
						$rek_inventory_or_ap = '213';
					}

					$transactions[] = [
						'tgl' => $current_date,
						'ket' => $ket,
						'harga_jual' => $harga_jual,
						'modal' => $modal,
						'rek_inventory_or_ap' => $rek_inventory_or_ap
					];
				}
			}
		}

		return $transactions;
	}

	public function jurnal_auto_save()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$payload = json_decode($this->input->raw_input_stream, true);
		$data_to_insert = isset($payload['data']) ? $payload['data'] : [];

		if (empty($data_to_insert)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Data kosong.']));
		}

		$username = $this->session->userdata('username');
		if (empty($username)) $username = 'cranam21';

		$this->db->trans_start();

		foreach ($data_to_insert as $row) {
			$this->db->set('tgl_insert', 'NOW()', FALSE);
			$this->db->insert('jurnal_umum', [
				'no_jurnal' => $row['no_jurnal'],
				'tgl_jurnal' => $row['tgl_jurnal'],
				'ket' => $row['ket'],
				'no_bukti' => $row['no_bukti'],
				'no_rek' => $row['no_rek'],
				'debet' => $row['debet'],
				'kredit' => $row['kredit'],
				'username' => $username,
			]);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output->set_content_type('application/json')
				->set_status_header(500)
				->set_output(json_encode(['status' => 'error', 'message' => 'Database error.']));
		}

		$count = count($data_to_insert);
		return $this->output->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'message' => "Berhasil menyimpan $count baris jurnal!"]));
	}

	
	public function edit()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
			$id = $this->input->post('id');  
			$text = "SELECT * FROM jurnal_umum WHERE no_jurnal='$id' LIMIT 1";
			$data = $this->app_model->manualQuery($text);
			foreach($data->result() as $db){
				$d['no_jurnal']	=$db->no_jurnal;
				$d['tgl']		= $this->app_model->tgl_str($db->tgl_jurnal);
				$d['no_bukti']	=$db->no_bukti;
				$d['ket']		=$db->ket;
				echo json_encode($d);
			}

		}else{
			header('location:'.base_url());
		}
	}

	public function get_jurnal_full()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$no_jurnal = $this->input->post('no_jurnal');
		if (empty($no_jurnal)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'No Jurnal wajib diisi']));
		}

		$rows = $this->db->get_where('jurnal_umum', ['no_jurnal' => $no_jurnal])->result_array();

		if (!empty($rows)) {
			return $this->output->set_content_type('application/json')
				->set_output(json_encode([
					'status' => 'success',
					'no_jurnal' => $rows[0]['no_jurnal'],
					'tgl_jurnal' => $rows[0]['tgl_jurnal'],
					'no_bukti' => $rows[0]['no_bukti'],
					'rows' => $rows
				]));
		}

		return $this->output->set_content_type('application/json')
			->set_status_header(404)
			->set_output(json_encode(['status' => 'error', 'message' => 'Data jurnal tidak ditemukan']));
	}

	public function save_jurnal_full()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$payload = json_decode($this->input->raw_input_stream, true);
		if (!$payload) {
			$payload = $this->input->post();
		}

		$no_jurnal  = trim($payload['no_jurnal'] ?? '');
		$tgl_jurnal = trim($payload['tgl_jurnal'] ?? '');
		$no_bukti   = trim($payload['no_bukti'] ?? '');
		$rows       = isset($payload['rows']) ? $payload['rows'] : [];

		if (empty($no_jurnal) || empty($rows)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Data jurnal atau baris transaksi tidak boleh kosong']));
		}

		$tot_debet = 0;
		$tot_kredit = 0;
		$clean_rows = [];

		foreach ($rows as $r) {
			$no_rek = trim($r['no_rek'] ?? '');
			$ket    = trim($r['ket'] ?? '');
			$debet  = (int) str_replace([',', '.'], '', (string)($r['debet'] ?? 0));
			$kredit = (int) str_replace([',', '.'], '', (string)($r['kredit'] ?? 0));

			if (!empty($no_rek)) {
				$tot_debet  += $debet;
				$tot_kredit += $kredit;
				$clean_rows[] = [
					'no_rek' => $no_rek,
					'ket'    => $ket,
					'debet'  => $debet,
					'kredit' => $kredit
				];
			}
		}

		if (empty($clean_rows)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Minimal 1 baris rekening harus dipilih']));
		}

		// Balance Validation
		if ($tot_debet !== $tot_kredit) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode([
					'status' => 'error',
					'message' => "Total Debet (Rp " . number_format($tot_debet, 0, ',', '.') . ") dan Total Kredit (Rp " . number_format($tot_kredit, 0, ',', '.') . ") tidak seimbang (Unbalanced)."
				]));
		}

		$username = $this->session->userdata('username');
		if (empty($username)) $username = 'admin';

		$this->db->trans_start();

		// Delete existing entries for this no_jurnal
		$this->db->delete('jurnal_umum', ['no_jurnal' => $no_jurnal]);

		// Insert all updated rows
		foreach ($clean_rows as $r) {
			$this->db->insert('jurnal_umum', [
				'no_jurnal'  => $no_jurnal,
				'tgl_jurnal' => $tgl_jurnal,
				'no_bukti'   => $no_bukti,
				'no_rek'     => $r['no_rek'],
				'ket'        => $r['ket'],
				'debet'      => $r['debet'],
				'kredit'     => $r['kredit'],
				'username'   => $username,
				'tgl_insert' => date('Y-m-d H:i:s')
			]);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->output->set_content_type('application/json')
				->set_status_header(500)
				->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pembaruan ke database']));
		}

		return $this->output->set_content_type('application/json')
			->set_output(json_encode([
				'status' => 'success',
				'message' => 'Seluruh baris transaksi No. Jurnal ' . $no_jurnal . ' berhasil diperbarui!'
			]));
	}
	
	
	public function hapus()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){			
			$id = $this->uri->segment(3);
			$this->app_model->manualQuery("DELETE FROM jurnal_umum WHERE no_jurnal='$id'");
			echo "<meta http-equiv='refresh' content='0; url=".base_url()."rekening'>";			
		}else{
			header('location:'.base_url());
		}
	}
	
	
	public function simpan()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
				$no_jurnal = $this->input->post('no_jurnal');
				$raw_tgl   = $this->input->post('tgl');

				if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_tgl)) {
					$tgl_jurnal = $raw_tgl;
				} else {
					$tgl_jurnal = $this->app_model->tgl_sql($raw_tgl);
				}

				$up['no_jurnal']   = $no_jurnal;
				$up['tgl_jurnal']  = $tgl_jurnal;
				$up['ket']         = $this->input->post('ket');
				$up['no_bukti']      = $this->input->post('no_bukti');
				$up['no_rek']      = $this->input->post('no_rek');
				$up['debet']       = str_replace(',','',$this->input->post('debet'));
				$up['kredit']      = str_replace(',','',$this->input->post('kredit'));
				$username          = $this->session->userdata('username');
				$up['username']    = !empty($username) ? $username : 'admin';
				$up['tgl_insert']  = date('Y-m-d H:i:s');
				
				$id['no_jurnal']   = $no_jurnal;
				$id['no_rek']      = $this->input->post('no_rek');
				
				$no_rek 	       = $this->input->post('no_rek');
				
				$text = "SELECT * FROM jurnal_umum WHERE no_jurnal='$no_jurnal' AND no_rek='$no_rek'";
				$data = $this->app_model->manualQuery($text);
				if($data->num_rows() > 0){
					$this->app_model->updateData("jurnal_umum",$up,$id);
					echo 'Simpan data Sukses';
				}else{
					$this->app_model->insertData("jurnal_umum",$up);
					echo 'Simpan data Sukses';		
				}
		}else{
				header('location:'.base_url());
		}
	
	}
	
	public function DetailJurnalUmum()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
			$id = $this->input->post('no_jurnal'); 
			
			$text = "SELECT * FROM jurnal_umum WHERE no_jurnal='$id'";
			$d['data'] = $this->app_model->manualQuery($text);
			
			$this->load->view('jurnal_umum/detail_jurnal',$d);
		}else{
			header('location:'.base_url());
		}
	}
	
	public function hapusDetail()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
			$id = $this->input->post('no_jurnal'); 
			$rek = $this->input->post('no_rek'); 
			
			$text = "DELETE FROM jurnal_umum WHERE no_jurnal='$id' AND no_rek='$rek'";
			$this->app_model->manualQuery($text);
			
			$text = "SELECT * FROM jurnal_umum WHERE no_jurnal='$id'";
			$d['data'] = $this->app_model->manualQuery($text);
			
			$this->load->view('jurnal_umum/detail_jurnal',$d);

		}else{
			header('location:'.base_url());
		}
	}
	
}

/* End of file profil.php */
/* Location: ./application/controllers/profil.php */