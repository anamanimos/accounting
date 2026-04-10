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

		$lines = explode("\n", str_replace("\r", "", $prompt));
		
		$current_date = date('Y-m-d');
		$transactions = [];
		
		foreach ($lines as $line) {
			$line = trim($line);
			if (empty($line)) continue;

			// 1. Cek Tanggal (DD - MM - YYYY)
			if (preg_match('/^(\d{1,2})\s*-\s*(\d{1,2})\s*-\s*(\d{4})$/', $line, $matches)) {
				$day = str_pad($matches[1], 2, "0", STR_PAD_LEFT);
				$month = str_pad($matches[2], 2, "0", STR_PAD_LEFT);
				$year = $matches[3];
				$current_date = "$year-$month-$day";
				continue;
			}

			// 2. Cek baris Transaksi (mengandung '|')
			if (strpos($line, '|') !== false) {
				$parts = explode('|', $line);
				$harga_jual = (int) trim($parts[1]);
				
				$left_part = trim($parts[0]);
				$dash_parts = explode(' - ', $left_part);
				
				if (count($dash_parts) >= 5) {
					$pelanggan = trim($dash_parts[0]);
					$suplier = trim($dash_parts[1]);
					$deskripsi = trim($dash_parts[2]);
					$ukuran = trim($dash_parts[3]);
					$modal = (int) trim($dash_parts[4]);
					
					$ket = "$pelanggan - $suplier - $deskripsi - $ukuran";
					
					// Rekening Logika berdasarkan supplier
					$rek_inventory_or_ap = '118'; // Default: Kas/Bank
					if (stripos($suplier, 'luar(p.riyadi)') !== false) {
						$rek_inventory_or_ap = '213'; // Hutang
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

		if (empty($transactions)) {
			return $this->output->set_content_type('application/json')
				->set_status_header(400)
				->set_output(json_encode(['status' => 'error', 'message' => 'Tidak ada transaksi valid. Pastikan format: [Pelanggan] - [Suplier] - [Deskripsi] - [Ukuran] - [Modal]|[Harga]']));
		}

		// Auto-fetch no_jurnal dan no_bukti dari Database
		$max_jurnal = $this->db->query("SELECT MAX(CAST(no_jurnal AS UNSIGNED)) as max_val FROM jurnal_umum")->row()->max_val;
		$max_bukti = $this->db->query("SELECT MAX(CAST(no_bukti AS UNSIGNED)) as max_val FROM jurnal_umum")->row()->max_val;
		
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
				$up['no_jurnal']=$this->input->post('no_jurnal');
				$up['tgl_jurnal']=$this->app_model->tgl_sql($this->input->post('tgl'));
				$up['ket']=$this->input->post('ket');
				$up['no_bukti']=$this->input->post('no_bukti');
				$up['no_rek']=$this->input->post('no_rek');
				$up['debet']=str_replace(',','',$this->input->post('debet'));
				$up['kredit']=str_replace(',','',$this->input->post('kredit'));
				$up['username']=$this->session->userdata('username');
				$up['tgl_insert']=date('Y-m-d h:m:s');
				
				$id['no_jurnal']=$this->input->post('no_jurnal');
				$id['no_rek']=$this->input->post('no_rek');
				
				$no_jurnal 	=$this->input->post('no_jurnal');
				$no_rek 	=$this->input->post('no_rek');
				
				$text = "SELECT * FROM jurnal_umum WHERE no_jurnal='$no_jurnal' AND no_rek='$no_rek'";
				$data = $this->app_model->manualQuery($text); //$this->app_model->getSelectedData("jurnal_umum",$id);
				if($data->num_rows()>0){
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
		
			//echo $text;
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
			$d['data'] = $this->app_model->manualQuery($text);
			
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