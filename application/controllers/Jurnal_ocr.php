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

        // Call Gemini API
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $api_key;
        
        $prompt = "Tolong analisis gambar nota ini dan ekstrak transaksi-transaksinya menjadi format baris teks persis seperti ini:
DD - MM - YYYY
[Pelanggan] - [Suplier] - [Deskripsi] - [Ukuran] - [Modal]

Aturannya:
1. Baris pertama HANYA tanggal transaksi di nota (format DD - MM - YYYY).
2. Baris kedua dan seterusnya adalah baris barang/transaksi.
3. [Pelanggan] SELALU diisi dengan teks \"Sevencols\" secara hardcode.
4. [Suplier] diambil dari nama toko yang ada di nota (misal HiATA Clothing).
5. [Deskripsi] diambil HANYA dari urutan teks berikut ini: \"" . $nama_order . "\". Jika ada banyak barang di nota, pisahkan teks \"" . $nama_order . "\" dengan koma (,) dan berikan deskripsi yang sesuai untuk setiap baris barang secara berurutan. Misalnya jika input adalah \"Order A, Order B\" dan ada 2 barang di nota, maka barang 1 deskripsinya \"Order A\" dan barang 2 deskripsinya \"Order B\".
6. [Ukuran] diambil dari JUMLAH KUANTITAS (Banyaknya/Qty) barang tersebut di nota.
7. [Modal] diambil dari TOTAL HARGA (Subtotal barang tersebut) di nota, BUKAN harga satuannya.
8. Jangan tambahkan penjelasan, markdown, awalan, atau akhiran apapun. Hanya kembalikan teks hasil akhirnya saja.";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inline_data" => [
                                "mime_type" => $mime_type,
                                "data" => $base64_image
                            ]
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "maxOutputTokens" => 800
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local env if ssl issues

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error', 
                    'message' => 'Gagal menghubungi API Gemini. HTTP Code: ' . $http_code,
                    'debug' => $response
                ]));
        }

        $res_json = json_decode($response, true);
        
        if (!isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error', 
                    'message' => 'Respon API tidak sesuai format yang diharapkan.',
                    'debug' => $response
                ]));
        }

        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text']);
        // Strip out markdown code blocks if gemini still gives it
        $generated_text = preg_replace('/```[a-z]*\n/i', '', $generated_text);
        $generated_text = str_replace('```', '', $generated_text);
        $generated_text = trim($generated_text);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success', 
                'data' => $generated_text
            ]));
    }
}
