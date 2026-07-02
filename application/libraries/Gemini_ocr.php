<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gemini_ocr {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        // Ensure Env class is loaded
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }
    }

    public function process_receipt($base64_image, $nama_order, $mime_type = 'image/jpeg') {
        $api_key = class_exists('Env') ? Env::get('GEMINI_API_KEY') : '';
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'Gemini API Key belum disetel di file .env.'];
        }

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

        $models_to_try = [
            'gemini-1.5-flash',
            'gemini-2.0-flash',
            'gemini-flash-latest'
        ];

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
                $error_msg = curl_error($ch);
                curl_close($ch);
                continue;
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Jika sukses, break dari loop
            if ($http_code === 200) {
                break;
            }
        }

        if ($http_code !== 200) {
            return ['success' => false, 'error' => 'Gagal menghubungi API Gemini walau sudah mencoba berbagai versi model. HTTP Code: ' . $http_code, 'debug' => $response];
        }

        $res_json = json_decode($response, true);
        file_put_contents('gemini_raw_response.txt', $response); // DEBUG
        
        if (!isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => false, 'error' => 'Respon API tidak sesuai format yang diharapkan.', 'debug' => $response];
        }

        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text']);
        // Strip out markdown code blocks if gemini still gives it
        $generated_text = preg_replace('/```json/i', '', $generated_text);
        $generated_text = preg_replace('/```/i', '', $generated_text);
        $generated_text = trim($generated_text);

        // Attempt to parse JSON and convert to legacy format
        $json_data = json_decode($generated_text, true);
        if (is_array($json_data) && count($json_data) > 0) {
            // First line is the date from the first item
            $tanggal = $json_data[0]['tanggal'] ?? date('d - m - Y');
            // Ensure date has spaces around dashes
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
