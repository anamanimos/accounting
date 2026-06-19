<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webhook_wa extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Webhook tidak membutuhkan login
    }

    public function index()
    {
        // Tangkap raw data JSON dari webhook
        $raw_input = file_get_contents('php://input');
        
        // Simpan ke wa.txt di root directory untuk keperluan debug & mencari ID Grup
        $log_file = FCPATH . 'wa.txt';
        $time = date('Y-m-d H:i:s');
        
        $log_content = "=== WEBHOOK RECEIVED AT " . $time . " ===\n";
        $log_content .= $raw_input . "\n\n";
        
        file_put_contents($log_file, $log_content, FILE_APPEND);

        // Response 200 OK ke WA Gateway
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['status' => 'success']));
    }
}
