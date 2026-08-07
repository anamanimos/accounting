<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gemini_ocr {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }
        $this->CI->load->model('app_model');
    }

    /**
     * Fetch Google Cloud / Gemini API key from DB settings table, fallback to .env
     */
    public function get_api_key() {
        $key = '';
        if (isset($this->CI->app_model) && method_exists($this->CI->app_model, 'get_setting')) {
            $key = $this->CI->app_model->get_setting('google_api_key', '');
            if (empty($key)) {
                $key = $this->CI->app_model->get_setting('gemini_api_key', '');
            }
        }
        if (empty($key) && class_exists('Env')) {
            $key = Env::get('GEMINI_API_KEY');
        }
        return trim($key);
    }

    /**
     * Process receipt using Google Cloud Vision API or Gemini (Free Tier)
     */
    public function process_receipt($base64_image, $nama_order, $mime_type = 'image/jpeg') {
        $api_key = $this->get_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'Google Cloud / Gemini API Key belum disetel. Silakan atur di Halaman Settings atau .env.'];
        }

        $ocr_provider = 'gemini_flash';
        $gemini_model = 'gemini-1.5-flash';

        if (isset($this->CI->app_model) && method_exists($this->CI->app_model, 'get_setting')) {
            $ocr_provider = $this->CI->app_model->get_setting('ocr_provider', 'gemini_flash');
            $gemini_model = $this->CI->app_model->get_setting('gemini_model', 'gemini-1.5-flash');
        }

        // If provider is Google Cloud Vision API
        if ($ocr_provider === 'vision_api') {
            return $this->process_via_cloud_vision($base64_image, $nama_order, $api_key);
        }

        // Default or Combined: process using Gemini
        return $this->process_via_gemini($base64_image, $nama_order, $api_key, $gemini_model, $mime_type);
    }

    /**
     * Process using Google Cloud Vision API (Free Tier - 1,000 units/mo)
     */
    protected function process_via_cloud_vision($base64_image, $nama_order, $api_key) {
        $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key;
        $payload = [
            "requests" => [
                [
                    "image" => [
                        "content" => $base64_image
                    ],
                    "features" => [
                        [
                            "type" => "TEXT_DETECTION"
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || empty($response)) {
            // Fallback to Gemini if Vision API encounters error
            return $this->process_via_gemini($base64_image, $nama_order, $api_key, 'gemini-1.5-flash');
        }

        $res_json = json_decode($response, true);
        $extracted_text = $res_json['responses'][0]['fullTextAnnotation']['text'] ?? '';

        if (empty($extracted_text)) {
            return ['success' => false, 'error' => 'Google Cloud Vision API tidak menemukan teks pada gambar ini.'];
        }

        // Structure raw OCR text with Gemini
        return $this->structure_ocr_text_with_gemini($extracted_text, $nama_order, $api_key);
    }

    /**
     * Structure raw OCR text with Gemini
     */
    protected function structure_ocr_text_with_gemini($raw_ocr_text, $nama_order, $api_key) {
        $prompt = "Tolong analisis hasil OCR teks nota ini dan ekstrak SEMUA transaksi/barang ke dalam format array JSON persis seperti contoh ini:
[
  {
    \"tanggal\": \"DD - MM - YYYY\",
    \"pelanggan\": \"Sevencols\",
    \"suplier\": \"[Nama Toko]\",
    \"deskripsi\": \"[Caption Barang]\",
    \"ukuran\": [Kuantitas],
    \"modal\": [Total Harga]
  }
]

Teks Hasil OCR Nota:
\"\"\"
" . $raw_ocr_text . "
\"\"\"

Aturannya:
1. Output WAJIB berupa JSON array murni tanpa tambahan teks/penjelasan atau markdown.
2. EKSTRAK SEMUA BARANG.
3. 'tanggal' diisi tanggal transaksi di nota (format DD - MM - YYYY).
4. 'pelanggan' SELALU diisi teks \"Sevencols\".
5. 'suplier' diambil dari nama toko di nota (misal HiATA Clothing).
6. 'deskripsi' diambil dari caption ini: \"" . $nama_order . "\".
7. 'ukuran' WAJIB diisi angka kuantitas/Banyaknya barang (integer).
8. 'modal' WAJIB DIISI! Nominal dari kolom Jumlah/Subtotal (integer bulat tanpa titik/koma).";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "maxOutputTokens" => 800
            ]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        $response = curl_exec($ch);
        curl_close($ch);

        $res_json = json_decode($response, true);
        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text'] ?? '');
        $generated_text = preg_replace('/```json/i', '', $generated_text);
        $generated_text = preg_replace('/```/i', '', $generated_text);
        $generated_text = trim($generated_text);

        return $this->format_json_to_legacy($generated_text);
    }

    /**
     * Process directly via Gemini Multimodal Vision API
     */
    protected function process_via_gemini($base64_image, $nama_order, $api_key, $primary_model = 'gemini-1.5-flash', $mime_type = 'image/jpeg') {
        $prompt = "Tolong analisis gambar nota ini dan ekstrak SEMUA transaksi/barang ke dalam format array JSON persis seperti contoh ini:
[
  {
    \"tanggal\": \"DD - MM - YYYY\",
    \"pelanggan\": \"Sevencols\",
    \"suplier\": \"[Nama Toko]\",
    \"deskripsi\": \"[Caption Barang]\",
    \"ukuran\": [Kuantitas],
    \"modal\": [Total Harga]
  }
]

Aturannya:
1. Output WAJIB berupa JSON array murni tanpa tambahan teks/penjelasan atau markdown (tanpa awalan ```json).
2. EKSTRAK SEMUA BARANG. Jika nota memiliki 3 macam barang, array JSON harus berisi 3 object.
3. 'tanggal' diisi tanggal transaksi di nota dengan format DD - MM - YYYY (harus ada spasi).
4. 'pelanggan' SELALU diisi teks \"Sevencols\" secara hardcode.
5. 'suplier' diambil dari nama toko yang menerbitkan nota (misal HiATA Clothing).
6. 'deskripsi' diambil dari teks caption ini: \"" . $nama_order . "\". Jika ada beberapa barang, sesuaikan/pecah caption ini per barang. Jika tidak, gunakan caption utuh.
7. 'ukuran' WAJIB diisi dengan angka kuantitas/Banyaknya barang (integer).
8. 'modal' WAJIB DIISI! Diambil dari nominal di kolom 'Jumlah' atau total harga khusus untuk baris barang tersebut, BUKAN harga satuan. WAJIB diisi berupa angka bulat (integer) TANPA titik/koma (contoh: 262500). Jangan potong angka nol-nya.";

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

        $models_to_try = array_unique([
            $primary_model,
            'gemini-1.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-latest'
        ]);

        $http_code = 0;
        $response = '';

        foreach ($models_to_try as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . $api_key;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 25);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                curl_close($ch);
                continue;
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                break;
            }
        }

        if ($http_code !== 200) {
            return ['success' => false, 'error' => 'Gagal menghubungi API Google Cloud / Gemini. HTTP Code: ' . $http_code, 'debug' => $response];
        }

        $res_json = json_decode($response, true);
        file_put_contents(FCPATH . 'gemini_raw_response.txt', $response); // DEBUG
        
        if (!isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => false, 'error' => 'Respon API tidak sesuai format yang diharapkan.', 'debug' => $response];
        }

        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text']);
        $generated_text = preg_replace('/```json/i', '', $generated_text);
        $generated_text = preg_replace('/```/i', '', $generated_text);
        $generated_text = trim($generated_text);

        return $this->format_json_to_legacy($generated_text);
    }

    /**
     * Parse JSON output to legacy dash-separated lines for journal builder
     */
    protected function format_json_to_legacy($generated_text) {
        $json_data = json_decode($generated_text, true);
        if (is_array($json_data) && count($json_data) > 0) {
            $tanggal = $json_data[0]['tanggal'] ?? date('d - m - Y');
            $tanggal = str_replace('-', ' - ', str_replace(' - ', '-', $tanggal));
            
            $output_lines = [$tanggal];
            foreach ($json_data as $item) {
                $p = $item['pelanggan'] ?? 'Sevencols';
                $s = $item['suplier'] ?? '';
                $d = $item['deskripsi'] ?? '';
                $u = $item['ukuran'] ?? 0;
                $m = $item['modal'] ?? 0;
                $output_lines[] = "$p - $s - $d - $u - $m";
            }
            $generated_text = implode("\n", $output_lines);
        }

        return ['success' => true, 'text' => $generated_text];
    }
}
