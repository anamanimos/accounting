<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whatsapp extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('username')) {
            redirect('login');
        }
    }

    public function index()
    {
        $status = 'unknown';
        $qrData = null;
        $jid = null;

        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }

        $gateway_url = class_exists('Env') ? (Env::get('WA_GATEWAY_URL') ?: 'https://wag.anam.ch') : 'https://wag.anam.ch';
        $username = class_exists('Env') ? (Env::get('WA_GATEWAY_USERNAME') ?: 'admin') : 'admin';
        $password = class_exists('Env') ? (Env::get('WA_GATEWAY_PASSWORD') ?: 'admin') : 'admin';
        $device_id = class_exists('Env') ? (Env::get('WA_DEVICE_ID') ?: 'erp-damaijaya') : 'erp-damaijaya';

        // Check status
        $statusData = $this->_curl_get($gateway_url . '/app/status', $username, $password, $device_id);

        if ($statusData && isset($statusData['results'])) {
            if ($statusData['results']['is_connected'] && $statusData['results']['is_logged_in']) {
                $status = 'connected';
                $jid = $statusData['results']['jid'] ?? null;
            } else {
                $status = 'disconnected';
            }
        } else {
            $status = 'error';
        }

        // If disconnected, try to get QR code
        if ($status === 'disconnected') {
            $loginData = $this->_curl_get($gateway_url . '/app/login', $username, $password, $device_id);
            if ($loginData && isset($loginData['results'])) {
                $qrData = $loginData['results'];
            }
        }

        $d = [
            'status' => $status,
            'qrData' => $qrData,
            'jid' => $jid,
            'device_id' => $device_id,
            'title' => 'Manajemen WhatsApp',
            'content' => 'whatsapp/index',
            'user' => (object) [
				'nama_lengkap' => $this->session->userdata('nama_lengkap'),
				'level'        => $this->session->userdata('level'),
				'email'        => $this->session->userdata('username') . '@accounting.test'
			]
        ];

        $this->load->view('templates/main', $d);
    }

    private function _curl_get($url, $username, $password, $device_id)
    {
        $ch = curl_init($url);
        $headers = [
            'X-Device-Id: ' . $device_id,
            'Authorization: Basic ' . base64_encode($username . ':' . $password)
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 || $http_code == 201) {
            return json_decode($response, true);
        }
        
        return null;
    }
}
