<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('app_model');
	}

	public function index()
	{
		if (empty($this->session->userdata('logged_in'))) {
			redirect('login');
		}

		$d['judul'] = "Pengaturan Sistem (Settings)";
		$d['title'] = "Pengaturan Sistem";

		$d['user'] = (object) [
			'nama_lengkap' => $this->session->userdata('nama_lengkap'),
			'level'        => $this->session->userdata('level'),
			'email'        => $this->session->userdata('username') . '@accounting.test'
		];

		// Program config
		$d['prg']            = $this->config->item('prg');
		$d['web_prg']        = $this->config->item('web_prg');
		$d['nama_program']   = $this->config->item('nama_program');
		$d['instansi']       = $this->config->item('instansi');
		$d['usaha']          = $this->config->item('usaha');
		$d['alamat_instansi']= $this->config->item('alamat_instansi');

		// Fetch all key-value settings
		$d['all_settings']   = $this->app_model->get_all_settings();
		$d['google_api_key'] = $this->app_model->get_setting('google_api_key', '');
		$d['ocr_provider']   = $this->app_model->get_setting('ocr_provider', 'gemini_flash');
		$d['gemini_model']   = $this->app_model->get_setting('gemini_model', 'gemini-1.5-flash');

		$d['content'] = 'settings/index';
		$this->load->view('templates/main', $d);
	}

	public function simpan()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$google_api_key = trim($this->input->post('google_api_key'));
		$ocr_provider   = trim($this->input->post('ocr_provider'));
		$gemini_model   = trim($this->input->post('gemini_model'));

		$this->app_model->set_setting('google_api_key', $google_api_key);
		$this->app_model->set_setting('ocr_provider', $ocr_provider);
		$this->app_model->set_setting('gemini_model', $gemini_model);

		// If extra dynamic key-value pairs are sent
		$keys   = $this->input->post('kv_key');
		$values = $this->input->post('kv_value');

		if (is_array($keys) && is_array($values)) {
			for ($i = 0; $i < count($keys); $i++) {
				$k = trim($keys[$i]);
				$v = trim($values[$i]);
				if (!empty($k)) {
					$this->app_model->set_setting($k, $v);
				}
			}
		}

		$this->session->set_flashdata('result_setting', 'Pengaturan berhasil disimpan!');
		redirect('settings');
	}

	public function simpan_kv()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$key   = trim($this->input->post('key'));
		$value = trim($this->input->post('value'));

		if (empty($key)) {
			return $this->output->set_content_type('application/json')
				->set_output(json_encode(['status' => 'error', 'message' => 'Key tidak boleh kosong']));
		}

		$this->app_model->set_setting($key, $value);

		return $this->output->set_content_type('application/json')
			->set_output(json_encode(['status' => 'success', 'message' => 'Key-Value berhasil disimpan']));
	}

	public function hapus_kv()
	{
		if (empty($this->session->userdata('logged_in'))) {
			return $this->output->set_content_type('application/json')
				->set_status_header(401)
				->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
		}

		$key = trim($this->input->post('key'));
		if (!empty($key)) {
			$this->app_model->delete_setting($key);
			return $this->output->set_content_type('application/json')
				->set_output(json_encode(['status' => 'success', 'message' => 'Setting berhasil dihapus']));
		}

		return $this->output->set_content_type('application/json')
			->set_output(json_encode(['status' => 'error', 'message' => 'Key tidak valid']));
	}
}
