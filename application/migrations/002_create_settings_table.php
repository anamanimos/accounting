<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_settings_table extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'key' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => FALSE
            ),
            'value' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            )
        ));
        $this->dbforge->add_key('key', TRUE);
        $this->dbforge->create_table('settings', TRUE);

        // Seed default settings if empty
        $default_settings = [
            [
                'key' => 'google_api_key',
                'value' => '',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'ocr_provider',
                'value' => 'gemini_flash',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'gemini_model',
                'value' => 'gemini-1.5-flash',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($default_settings as $setting) {
            $exists = $this->db->get_where('settings', ['key' => $setting['key']])->num_rows();
            if ($exists == 0) {
                $this->db->insert('settings', $setting);
            }
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('settings', TRUE);
    }
}
