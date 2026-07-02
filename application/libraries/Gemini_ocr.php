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
8. PENTING: [Modal] WAJIB diisi! Tulis angkanya TANPA titik/koma (contoh: Rp 90.000 wajib ditulis 90000). Jangan potong angka nol-nya.
9. Jangan tambahkan penjelasan, markdown, awalan, atau akhiran apapun. Hanya kembalikan teks hasil akhirnya saja.";

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
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
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
        
        if (!isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => false, 'error' => 'Respon API tidak sesuai format yang diharapkan.', 'debug' => $response];
        }

        $generated_text = trim($res_json['candidates'][0]['content']['parts'][0]['text']);
        // Strip out markdown code blocks if gemini still gives it
        $generated_text = preg_replace('/```[a-z]*\n/i', '', $generated_text);
        $generated_text = str_replace('```', '', $generated_text);
        $generated_text = trim($generated_text);

        return ['success' => true, 'text' => $generated_text];
    }
}
