<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

#[AllowDynamicProperties]
class Home extends CI_Controller {

	/**
	 * @author : Deddy Rusdiansyah,S.Kom
	 * @web : http://deddyrusdiansyah.blogspot.com
	 * @keterangan : Controller untuk halaman awal ketika aplikasi  diakses
	 **/
	public function index()
	{
		$cek = $this->session->userdata('logged_in');
		if(!empty($cek)){
			$d['prg']= $this->config->item('prg');
			$d['web_prg']= $this->config->item('web_prg');
			
			$d['nama_program']= $this->config->item('nama_program');
			$d['instansi']= $this->config->item('instansi');
			$d['usaha']= $this->config->item('usaha');
			$d['alamat_instansi']= $this->config->item('alamat_instansi');

			$d['judul']="Home";

			// User Object for View
			$d['user'] = (object) [
				'nama_lengkap' => $this->session->userdata('nama_lengkap'),
				'level'        => $this->session->userdata('level'),
				'email'        => $this->session->userdata('username') . '@accounting.test' // mock email or leave blank
			];

			$d['title'] = "Dashboard Utama";
			$d['content'] = 'dashboard/index';
			$this->load->view('templates/main',$d);
		}else{
			header('location:'.base_url().'login');
		}
	}
	
	public function logout(){
		$cek = $this->session->userdata('logged_in');
		if(empty($cek))
		{
			redirect('login');
		}else{
			$this->session->sess_destroy();
			redirect('login');
		}
	}
	
	
}

/* End of file index.php */
/* Location: ./application/controllers/index.php */