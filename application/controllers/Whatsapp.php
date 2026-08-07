<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whatsapp extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('username')) {
            redirect('login');
        }
        $this->load->model('app_model');
        if (!class_exists('Env') && file_exists(FCPATH . 'application/config/env.php')) {
            require_once FCPATH . 'application/config/env.php';
        }
    }

    public function index()
    {
        $status = 'unknown';
        $qrData = null;
        $jid = null;

        $gateway_url = rtrim($this->app_model->get_setting('wa_gateway_url', ''), '/');
        if (empty($gateway_url)) {
            $gateway_url = class_exists('Env') ? (Env::get('WA_GATEWAY_URL') ?: 'https://wag.nams.my.id') : 'https://wag.nams.my.id';
        }

        $username = $this->app_model->get_setting('wa_gateway_username', '');
        if (empty($username)) {
            $username = class_exists('Env') ? (Env::get('WA_GATEWAY_USERNAME') ?: 'admin') : 'admin';
        }

        $password = $this->app_model->get_setting('wa_gateway_password', '');
        if (empty($password)) {
            $password = class_exists('Env') ? (Env::get('WA_GATEWAY_PASSWORD') ?: 'admin') : 'admin';
        }

        $device_id = $this->app_model->get_setting('wa_device_id', '');
        if (empty($device_id)) {
            $device_id = class_exists('Env') ? (Env::get('WA_DEVICE_ID') ?: 'erp-damaijaya') : 'erp-damaijaya';
        }

        $group_id = $this->app_model->get_setting('wa_group_id', '');
        if (empty($group_id)) {
            $group_id = class_exists('Env') ? (Env::get('WA_GROUP_ID') ?: '120363426581172416@g.us') : '120363426581172416@g.us';
        }

        // Check status
        $statusData = $this->_curl_get($gateway_url . '/app/status', $username, $password, $device_id);

        if ($statusData && isset($statusData['results'])) {
            if (!empty($statusData['results']['is_connected']) && !empty($statusData['results']['is_logged_in'])) {
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
            'gateway_url' => $gateway_url,
            'username' => $username,
            'password' => $password,
            'device_id' => $device_id,
            'group_id' => $group_id,
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

    public function simpan()
    {
        if (!$this->session->userdata('username')) {
            redirect('login');
        }

        $wa_gateway_url      = rtrim(trim($this->input->post('wa_gateway_url')), '/');
        $wa_device_id        = trim($this->input->post('wa_device_id'));
        $wa_group_id         = trim($this->input->post('wa_group_id'));
        $wa_gateway_username = trim($this->input->post('wa_gateway_username'));
        $wa_gateway_password = trim($this->input->post('wa_gateway_password'));

        if (empty($wa_gateway_url)) {
            $wa_gateway_url = 'https://wag.nams.my.id';
        }

        $this->app_model->set_setting('wa_gateway_url', $wa_gateway_url);
        $this->app_model->set_setting('wa_device_id', $wa_device_id);
        $this->app_model->set_setting('wa_group_id', $wa_group_id);
        $this->app_model->set_setting('wa_gateway_username', $wa_gateway_username);
        $this->app_model->set_setting('wa_gateway_password', $wa_gateway_password);

        $this->session->set_flashdata('success', 'Pengaturan WA Gateway berhasil disimpan.');
        redirect('whatsapp');
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 || $http_code == 201) {
            return json_decode($response, true);
        }
        
        return null;
    }
}
