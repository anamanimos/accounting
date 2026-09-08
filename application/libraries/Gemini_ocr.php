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
     * Fetch Gemini API key (from AI Studio or DB settings)
     */
    public function get_gemini_api_key() {
        $key = '';
        if (isset($this->CI->app_model) && method_exists($this->CI->app_model, 'get_setting')) {
            $key = $this->CI->app_model->get_setting('gemini_api_key', '');
            if (empty($key)) {
                $key = $this->CI->app_model->get_setting('google_api_key', '');
            }
        }
        if (empty($key) && class_exists('Env')) {
            $key = Env::get('GEMINI_API_KEY');
        }
        return trim($key);
    }

    /**
     * Fetch Google Cloud Vision API key
     */
    public function get_vision_api_key() {
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

    public function get_api_key() {
        return $this->get_gemini_api_key();
    }

    /**
     * Get list of models supported by the provided API Key from Google API
     */
    public function get_available_models($custom_key = null) {
        $api_key = !empty($custom_key) ? trim($custom_key) : $this->get_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'API Key belum diisi.'];
        }

        $endpoints = ['v1beta', 'v1'];
        $found_models = [];
        $last_code = 0;
        $last_response = '';

        foreach ($endpoints as $ver) {
            $url = "https://generativelanguage.googleapis.com/" . $ver . "/models?key=" . $api_key;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $data = json_decode($response, true);
                if (isset($data['models']) && is_array($data['models'])) {
                    foreach ($data['models'] as $m) {
                        $name = str_replace('models/', '', $m['name'] ?? '');
                        $methods = $m['supportedGenerationMethods'] ?? [];
                        if (in_array('generateContent', $methods) && !empty($name)) {
                            $found_models[] = [
                                'name' => $name,
                                'version' => $ver,
                                'displayName' => $m['displayName'] ?? $name,
                                'description' => $m['description'] ?? ''
                            ];
                        }
                    }
                }
                if (!empty($found_models)) {
                    return [
                        'success' => true,
                        'endpoint' => $ver,
                        'models' => $found_models,
                        'raw' => $response
                    ];
                }
            } else {
                $last_code = $http_code;
                $last_response = $response;
            }
        }

        return [
            'success' => false,
            'http_code' => $last_code,
            'error' => 'Gagal mengambil daftar model dari Google API (HTTP ' . $last_code . ').',
            'debug' => $last_response
        ];
    }

    /**
     * Helper to call Gemini API trying multiple endpoints (v1beta, v1) and model candidates
     */
    protected function call_gemini_api($payload, $api_key, $primary_model = 'gemini-1.5-flash') {
        $endpoints = ['v1beta'];
        $primary_model = str_replace('models/', '', trim($primary_model));

        $models_to_try = array_unique(array_filter([
            $primary_model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-latest',
            'gemini-1.5-flash'
        ]));

        $last_response = '';
        $last_http_code = 0;
        $used_model = '';
        $used_endpoint = '';

        // Phase 1: Try standard endpoints & models
        foreach ($endpoints as $ver) {
            foreach ($models_to_try as $model) {
                $url = "https://generativelanguage.googleapis.com/" . $ver . "/models/" . $model . ":generateContent?key=" . $api_key;
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code === 200) {
                    return [
                        'success' => true,
                        'http_code' => 200,
                        'endpoint' => $ver,
                        'model' => $model,
                        'response' => $response
                    ];
                }

                $last_http_code = $http_code;
                $last_response = $response;
                $used_model = $model;
                $used_endpoint = $ver;
            }
        }

        // Phase 2: Dynamically discover models supported by this specific API Key
        $model_list_res = $this->get_available_models($api_key);
        if ($model_list_res['success'] && !empty($model_list_res['models'])) {
            $ver = $model_list_res['endpoint'];
            foreach ($model_list_res['models'] as $m_info) {
                $model = $m_info['name'];
                $url = "https://generativelanguage.googleapis.com/" . $ver . "/models/" . $model . ":generateContent?key=" . $api_key;

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

                if ($http_code === 200) {
                    return [
                        'success' => true,
                        'http_code' => 200,
                        'endpoint' => $ver,
                        'model' => $model,
                        'response' => $response
                    ];
                }
                $last_http_code = $http_code;
                $last_response = $response;
                $used_model = $model;
            }
        } elseif (!$model_list_res['success'] && !empty($model_list_res['debug'])) {
            return [
                'success' => false,
                'http_code' => $model_list_res['http_code'],
                'endpoint' => 'v1beta',
                'model' => $primary_model,
                'response' => $model_list_res['debug']
            ];
        }

        return [
            'success' => false,
            'http_code' => $last_http_code,
            'endpoint' => $used_endpoint,
            'model' => $used_model,
            'response' => $last_response
        ];
    }

    /**
     * Test Gemini API Connection
     */
    public function test_gemini($custom_key = null, $custom_model = 'gemini-1.5-flash') {
        $api_key = !empty($custom_key) ? trim($custom_key) : $this->get_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'API Key belum diisi.'];
        }

        $model = !empty($custom_model) ? trim($custom_model) : 'gemini-1.5-flash';
        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => "Ping test. Jawab singkat 'OK: Gemini API Terhubung'."]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "maxOutputTokens" => 100
            ]
        ];

        $res = $this->call_gemini_api($payload, $api_key, $model);

        if (!$res['success']) {
            $res_json = json_decode($res['response'], true);
            $msg = $res_json['error']['message'] ?? ('HTTP Error ' . $res['http_code']);
            return ['success' => false, 'error' => 'Google Gemini API Error (' . $res['http_code'] . '): ' . $msg, 'debug' => $res['response']];
        }

        $res_json = json_decode($res['response'], true);
        $text = trim($res_json['candidates'][0]['content']['parts'][0]['text'] ?? 'Respon kosong');

        return [
            'success' => true,
            'message' => 'Koneksi Gemini API (' . $res['model'] . ' / ' . $res['endpoint'] . ') Berhasil!',
            'response' => $text
        ];
    }

    /**
     * Test Google Cloud Vision API Connection
     */
    public function test_vision($custom_key = null) {
        $api_key = !empty($custom_key) ? trim($custom_key) : $this->get_vision_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'API Key belum diisi.'];
        }

        $test_base64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=";
        $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key;
        $payload = [
            "requests" => [
                [
                    "image" => [
                        "content" => $test_base64
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if (!empty($curl_err)) {
            return ['success' => false, 'error' => 'cURL Error: ' . $curl_err];
        }

        if ($http_code !== 200) {
            $res_json = json_decode($response, true);
            $msg = $res_json['error']['message'] ?? ('HTTP Error ' . $http_code);
            return ['success' => false, 'error' => 'Google Cloud Vision API Error (' . $http_code . '): ' . $msg, 'debug' => $response];
        }

        return ['success' => true, 'message' => 'Koneksi Google Cloud Vision API (Free Tier) Berhasil!', 'response' => 'Status HTTP 200 OK. Feature TEXT_DETECTION aktif.'];
    }

    /**
     * Process receipt using Google Cloud Vision API or Gemini (Free Tier)
     */
    public function process_receipt($base64_image, $nama_order, $mime_type = 'image/jpeg') {
        $api_key = $this->get_gemini_api_key();
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'Google Cloud / Gemini API Key belum disetel. Silakan atur di Halaman Settings atau .env.'];
        }

        // Auto detect mime type from base64 binary header if not specified or default
        if (!empty($base64_image)) {
            $raw_bytes = base64_decode(substr($base64_image, 0, 32));
            if (substr($raw_bytes, 0, 8) === "\x89PNG\r\n\x1a\n") {
                $mime_type = 'image/png';
            } elseif (substr($raw_bytes, 0, 4) === "RIFF" && substr($raw_bytes, 8, 4) === "WEBP") {
                $mime_type = 'image/webp';
            }
        }

        $ocr_provider = 'gemini_flash';
        $gemini_model = 'gemini-2.5-flash';

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
        $vision_key = $this->get_vision_api_key();
        $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $vision_key;
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

        $res = $this->call_gemini_api($payload, $api_key, 'gemini-1.5-flash');

        if (!$res['success']) {
            return ['success' => false, 'error' => 'Gagal menstrukturkan teks dengan Gemini API. HTTP Code: ' . $res['http_code'], 'debug' => $res['response']];
        }

        $res_json = json_decode($res['response'], true);
        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text'] ?? '');
        $generated_text = preg_replace('/```json/i', '', $generated_text);
        $generated_text = preg_replace('/```/i', '', $generated_text);
        $generated_text = trim($generated_text);

        return $this->format_json_to_legacy($generated_text, $nama_order);
    }

    /**
     * Parse various date formats including Indonesian month names into YYYY-MM-DD
     */
    public function parse_date_indonesia($text) {
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

    /**
     * Process directly via Gemini Multimodal Vision API
     */
    protected function process_via_gemini($base64_image, $nama_order, $api_key, $primary_model = 'gemini-1.5-flash', $mime_type = 'image/jpeg') {
        $prompt = "Tolong analisis gambar nota ini dan ekstrak SEMUA transaksi/barang ke dalam format array JSON persis seperti contoh ini:
[
  {
    \"tanggal\": \"DD-MM-YYYY\",
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
3. 'tanggal' WAJIB diisi TANGGAL TRANSAKSI YANG TERCANTUM PADA NOTA (misal nota tertulis tanggal 14 Juli 2026, isi: \"14-07-2026\"). JANGAN gunakan tanggal hari ini jika di nota ada tanggal/waktu transaksi.
4. 'pelanggan' SELALU diisi teks \"Sevencols\" secara hardcode.
5. 'suplier' diambil dari nama toko yang menerbitkan nota (misal HiATA Clothing, PE, dll).
6. 'deskripsi' diambil dari teks caption ini: \"" . $nama_order . "\". Jika ada beberapa barang, sesuaikan/pecah caption ini per barang. Jika tidak, gunakan caption utuh.
7. 'ukuran' WAJIB diisi dengan angka kuantitas/Banyaknya barang (integer).
8. 'modal' WAJIB DIISI! Diambil dari nominal di kolom 'Jumlah' atau total harga khusus untuk baris barang tersebut, BUKAN harga satuan. WAJIB diisi berupa angka bulat (integer) TANPA titik/koma (contoh: 262500).";

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
                "maxOutputTokens" => 4096
            ]
        ];

        $res = $this->call_gemini_api($payload, $api_key, $primary_model);

        if (!$res['success']) {
            return ['success' => false, 'error' => 'Gagal menghubungi API Google Cloud / Gemini. HTTP Code: ' . $res['http_code'], 'debug' => $res['response']];
        }

        $res_json = json_decode($res['response'], true);
        file_put_contents(FCPATH . 'gemini_raw_response.txt', $res['response']); // DEBUG
        
        if (!isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => false, 'error' => 'Respon API tidak sesuai format yang diharapkan.', 'debug' => $res['response']];
        }

        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text']);
        $generated_text = preg_replace('/```json/i', '', $generated_text);
        $generated_text = preg_replace('/```/i', '', $generated_text);
        $generated_text = trim($generated_text);

        return $this->format_json_to_legacy($generated_text, $nama_order);
    }

    /**
     * Parse JSON output or raw text to legacy dash-separated lines for journal builder
     */
    protected function format_json_to_legacy($generated_text, $nama_order = 'Nota AI') {
        // 1. Try to extract JSON array using Regex
        $clean_json = '';
        if (preg_match('/\[\s*\{[\s\S]*\}\s*\]/', $generated_text, $matches)) {
            $clean_json = $matches[0];
        } else {
            $clean_json = $generated_text;
        }

        $json_data = json_decode($clean_json, true);
        if (!is_array($json_data)) {
            $json_data = json_decode($generated_text, true);
        }

        // Repair partial/truncated JSON array if needed
        if (!is_array($json_data) && strpos($generated_text, '[') !== false) {
            $repaired_json = preg_replace('/,\s*"[^"]*"?\s*:?\s*"?[^"]*$/', '', $generated_text);
            $repaired_json = rtrim($repaired_json, " \t\n\r,");
            if (substr($repaired_json, -1) === '}') {
                $repaired_json .= ']';
            } elseif (substr($repaired_json, -1) !== ']') {
                $repaired_json .= '}]';
            }
            $json_data = json_decode($repaired_json, true);
        }

        if (is_array($json_data) && count($json_data) > 0) {
            $raw_tgl = $json_data[0]['tanggal'] ?? '';
            $parsed_tgl = $this->parse_date_indonesia($raw_tgl);
            
            if ($parsed_tgl) {
                $parts = explode('-', $parsed_tgl);
                $formatted_date = "{$parts[2]} - {$parts[1]} - {$parts[0]}";
            } else {
                $formatted_date = date('d - m - Y');
            }

            $output_lines = [$formatted_date];
            foreach ($json_data as $item) {
                $p = !empty($item['pelanggan']) ? trim($item['pelanggan']) : 'Sevencols';
                $s = !empty($item['suplier']) ? trim($item['suplier']) : 'Suplier Utama';
                $d = !empty($item['deskripsi']) ? trim($item['deskripsi']) : $nama_order;
                $u = isset($item['ukuran']) ? trim($item['ukuran']) : '0';
                $m = isset($item['modal']) ? (int) preg_replace('/[^\d]/', '', (string)$item['modal']) : 0;

                // Clean dash symbols from text fields to avoid breaking explode(' - ', $line)
                $p = str_replace('-', ' ', $p);
                $s = str_replace('-', ' ', $s);
                $d = str_replace('-', ' ', $d);

                $output_lines[] = "$p - $s - $d - $u - $m";
            }
            return ['success' => true, 'text' => implode("\n", $output_lines)];
        }

        // 2. Fallback parsing if JSON decoding fails
        $lines = explode("\n", str_replace("\r", "", $generated_text));
        $formatted_date = date('d - m - Y');
        $output_lines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parsed_line_tgl = $this->parse_date_indonesia($line);
            if ($parsed_line_tgl) {
                $parts = explode('-', $parsed_line_tgl);
                $formatted_date = "{$parts[2]} - {$parts[1]} - {$parts[0]}";
                continue;
            }

            if (strpos($line, '-') !== false || strpos($line, '|') !== false) {
                $parts = preg_split('/[\-\|]/', $line);
                $clean_parts = array_map('trim', $parts);
                if (count($clean_parts) >= 4) {
                    $p = $clean_parts[0] ?: 'Sevencols';
                    $s = $clean_parts[1] ?: 'Suplier Utama';
                    $d = $clean_parts[2] ?: $nama_order;
                    $u = preg_replace('/[^\d]/', '', $clean_parts[3]) ?: '1';
                    $m = isset($clean_parts[4]) ? (int) preg_replace('/[^\d]/', '', $clean_parts[4]) : 0;

                    $p = str_replace('-', ' ', $p);
                    $s = str_replace('-', ' ', $s);
                    $d = str_replace('-', ' ', $d);

                    $output_lines[] = "$p - $s - $d - $u - $m";
                }
            }
        }

        if (!empty($output_lines)) {
            array_unshift($output_lines, $formatted_date);
            return ['success' => true, 'text' => implode("\n", $output_lines)];
        }

        return ['success' => true, 'text' => $generated_text];
    }
}
