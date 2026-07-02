<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webhook_wa extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // Ensure Env class is loaded
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }
    }

    public function index()
    {
        $raw_input = file_get_contents('php://input');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';

        $log_file = FCPATH . 'wa.txt';
        $time = date('Y-m-d H:i:s');
        
        $log_content = "=== WEBHOOK RECEIVED AT " . $time . " ===\n";
        $log_content .= "Method: " . $method . "\n";
        $log_content .= "Payload: " . $raw_input . "\n\n";
        
        file_put_contents($log_file, $log_content, FILE_APPEND);
        
        $data = json_decode($raw_input, true);
        if (!$data || !isset($data['event'])) {
            return $this->_response(['status' => 'invalid_json']);
        }

        // Hanya proses event message
        if ($data['event'] !== 'message') {
            return $this->_response(['status' => 'ignored_not_message']);
        }

        $session_id = $data['session_id'] ?? '';

        // Filter berdasarkan session_id
        if (strpos($session_id, 'erp-damaijaya') === false) {
            return $this->_response(['status' => 'ignored_other_session']);
        }

        $payload = $data['payload'] ?? [];
        if (empty($payload)) {
            return $this->_response(['status' => 'ignored_empty_payload']);
        }

        // Cegah looping bot
        if (isset($payload['is_from_me']) && $payload['is_from_me'] == true) {
            return $this->_response(['status' => 'ignored_from_me']);
        }

        $chat_id = $payload['chat_id'] ?? '';
        $target_group = Env::get('WA_GROUP_ID') ?: '120363426581172416@g.us';

        // Hanya proses pesan dari grup target
        if ($chat_id !== $target_group) {
            return $this->_response(['status' => 'ignored_wrong_group']);
        }

        $message_id = $payload['id'] ?? '';
        $body = trim($payload['body'] ?? '');
        $sender_jid = $payload['from'] ?? '';
        $replied_to_id = $payload['replied_to_id'] ?? null;

        // Cek State Machine (YA / BATAL)
        $upper_body = strtoupper($body);
        if (in_array($upper_body, ['YA', 'BATAL'])) {
            file_put_contents(FCPATH.'wa.txt', "[DEBUG] Detected YA/BATAL. upper_body: $upper_body, replied_to_id: " . ($replied_to_id ?: 'null') . "\n", FILE_APPEND);
            
            $draft = null;
            if ($replied_to_id) {
                $draft = $this->db->get_where('wa_draft_jurnal', ['message_id' => $replied_to_id, 'status' => 'pending'])->row();
            }
            // Jika tidak di-reply ATAU ID tidak ditemukan di DB (karena GOWA tidak return ID), ambil draf terakhir
            if (!$draft) {
                file_put_contents(FCPATH.'wa.txt', "[DEBUG] Draft not found by replied_to_id, falling back to DESC\n", FILE_APPEND);
                $draft = $this->db->order_by('id', 'DESC')->get_where('wa_draft_jurnal', ['status' => 'pending'])->row();
            }

            if ($draft) {
                file_put_contents(FCPATH.'wa.txt', "[DEBUG] Found pending draft ID: {$draft->id}. Processing...\n", FILE_APPEND);
                
                if ($upper_body === 'BATAL') {
                    $this->db->update('wa_draft_jurnal', ['status' => 'rejected'], ['id' => $draft->id]);
                    $this->_send_message($chat_id, "Draf jurnal telah dibatalkan.", $message_id);
                } else {
                    // YA: Simpan ke database
                    $jurnal_data = json_decode($draft->payload_jurnal, true);
                    file_put_contents(FCPATH.'wa.txt', "[DEBUG] Starting DB transaction with payload: " . json_encode($jurnal_data) . "\n", FILE_APPEND);
                    
                    // Matikan db_debug agar script tidak mati tiba-tiba jika ada error SQL
                    $this->db->db_debug = FALSE;
                    $this->db->trans_start();
                    
                    foreach ($jurnal_data as $row) {
                        if (!$this->db->insert('jurnal_umum', $row)) {
                            file_put_contents(FCPATH.'wa.txt', "[DEBUG DB ERROR] " . json_encode($this->db->error()) . "\n", FILE_APPEND);
                        }
                    }
                    $this->db->trans_complete();
                    $trans_status = $this->db->trans_status();
                    $this->db->db_debug = TRUE; // Kembalikan ke normal

                    file_put_contents(FCPATH.'wa.txt', "[DEBUG] DB transaction complete. Status: " . ($trans_status === FALSE ? 'FAILED' : 'SUCCESS') . "\n", FILE_APPEND);

                    if ($trans_status === FALSE) {
                        $this->_send_message($chat_id, "Gagal menyimpan jurnal ke database. Mohon cek log server.", $message_id);
                    } else {
                        $this->db->update('wa_draft_jurnal', ['status' => 'approved'], ['id' => $draft->id]);
                        $this->_send_message($chat_id, "✅ Jurnal berhasil disimpan!", $message_id);
                    }
                }
                return $this->_response(['status' => 'state_processed']);
            } else {
                file_put_contents(FCPATH.'wa.txt', "[DEBUG] NO PENDING DRAFT FOUND AT ALL!\n", FILE_APPEND);
            }
        }

        $is_processing_image = false;
        $image_path_to_process = null;
        $nama_order_to_process = null;
        $prompt = '';

        if (isset($payload['image'])) {
            $image_path = isset($payload['image']['path']) ? $payload['image']['path'] : null;
            if (!$image_path && isset($payload['image']['url'])) {
                $image_path = $payload['image']['url'];
            }
            
            $nama_order = $body;
            if (empty($nama_order)) {
                $sent_msg = $this->_send_message($chat_id, "Silakan balas pesan ini dengan teks keterangan (Nama Order) untuk gambar tersebut:", $message_id);
                
                $bot_msg_id = 'unknown_' . time() . '_' . rand(100, 999);
                if ($sent_msg) {
                    if (isset($sent_msg['results']['message_id'])) {
                        $bot_msg_id = $sent_msg['results']['message_id'];
                    } elseif (isset($sent_msg['data']['id'])) {
                        $bot_msg_id = $sent_msg['data']['id'];
                    } elseif (isset($sent_msg['data']['message_id'])) {
                        $bot_msg_id = $sent_msg['data']['message_id'];
                    } elseif (isset($sent_msg['message_id'])) {
                        $bot_msg_id = $sent_msg['message_id'];
                    }
                }

                $this->db->insert('wa_pending_image', [
                    'message_id' => $bot_msg_id,
                    'image_url' => $image_path,
                    'sender_jid' => $sender_jid,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                return $this->_response(['status' => 'waiting_for_name']);
            } else {
                $is_processing_image = true;
                $image_path_to_process = $image_path;
                $nama_order_to_process = $nama_order;
            }
        } else {
            // Teks biasa. Cek apakah membalas permintaan Nama Order
            $pending = null;
            if ($replied_to_id) {
                $pending = $this->db->get_where('wa_pending_image', ['message_id' => $replied_to_id])->row();
            } else {
                // Cek latest pending request yang belum dijawab
                $pending = $this->db->order_by('id', 'DESC')->get_where('wa_pending_image', ['sender_jid' => $sender_jid])->row();
            }

            if ($pending && !empty($body) && !in_array(strtoupper($body), ['YA', 'BATAL'])) {
                $this->db->delete('wa_pending_image', ['id' => $pending->id]);
                
                $is_processing_image = true;
                $image_path_to_process = $pending->image_url;
                $nama_order_to_process = $body;
                
                $this->_send_message($chat_id, "Memproses gambar dengan nama order: *" . $body . "*...", $message_id);
            } else {
                $prompt = $body;
            }
        }

        if ($is_processing_image) {
            $gateway_url = rtrim(Env::get('WA_GATEWAY_URL') ?: 'https://wag.anam.ch', '/');
            $image_url = (strpos($image_path_to_process, 'http') === 0) ? $image_path_to_process : $gateway_url . '/' . $image_path_to_process;
            
            $base64_image = $this->_download_and_base64($image_url);
            if (!$base64_image) {
                $this->_send_message($chat_id, "Gagal mengunduh gambar nota dari gateway.", $message_id);
                return $this->_response(['status' => 'image_download_failed']);
            }

            $this->load->library('gemini_ocr');
            $gemini_result = $this->gemini_ocr->process_receipt($base64_image, $nama_order_to_process);
            
            if (!$gemini_result['success']) {
                file_put_contents(FCPATH.'wa.txt', "[DEBUG GEMINI ERROR] " . $gemini_result['error'] . "\n", FILE_APPEND);
                $this->_send_message($chat_id, "Gagal: Layanan AI sedang bermasalah / sibuk.", $message_id);
                return $this->_response(['status' => 'gemini_error']);
            }

            file_put_contents(FCPATH.'wa.txt', "[DEBUG GEMINI] Raw Output: \n" . $gemini_result['text'] . "\n", FILE_APPEND);
            $prompt = $gemini_result['text'];
        }

        $transactions = $this->_parse_prompt($prompt);
        if (empty($transactions)) {
            // Jangan balas jika bukan format jurnal agar grup tidak berisik
            file_put_contents(FCPATH.'wa.txt', "[DEBUG] Ignoring message because parsed transactions are empty. Prompt: $prompt\n", FILE_APPEND);
            if (isset($payload['image'])) {
                $this->_send_message($chat_id, "Maaf, AI gagal membaca format nota atau teks balasan terlalu pendek. Silakan coba foto nota yang lebih jelas.", $message_id);
            }
            return $this->_response(['status' => 'ignored_not_prompt']);
        }

        // Ubah jadi array jurnal yang siap insert
        $jurnal_rows = $this->_build_jurnal_array($transactions);

        // Buat pesan balasan preview
        $preview_msg = "*DRAF JURNAL*\n\n";
        $total_modal = 0;
        $total_jual = 0;
        foreach ($transactions as $i => $trx) {
            $preview_msg .= ($i+1) . ". " . $trx['tgl'] . "\n";
            $preview_msg .= "Ket: " . $trx['ket'] . "\n";
            $preview_msg .= "Jual: Rp " . number_format($trx['harga_jual'],0,',','.') . "\n";
            $preview_msg .= "Modal: Rp " . number_format($trx['modal'],0,',','.') . "\n\n";
            $total_jual += $trx['harga_jual'];
            $total_modal += $trx['modal'];
        }
        $preview_msg .= "Total Jual: Rp " . number_format($total_jual,0,',','.') . "\n";
        $preview_msg .= "Total Modal: Rp " . number_format($total_modal,0,',','.') . "\n\n";
        $preview_msg .= "Balas pesan ini dengan kata *YA* untuk menyimpan, atau *BATAL*.";

        // Kirim draft ke grup
        $sent_msg = $this->_send_message($chat_id, $preview_msg, $message_id);
        
        $bot_msg_id = 'unknown_' . time() . '_' . rand(100, 999);
        if ($sent_msg) {
            if (isset($sent_msg['results']['message_id'])) {
                $bot_msg_id = $sent_msg['results']['message_id'];
            } elseif (isset($sent_msg['data']['id'])) {
                $bot_msg_id = $sent_msg['data']['id'];
            } elseif (isset($sent_msg['data']['message_id'])) {
                $bot_msg_id = $sent_msg['data']['message_id'];
            } elseif (isset($sent_msg['message_id'])) {
                $bot_msg_id = $sent_msg['message_id'];
            }
        }
        
        // Selalu simpan ke wa_draft_jurnal agar fitur YA/BATAL berfungsi dengan melihat draf terakhir
        $this->db->insert('wa_draft_jurnal', [
            'message_id' => $bot_msg_id,
            'sender_jid' => $sender_jid,
            'payload_jurnal' => json_encode($jurnal_rows),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->_response(['status' => 'draft_created']);
    }

    private function _response($data)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($data));
    }

    private function _send_message($phone, $message, $reply_to_id = null)
    {
        $gateway_url = rtrim(Env::get('WA_GATEWAY_URL') ?: 'https://wag.anam.ch', '/');
        $username = Env::get('WA_GATEWAY_USERNAME') ?: 'admin';
        $password = Env::get('WA_GATEWAY_PASSWORD') ?: 'admin';
        $device_id = Env::get('WA_DEVICE_ID') ?: 'erp-damaijaya';

        $payload = [
            'phone' => $phone,
            'message' => $message,
            'isGroup' => true,
        ];

        if ($reply_to_id) {
            $payload['reply_to'] = $reply_to_id;
        }

        $ch = curl_init($gateway_url . '/send/message');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Device-Id: ' . $device_id,
            'Authorization: Basic ' . base64_encode($username . ':' . $password),
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function _download_and_base64($url)
    {
        $username = Env::get('WA_GATEWAY_USERNAME') ?: 'admin';
        $password = Env::get('WA_GATEWAY_PASSWORD') ?: 'admin';
        $device_id = Env::get('WA_DEVICE_ID') ?: 'erp-damaijaya';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Device-Id: ' . $device_id,
            'Authorization: Basic ' . base64_encode($username . ':' . $password)
        ]);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $data) {
            return base64_encode($data);
        }
        return false;
    }



    private function _parse_prompt($prompt)
    {
        $lines = explode("\n", str_replace("\r", "", $prompt));
        $current_date = date('Y-m-d');
        $transactions = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(\d{1,2})\s*-\s*(\d{1,2})\s*-\s*(\d{4})$/', $line, $matches)) {
                $day = str_pad($matches[1], 2, "0", STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, "0", STR_PAD_LEFT);
                $year = $matches[3];
                $current_date = "$year-$month-$day";
                continue;
            }

            if (strpos($line, ' - ') !== false) {
                $harga_jual = 0;
                $left_part = $line;

                if (strpos($line, '|') !== false) {
                    $parts = explode('|', $line);
                    $harga_str = trim($parts[1]);
                    $harga_str = str_replace(['.', ','], '', $harga_str);
                    $harga_jual = (int) $harga_str;
                    $left_part = trim($parts[0]);
                }
                
                $dash_parts = explode(' - ', $left_part);
                
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
                        $harga_per_cm = $mh ? $mh->harga_jual : 0;
                        preg_match_all('/\d+/', $ukuran, $matches);
                        $panjang = (!empty($matches[0])) ? (int) end($matches[0]) : 0;
                        $harga_jual = $panjang * $harga_per_cm;
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

    private function _build_jurnal_array($transactions)
    {
        $max_jurnal = $this->db->query("SELECT MAX(CAST(no_jurnal AS UNSIGNED)) as max_val FROM jurnal_umum")->row()->max_val;
        $max_bukti = $this->db->query("SELECT MAX(CAST(no_bukti AS UNSIGNED)) as max_val FROM jurnal_umum")->row()->max_val;
        
        $no_jurnal = $max_jurnal ? $max_jurnal + 1 : 1;
        $no_bukti = $max_bukti ? $max_bukti + 1 : 1;
        
        $rows = [];
        $tgl_insert = date('Y-m-d H:i:s');
        foreach ($transactions as $trx) {
            $modal = $trx['modal'];
            $harga_jual = $trx['harga_jual'];
            $rek_inventory_or_ap = $trx['rek_inventory_or_ap'];
            
            // Baris 1: Pendapatan (411) Kredit harga_jual
            $rows[] = [
                'tgl_jurnal' => $trx['tgl'],
                'ket' => $trx['ket'],
                'no_rek' => '411',
                'debet' => 0,
                'kredit' => $harga_jual,
                'no_jurnal' => str_pad($no_jurnal, 6, "0", STR_PAD_LEFT),
                'no_bukti' => str_pad($no_bukti, 6, "0", STR_PAD_LEFT),
                'username' => 'WA Bot',
                'tgl_insert' => $tgl_insert
            ];
            // Baris 2: Piutang (112) Debit harga_jual
            $rows[] = [
                'tgl_jurnal' => $trx['tgl'],
                'ket' => $trx['ket'],
                'no_rek' => '112',
                'debet' => $harga_jual,
                'kredit' => 0,
                'no_jurnal' => str_pad($no_jurnal, 6, "0", STR_PAD_LEFT),
                'no_bukti' => str_pad($no_bukti, 6, "0", STR_PAD_LEFT),
                'username' => 'WA Bot',
                'tgl_insert' => $tgl_insert
            ];
            // Baris 3: Hutang/Kas (213/118) Kredit modal
            $rows[] = [
                'tgl_jurnal' => $trx['tgl'],
                'ket' => $trx['ket'],
                'no_rek' => $rek_inventory_or_ap,
                'debet' => 0,
                'kredit' => $modal,
                'no_jurnal' => str_pad($no_jurnal, 6, "0", STR_PAD_LEFT),
                'no_bukti' => str_pad($no_bukti, 6, "0", STR_PAD_LEFT),
                'username' => 'WA Bot',
                'tgl_insert' => $tgl_insert
            ];
            // Baris 4: HPP (516) Debit modal
            $rows[] = [
                'tgl_jurnal' => $trx['tgl'],
                'ket' => $trx['ket'],
                'no_rek' => '516',
                'debet' => $modal,
                'kredit' => 0,
                'no_jurnal' => str_pad($no_jurnal, 6, "0", STR_PAD_LEFT),
                'no_bukti' => str_pad($no_bukti, 6, "0", STR_PAD_LEFT),
                'username' => 'WA Bot',
                'tgl_insert' => $tgl_insert
            ];
            $no_jurnal++;
            $no_bukti++;
        }
        return $rows;
    }
}
