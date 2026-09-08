<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webhook_wa extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('app_model');
        // Ensure Env class is loaded
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }
    }

    private function _get_wa_setting($key, $default = '')
    {
        $val = '';
        if (isset($this->app_model) && method_exists($this->app_model, 'get_setting')) {
            $val = $this->app_model->get_setting($key, '');
        }
        if (empty($val) && class_exists('Env')) {
            $env_map = [
                'wa_gateway_url'      => 'WA_GATEWAY_URL',
                'wa_gateway_username' => 'WA_GATEWAY_USERNAME',
                'wa_gateway_password' => 'WA_GATEWAY_PASSWORD',
                'wa_device_id'        => 'WA_DEVICE_ID',
                'wa_group_id'         => 'WA_GROUP_ID'
            ];
            $env_key = $env_map[$key] ?? strtoupper($key);
            $val = Env::get($env_key);
        }
        return !empty($val) ? trim($val) : $default;
    }

    private function _extract_caption($payload)
    {
        if (empty($payload) || !is_array($payload)) return '';

        if (!empty($payload['body']) && is_string($payload['body'])) return trim($payload['body']);
        if (!empty($payload['caption']) && is_string($payload['caption'])) return trim($payload['caption']);
        if (!empty($payload['text']) && is_string($payload['text'])) return trim($payload['text']);

        if (!empty($payload['message']) && is_array($payload['message'])) {
            $msg = $payload['message'];
            if (!empty($msg['imageMessage']['caption'])) return trim($msg['imageMessage']['caption']);
            if (!empty($msg['documentMessage']['caption'])) return trim($msg['documentMessage']['caption']);
            if (!empty($msg['conversation'])) return trim($msg['conversation']);
            if (!empty($msg['extendedTextMessage']['text'])) return trim($msg['extendedTextMessage']['text']);
        }

        return '';
    }

    private function _extract_image_path($payload)
    {
        if (empty($payload) || !is_array($payload)) return null;

        // 1. Direct 'image' key
        if (!empty($payload['image'])) {
            if (is_string($payload['image'])) return $payload['image'];
            if (is_array($payload['image'])) {
                return $payload['image']['path'] ?? $payload['image']['url'] ?? $payload['image']['directPath'] ?? null;
            }
        }

        // 2. Direct 'media', 'file', 'document', 'url', etc.
        foreach (['media', 'file', 'document', 'media_path', 'image_url', 'media_url', 'url'] as $k) {
            if (!empty($payload[$k])) {
                if (is_string($payload[$k])) return $payload[$k];
                if (is_array($payload[$k])) {
                    return $payload[$k]['path'] ?? $payload[$k]['url'] ?? $payload[$k]['directPath'] ?? null;
                }
            }
        }

        // 3. Nested 'message' structure (Baileys format)
        if (!empty($payload['message']) && is_array($payload['message'])) {
            $msg = $payload['message'];
            if (!empty($msg['imageMessage'])) {
                return $msg['imageMessage']['url'] ?? $msg['imageMessage']['directPath'] ?? $msg['imageMessage']['path'] ?? null;
            }
            if (!empty($msg['documentMessage'])) {
                return $msg['documentMessage']['url'] ?? $msg['documentMessage']['directPath'] ?? $msg['documentMessage']['path'] ?? null;
            }
        }

        // 4. Type is image / media and path is specified
        $type = strtolower($payload['type'] ?? $payload['media_type'] ?? '');
        if (in_array($type, ['image', 'media', 'document', 'photo', 'picture'])) {
            return $payload['path'] ?? $payload['url'] ?? $payload['file_path'] ?? null;
        }

        return null;
    }

    public function index()
    {
        // Mencegah script mati jika gateway WA timeout karena Gemini butuh waktu lama
        ignore_user_abort(true);
        set_time_limit(180);

        $raw_input = file_get_contents('php://input');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';

        $log_file = FCPATH . 'wa.txt';
        $time = date('Y-m-d H:i:s');
        
        $log_content = "=== WEBHOOK RECEIVED AT " . $time . " ===\n";
        $log_content .= "Method: " . $method . "\n";
        $log_content .= "Payload: " . $raw_input . "\n\n";
        
        @file_put_contents($log_file, $log_content, FILE_APPEND);
        
        $data = json_decode($raw_input, true);
        if (!$data || !is_array($data)) {
            return $this->_response(['status' => 'invalid_json']);
        }

        // Hanya proses event message jika ada field event
        $event = strtolower($data['event'] ?? '');
        if (!empty($event) && strpos($event, 'message') === false) {
            @file_put_contents($log_file, "[DEBUG] Ignored event: $event\n", FILE_APPEND);
            return $this->_response(['status' => 'ignored_not_message']);
        }

        $session_id = $data['session_id'] ?? $data['device_id'] ?? $data['sessionId'] ?? '';
        $configured_device_id = $this->_get_wa_setting('wa_device_id', 'erp-damaijaya');

        // Filter session_id jika keduanya ada dan jelas tidak cocok
        if (!empty($session_id) && !empty($configured_device_id)) {
            if (stripos($session_id, $configured_device_id) === false && stripos($configured_device_id, $session_id) === false) {
                @file_put_contents($log_file, "[DEBUG] Ignored session mismatch: incoming '$session_id' vs configured '$configured_device_id'\n", FILE_APPEND);
                return $this->_response(['status' => 'ignored_other_session']);
            }
        }

        $payload = $data['payload'] ?? $data['data'] ?? [];
        if (empty($payload) || !is_array($payload)) {
            @file_put_contents($log_file, "[DEBUG] Empty payload\n", FILE_APPEND);
            return $this->_response(['status' => 'ignored_empty_payload']);
        }

        $body = $this->_extract_caption($payload);
        $image_path = $this->_extract_image_path($payload);

        // Cegah looping pesan bot sendiri, tapi izinkan manual input jika bukan pesan otomatis bot
        if (!empty($payload['is_from_me']) && $payload['is_from_me'] == true) {
            $bot_markers = ['*DRAF JURNAL', 'Silakan balas pesan ini', 'Memproses gambar', '✅ Jurnal berhasil disimpan', 'Draf jurnal telah dibatalkan', '⚠️ Gagal'];
            $is_bot_auto_msg = false;
            foreach ($bot_markers as $marker) {
                if (stripos($body, $marker) !== false) {
                    $is_bot_auto_msg = true;
                    break;
                }
            }
            if ($is_bot_auto_msg) {
                @file_put_contents($log_file, "[DEBUG] Ignored bot self auto-message\n", FILE_APPEND);
                return $this->_response(['status' => 'ignored_from_me']);
            }
        }

        $message_id = $payload['id'] ?? $payload['message_id'] ?? $payload['key']['id'] ?? '';
        
        // Idempotency check (mencegah proses berulang jika gateway mengirim ulang webhook)
        if (!empty($message_id)) {
            $cache_file = FCPATH . 'application/cache/wa_msg_' . md5($message_id);
            if (file_exists($cache_file)) {
                @file_put_contents($log_file, "[DEBUG] Message already processed: $message_id\n", FILE_APPEND);
                return $this->_response(['status' => 'already_processed']);
            }
            @file_put_contents($cache_file, date('Y-m-d H:i:s'));
        }

        $chat_id = trim($payload['chat_id'] ?? $payload['from'] ?? $payload['key']['remoteJid'] ?? '');
        $target_group = trim($this->_get_wa_setting('wa_group_id', '120363426581172416@g.us'));

        // Hanya proses pesan dari grup target jika target_group diset
        if (!empty($target_group) && !empty($chat_id) && $chat_id !== $target_group) {
            @file_put_contents($log_file, "[DEBUG] Ignored wrong group: incoming '$chat_id' vs target '$target_group'\n", FILE_APPEND);
            return $this->_response(['status' => 'ignored_wrong_group']);
        }

        $sender_jid = $payload['from'] ?? $payload['participant'] ?? $payload['key']['participant'] ?? $chat_id;
        $replied_to_id = $payload['replied_to_id'] ?? $payload['contextInfo']['stanzaId'] ?? null;

        // Cek State Machine (YA / BATAL)
        $upper_body = strtoupper(trim($body));
        if (in_array($upper_body, ['YA', 'BATAL'])) {
            @file_put_contents($log_file, "[DEBUG] Detected YA/BATAL. upper_body: $upper_body, replied_to_id: " . ($replied_to_id ?: 'null') . "\n", FILE_APPEND);
            
            $draft = null;
            if ($replied_to_id) {
                $draft = $this->db->get_where('wa_draft_jurnal', ['message_id' => $replied_to_id, 'status' => 'pending'])->row();
            }
            // Jika tidak di-reply ATAU ID tidak ditemukan di DB (karena GOWA tidak return ID), ambil draf terakhir
            if (!$draft) {
                @file_put_contents($log_file, "[DEBUG] Draft not found by replied_to_id, falling back to DESC\n", FILE_APPEND);
                $draft = $this->db->order_by('id', 'DESC')->get_where('wa_draft_jurnal', ['status' => 'pending'])->row();
            }

            if ($draft) {
                @file_put_contents($log_file, "[DEBUG] Found pending draft ID: {$draft->id}. Processing...\n", FILE_APPEND);
                
                if ($upper_body === 'BATAL') {
                    $this->db->update('wa_draft_jurnal', ['status' => 'rejected'], ['id' => $draft->id]);
                    $this->_send_message($chat_id, "Draf jurnal telah dibatalkan.", $message_id);
                } else {
                    // YA: Simpan ke database
                    $jurnal_data = json_decode($draft->payload_jurnal, true);
                    @file_put_contents($log_file, "[DEBUG] Starting DB transaction with payload: " . json_encode($jurnal_data) . "\n", FILE_APPEND);
                    
                    // Matikan db_debug agar script tidak mati tiba-tiba jika ada error SQL
                    $this->db->db_debug = FALSE;
                    $this->db->trans_start();
                    
                    foreach ($jurnal_data as $row) {
                        if (!$this->db->insert('jurnal_umum', $row)) {
                            @file_put_contents($log_file, "[DEBUG DB ERROR] " . json_encode($this->db->error()) . "\n", FILE_APPEND);
                        }
                    }
                    $this->db->trans_complete();
                    $trans_status = $this->db->trans_status();
                    $this->db->db_debug = TRUE; // Kembalikan ke normal

                    @file_put_contents($log_file, "[DEBUG] DB transaction complete. Status: " . ($trans_status === FALSE ? 'FAILED' : 'SUCCESS') . "\n", FILE_APPEND);

                    if ($trans_status === FALSE) {
                        $this->_send_message($chat_id, "Gagal menyimpan jurnal ke database. Mohon cek log server.", $message_id);
                    } else {
                        $this->db->update('wa_draft_jurnal', ['status' => 'approved'], ['id' => $draft->id]);
                        $this->_send_message($chat_id, "✅ Jurnal berhasil disimpan!", $message_id);
                    }
                }
                return $this->_response(['status' => 'state_processed']);
            } else {
                @file_put_contents($log_file, "[DEBUG] NO PENDING DRAFT FOUND AT ALL!\n", FILE_APPEND);
            }
        }

        $is_processing_image = false;
        $image_path_to_process = null;
        $nama_order_to_process = null;
        $prompt = '';

        if (!empty($image_path)) {
            $nama_order = $body;
            if (empty($nama_order)) {
                $sent_msg = $this->_send_message($chat_id, "Silakan balas pesan ini dengan teks keterangan (Nama Order) untuk gambar tersebut:\n_(Opsional sertakan tanggal jika berbeda dari nota, contoh: Size Sevencols 14/07/2026)_", $message_id);
                
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
                return $this->_response(['status' => 'waiting_for_name', 'image_path' => $image_path]);
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
            }
            
            if (!$pending && !empty($sender_jid)) {
                // Cek latest pending request by sender_jid
                $pending = $this->db->order_by('id', 'DESC')->get_where('wa_pending_image', ['sender_jid' => $sender_jid])->row();
            }

            if (!$pending) {
                // Fallback: Cek latest pending request di grup dalam 15 menit terakhir
                $fifteen_mins_ago = date('Y-m-d H:i:s', time() - 900);
                $pending = $this->db->order_by('id', 'DESC')->get_where('wa_pending_image', ['created_at >=' => $fifteen_mins_ago])->row();
            }

            $override_date = null;
            if ($pending && !empty($body) && !in_array(strtoupper($body), ['YA', 'BATAL'])) {
                $this->db->delete('wa_pending_image', ['id' => $pending->id]);
                
                $is_processing_image = true;
                $image_path_to_process = $pending->image_url;
                
                // Extract optional date in reply text (e.g. "Size Sevencols 14/07/2026")
                $custom_date = $this->_extract_date($body);
                $clean_nama_order = preg_replace('/(?:tgl|tanggal)?\s*[:\.]?\s*\d{1,4}[\/\.-]\d{1,2}[\/\.-]\d{1,4}/i', '', $body);
                $clean_nama_order = trim($clean_nama_order, " \t\n\r\0\x0B*_~\\");
                
                $nama_order_to_process = !empty($clean_nama_order) ? $clean_nama_order : $body;
                if ($custom_date) {
                    $override_date = $custom_date;
                }
            } else {
                $prompt = $body;
            }
        }

        if ($is_processing_image) {
            $this->_send_message($chat_id, "Memproses gambar dengan nama order: *" . $nama_order_to_process . "*...", $message_id);

            $dl_res = $this->_download_and_base64($image_path_to_process);
            if (!$dl_res['success']) {
                $this->_send_message($chat_id, "⚠️ Gagal mengunduh gambar nota dari gateway.\n\n*Log:* " . $dl_res['debug'], $message_id);
                return $this->_response(['status' => 'image_download_failed']);
            }
            $base64_image = $dl_res['base64'];

            $this->load->library('gemini_ocr');
            $gemini_result = $this->gemini_ocr->process_receipt($base64_image, $nama_order_to_process);
            
            if (!$gemini_result['success']) {
                @file_put_contents($log_file, "[DEBUG GEMINI ERROR] " . $gemini_result['error'] . "\n", FILE_APPEND);
                $this->_send_message($chat_id, "⚠️ Gagal AI Gemini (" . ($gemini_result['error'] ?? 'Error') . ").", $message_id);
                return $this->_response(['status' => 'gemini_error']);
            }

            @file_put_contents($log_file, "[DEBUG GEMINI] Raw Output: \n" . $gemini_result['text'] . "\n", FILE_APPEND);
            $prompt = $gemini_result['text'];
        }

        $transactions = $this->_parse_prompt($prompt);
        if (empty($transactions)) {
            @file_put_contents($log_file, "[DEBUG] Ignoring message because parsed transactions are empty. Prompt: $prompt\n", FILE_APPEND);
            if ($is_processing_image || !empty($image_path)) {
                $short_prompt = (strlen($prompt) > 250) ? substr($prompt, 0, 250) . '...' : $prompt;
                $this->_send_message($chat_id, "⚠️ AI Gemini selesai membaca tetapi tidak menemukan format transaksi valid.\n\n*Hasil Teks AI:* \n" . $short_prompt, $message_id);
            }
            return $this->_response(['status' => 'ignored_not_prompt']);
        }

        // Apply override date if user specified date in image reply text
        if (!empty($override_date) && !empty($transactions)) {
            foreach ($transactions as &$trx) {
                $trx['tgl'] = $override_date;
            }
        }

        // Ubah jadi array jurnal yang siap insert
        $jurnal_rows = $this->_build_jurnal_array($transactions);

        // Buat pesan balasan preview
        $source_title = $is_processing_image ? "(Hasil Scan Foto Nota)" : "(Hasil Teks Chat Order)";
        $first_tgl = !empty($transactions[0]['tgl']) ? date('d-m-Y', strtotime($transactions[0]['tgl'])) : date('d-m-Y');
        
        $preview_msg = "*DRAF JURNAL $source_title*\n";
        $preview_msg .= "Tanggal: *" . $first_tgl . "*\n\n";
        
        $total_modal = 0;
        $total_jual = 0;
        foreach ($transactions as $i => $trx) {
            $preview_msg .= ($i+1) . ". Ket: " . $trx['ket'] . "\n";
            $preview_msg .= "   Jual: Rp " . number_format($trx['harga_jual'],0,',','.') . "\n";
            $preview_msg .= "   Modal: Rp " . number_format($trx['modal'],0,',','.') . "\n\n";
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
        $gateway_url = rtrim($this->_get_wa_setting('wa_gateway_url', 'https://wag.nams.my.id'), '/');
        $username    = $this->_get_wa_setting('wa_gateway_username', 'admin');
        $password    = $this->_get_wa_setting('wa_gateway_password', 'admin');
        $device_id   = $this->_get_wa_setting('wa_device_id', 'erp-damaijaya');

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        $log_file = FCPATH . 'wa.txt';
        @file_put_contents($log_file, "[SEND MESSAGE] To: $phone, HTTP: $http_code, Err: " . ($curl_err ?: 'none') . ", Res: $response\n", FILE_APPEND);

        return json_decode($response, true);
    }

    private function _download_and_base64($url)
    {
        $username    = $this->_get_wa_setting('wa_gateway_username', 'admin');
        $password    = $this->_get_wa_setting('wa_gateway_password', 'admin');
        $device_id   = $this->_get_wa_setting('wa_device_id', 'erp-damaijaya');
        $gateway_url = rtrim($this->_get_wa_setting('wa_gateway_url', 'https://wag.nams.my.id'), '/');

        $raw_path = $url;
        $candidates = [];

        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $candidates[] = $url;
            $parsed_path = ltrim(parse_url($url, PHP_URL_PATH), '/');
            if (!empty($parsed_path)) {
                $raw_path = $parsed_path;
            }
        }

        $clean_path = ltrim($raw_path, '/');
        if (!empty($clean_path)) {
            $candidates[] = $gateway_url . '/' . $clean_path;
            $candidates[] = $gateway_url . '/app/media?path=' . urlencode($clean_path);
            $candidates[] = $gateway_url . '/media?path=' . urlencode($clean_path);
            $candidates[] = $gateway_url . '/app/files/' . $clean_path;
            $candidates[] = $gateway_url . '/files/' . $clean_path;
        }

        $candidates = array_unique($candidates);
        $attempt_logs = [];

        foreach ($candidates as $cand_url) {
            $ch = curl_init($cand_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Device-Id: ' . $device_id,
                'Authorization: Basic ' . base64_encode($username . ':' . $password)
            ]);
            $data = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            if ($http_code === 200 && !empty($data)) {
                // Ignore HTML error pages
                if (strpos($data, '<!DOCTYPE') !== false || strpos($data, '<html') !== false) {
                    $attempt_logs[] = "$cand_url -> HTTP 200 but HTML error page";
                    continue;
                }

                // Verify image header bytes
                $header = substr($data, 0, 8);
                $is_image = (
                    substr($header, 0, 2) === "\xFF\xD8" || // JPEG
                    substr($header, 0, 8) === "\x89PNG\r\n\x1a\n" || // PNG
                    (substr($header, 0, 4) === "RIFF" && substr($data, 8, 4) === "WEBP") || // WEBP
                    substr($header, 0, 4) === "%PDF" // PDF
                );

                if ($is_image || (is_string($content_type) && strpos($content_type, 'image/') !== false)) {
                    @file_put_contents(FCPATH.'wa.txt', "[DEBUG DOWNLOAD SUCCESS] $cand_url, Size: " . strlen($data) . " bytes\n", FILE_APPEND);
                    return ['success' => true, 'base64' => base64_encode($data), 'url' => $cand_url, 'size' => strlen($data)];
                } else {
                    $attempt_logs[] = "$cand_url -> HTTP 200 but not valid image header ($content_type)";
                }
            } else {
                $attempt_logs[] = "$cand_url -> HTTP $http_code" . ($curl_err ? " ($curl_err)" : "");
            }
        }

        return ['success' => false, 'debug' => implode("\n", $attempt_logs)];
    }

    private function _extract_date($text)
    {
        if (empty($text)) return null;

        // Match formats like: 14/07/2026, 14-07-2026, 2026-07-14, 14.07.2026, Tgl: 14/07/2026
        if (preg_match('/(?:tgl|tanggal)?\s*[:\.]?\s*(\d{1,4})[\/\.-](\d{1,2})[\/\.-](\d{1,4})/i', $text, $m)) {
            $p1 = (int)$m[1];
            $p2 = (int)$m[2];
            $p3 = (int)$m[3];

            if (strlen($m[1]) == 4) {
                // YYYY-MM-DD
                return sprintf("%04d-%02d-%02d", $p1, $p2, $p3);
            } elseif (strlen($m[3]) == 4) {
                // DD-MM-YYYY
                return sprintf("%04d-%02d-%02d", $p3, $p2, $p1);
            }
        }
        return null;
    }

    private function _parse_pe_text($prompt)
    {
        $lines = explode("\n", str_replace("\r", "", $prompt));
        $current_date = $this->_extract_date($prompt);
        if (!$current_date) {
            $current_date = date('Y-m-d');
        }

        $transactions = [];

        foreach ($lines as $line) {
            $clean_line = trim($line, " \t\n\r\0\x0B*_~\\");
            if (empty($clean_line)) continue;

            $line_date = $this->_extract_date($clean_line);
            if ($line_date) {
                $current_date = $line_date;
            }

            // Pattern: Cetak DTF 557CM = Rp 139.250 or 557CM = Rp 139.250 or Cetak DTF 557 CM = Rp 139.250
            if (preg_match('/^(.*?)\s*(\d+)\s*(?:CM|cm)?\s*=\s*(?:Rp|rp)?\s*([\d\.,]+)/i', $clean_line, $m)) {
                $deskripsi = trim($m[1]);
                if (empty($deskripsi)) {
                    $deskripsi = 'Cetak DTF';
                }
                $ukuran = trim($m[2]);
                $modal_str = str_replace(['.', ','], '', trim($m[3]));
                $modal = (int) preg_replace('/[^\d]/', '', $modal_str);

                $pelanggan = 'Sevencols';
                $suplier   = 'PE';

                $harga_jual = 0;
                $mh = $this->db->query("SELECT harga_jual FROM master_harga LIMIT 1")->row();
                $harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
                $panjang = (int) $ukuran;
                $harga_jual = $panjang * $harga_per_cm;
                if ($harga_jual === 0 && $modal > 0) {
                    $harga_jual = $modal;
                }

                $ket = "$pelanggan - $suplier - $deskripsi - $ukuran";
                $rek_inventory_or_ap = '118';

                $transactions[] = [
                    'tgl' => $current_date,
                    'ket' => $ket,
                    'harga_jual' => $harga_jual,
                    'modal' => $modal,
                    'rek_inventory_or_ap' => $rek_inventory_or_ap
                ];
            }
        }

        return $transactions;
    }

    private function _parse_prompt($prompt)
    {
        $current_date = date('Y-m-d');
        
        // Check date in text if present
        $extracted_date = $this->_extract_date($prompt);
        if ($extracted_date) {
            $current_date = $extracted_date;
        }

        // 0. Check PE Order Format (Vendor PE hardcoded)
        $pe_trxs = $this->_parse_pe_text($prompt);
        if (!empty($pe_trxs)) {
            return $pe_trxs;
        }

        $transactions = [];

        // Direct JSON Array Support if Gemini returned JSON directly
        $clean_json = '';
        if (preg_match('/\[\s*\{[\s\S]*\}\s*\]/', $prompt, $matches)) {
            $clean_json = $matches[0];
        } elseif (strpos(trim($prompt), '[') === 0) {
            $clean_json = $prompt;
        }

        if (!empty($clean_json)) {
            $json_data = json_decode($clean_json, true);
            if (!is_array($json_data)) {
                // Try JSON auto-repair for truncated JSON
                $repaired = preg_replace('/,\s*"[^"]*"?\s*:?\s*"?[^"]*$/', '', $clean_json);
                $repaired = rtrim($repaired, " \t\n\r,");
                if (substr($repaired, -1) === '}') {
                    $repaired .= ']';
                } elseif (substr($repaired, -1) !== ']') {
                    $repaired .= '}]';
                }
                $json_data = json_decode($repaired, true);
            }

            if (is_array($json_data) && count($json_data) > 0) {
                foreach ($json_data as $item) {
                    $raw_tgl   = $item['tanggal'] ?? date('Y-m-d');
                    $pelanggan = !empty($item['pelanggan']) ? trim($item['pelanggan']) : 'Sevencols';
                    $suplier   = !empty($item['suplier']) ? trim($item['suplier']) : 'Suplier Utama';
                    $deskripsi = !empty($item['deskripsi']) ? trim($item['deskripsi']) : 'Nota AI';
                    $ukuran    = isset($item['ukuran']) ? trim((string)$item['ukuran']) : '1';
                    $modal     = isset($item['modal']) ? (int) preg_replace('/[^\d]/', '', (string)$item['modal']) : 0;

                    $item_date = $this->_extract_date($raw_tgl);
                    if ($item_date) {
                        $current_date = $item_date;
                    }

                    $harga_jual = 0;
                    $mh = $this->db->query("SELECT harga_jual FROM master_harga LIMIT 1")->row();
                    $harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
                    preg_match_all('/\d+/', $ukuran, $matches_uk);
                    $panjang = (!empty($matches_uk[0])) ? (int) end($matches_uk[0]) : 0;
                    $harga_jual = $panjang * $harga_per_cm;
                    if ($harga_jual === 0 && $modal > 0) {
                        $harga_jual = $modal;
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
                if (!empty($transactions)) {
                    return $transactions;
                }
            }
        }

        // Line by line fallback
        $lines = explode("\n", str_replace("\r", "", $prompt));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // 1. Cek Tanggal
            $line_date = $this->_extract_date($line);
            if ($line_date) {
                $current_date = $line_date;
                continue;
            }

            // 2. Cek baris Transaksi
            if (strpos($line, '-') !== false || strpos($line, '|') !== false) {
                $harga_jual = 0;
                $left_part = $line;

                if (strpos($line, '|') !== false) {
                    $parts = explode('|', $line);
                    $harga_str = trim($parts[1]);
                    $harga_str = str_replace(['.', ','], '', $harga_str);
                    $harga_jual = (int) $harga_str;
                    $left_part = trim($parts[0]);
                }
                
                $dash_parts = explode('-', $left_part);
                
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
                        $harga_per_cm = $mh ? (int)$mh->harga_jual : 0;
                        
                        preg_match_all('/\d+/', $ukuran, $matches_uk);
                        $panjang = (!empty($matches_uk[0])) ? (int) end($matches_uk[0]) : 0;
                        
                        $harga_jual = $panjang * $harga_per_cm;
                        
                        if ($harga_jual === 0 && $modal > 0) {
                            $harga_jual = $modal;
                        }
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
        $jurnal_rows = [];
        $max_jurnal = $this->app_model->getMaxJurnal();
        $current_jurnal = $max_jurnal ? (int)$max_jurnal + 1 : (int)(date('y') . date('m') . '00001');

        foreach ($transactions as $trx) {
            $tgl_jurnal = $trx['tgl'];
            $ket = $trx['ket'];
            $harga_jual = $trx['harga_jual'];
            $modal = $trx['modal'];
            $rek_inventory_or_ap = $trx['rek_inventory_or_ap'];
            $tgl_insert = date('Y-m-d H:i:s');
            $username = 'WA-BOT';

            // Debet Piutang (112)
            $jurnal_rows[] = [
                'no_jurnal' => (string)$current_jurnal,
                'tgl_jurnal' => $tgl_jurnal,
                'no_bukti' => '',
                'ket' => $ket,
                'no_rek' => '112',
                'debet' => $harga_jual,
                'kredit' => 0,
                'tgl_insert' => $tgl_insert,
                'username' => $username
            ];

            // Kredit Pendapatan (411)
            $jurnal_rows[] = [
                'no_jurnal' => (string)$current_jurnal,
                'tgl_jurnal' => $tgl_jurnal,
                'no_bukti' => '',
                'ket' => $ket,
                'no_rek' => '411',
                'debet' => 0,
                'kredit' => $harga_jual,
                'tgl_insert' => $tgl_insert,
                'username' => $username
            ];

            // Debet HPP (516)
            $jurnal_rows[] = [
                'no_jurnal' => (string)$current_jurnal,
                'tgl_jurnal' => $tgl_jurnal,
                'no_bukti' => '',
                'ket' => $ket,
                'no_rek' => '516',
                'debet' => $modal,
                'kredit' => 0,
                'tgl_insert' => $tgl_insert,
                'username' => $username
            ];

            // Kredit Kas / Hutang (118 atau 213)
            $jurnal_rows[] = [
                'no_jurnal' => (string)$current_jurnal,
                'tgl_jurnal' => $tgl_jurnal,
                'no_bukti' => '',
                'ket' => $ket,
                'no_rek' => $rek_inventory_or_ap,
                'debet' => 0,
                'kredit' => $modal,
                'tgl_insert' => $tgl_insert,
                'username' => $username
            ];

            $current_jurnal++;
        }

        return $jurnal_rows;
    }

    public function logs()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $log_file = FCPATH . 'wa.txt';
        if (file_exists($log_file)) {
            $content = file_get_contents($log_file);
            $lines = explode("\n", $content);
            $recent = array_slice($lines, -150);
            echo implode("\n", $recent);
        } else {
            echo "Log file wa.txt belum ada.";
        }
    }

    public function test_send()
    {
        header('Content-Type: application/json; charset=utf-8');
        $target_group = $this->_get_wa_setting('wa_group_id', '120363426581172416@g.us');
        $res = $this->_send_message($target_group, "🤖 Tes koneksi bot WhatsApp berhasil! (" . date('d-m-Y H:i:s') . ")");
        echo json_encode([
            'status' => 'ok',
            'target_group' => $target_group,
            'gateway_response' => $res
        ], JSON_PRETTY_PRINT);
    }
}
