<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_wa_pending_image extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'message_id' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
            ),
            'image_url' => array(
                'type' => 'TEXT',
            ),
            'sender_jid' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
            ),
            'status' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
                'default' => 'pending'
            ),
            'created_at' => array(
                'type' => 'DATETIME',
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('wa_pending_image', TRUE); // TRUE for IF NOT EXISTS
    }

    public function down()
    {
        $this->dbforge->drop_table('wa_pending_image', TRUE); // TRUE for IF EXISTS
    }
}
