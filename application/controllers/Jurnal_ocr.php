<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jurnal_ocr extends CI_Controller {

	public function index()
	{
		if (empty($this->session->userdata('logged_in'))) {
			redirect('login');
		}

		$d['judul'] = "Jurnal OCR (Nota AI)";
		$d['title'] = "Jurnal OCR (Nota AI)";
		
		$d['user'] = (object) [
			'nama_lengkap' => $this->session->userdata('nama_lengkap'),
			'level'        => $this->session->userdata('level'),
			'email'        => $this->session->userdata('username') . '@accounting.test'
		];

        // Ensure Env class is loaded
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }

		$d['has_api_key'] = (class_exists('Env') && !empty(Env::get('GEMINI_API_KEY')));

		$d['content'] = 'jurnal_umum/jurnal_ocr';
		$this->load->view('templates/main', $d);
	}

    public function scan()
    {
        if (empty($this->session->userdata('logged_in'))) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
        }

        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }

        $api_key = class_exists('Env') ? Env::get('GEMINI_API_KEY') : '';

        if (empty($api_key)) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gemini API Key belum disetel di file .env.']));
        }

        $nama_order = $this->input->post('nama_order');
        if (empty($nama_order)) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Nama Order wajib diisi.']));
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gambar nota wajib diunggah.']));
        }

        // Validate mime type
        $mime_type = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Harap unggah JPG, PNG, atau PDF.']));
        }

        $base64_image = base64_encode(file_get_contents($_FILES['image']['tmp_name']));

        $this->load->library('gemini_ocr');
        $result = $this->gemini_ocr->process_receipt($base64_image, $nama_order, $mime_type);

        if (!$result['success']) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error', 
                    'message' => $result['error'],
                    'debug' => isset($result['debug']) ? $result['debug'] : ''
                ]));
        }

        $generated_text = $result['text'];

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success', 
                'data' => $generated_text
            ]));
    }
}
