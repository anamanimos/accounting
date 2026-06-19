<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Create_db extends CI_Controller {

    public function index()
    {
        $this->load->database();
        
        $sql = "CREATE TABLE IF NOT EXISTS `wa_draft_jurnal` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `message_id` varchar(255) NOT NULL,
          `sender_jid` varchar(255) NOT NULL,
          `payload_jurnal` text NOT NULL,
          `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `message_id` (`message_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($this->db->query($sql)) {
            echo "Table wa_draft_jurnal created successfully!";
        } else {
            echo "Failed to create table.";
        }
    }
}
