<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_option_for_emails extends CI_Migration
{

	// Purpose is to remove from the base SQL file - not going to change that any more..

    public function up()
    {
		### Drop table  ##
	$this->db->insert('config', array('parameter' => 'request_emails', 'value' => '0'));
        $this->dbforge->add_column("users", array('email_address' => array('type' => 'varchar', 'constraint' => '150')));
        $this->dbforge->add_column("users", array('activation_hash' => array('type' => 'varchar', 'constraint' => '64')));
        $this->dbforge->add_column("users", array('activation_id' => array('type' => 'varchar', 'constraint' => '64')));
        $this->dbforge->add_column("users", array('email_activated' => array('type' => 'enum', 'constraint' => array('0','1'), 'default' => '1')));
        $this->dbforge->add_column("users", array('email_updates' => array('type' => 'enum', 'constraint' => array('0','1'), 'default' => '1')));

    }

    public function down()
    {
	$this->db->where('parameter', 'request_emails')->delete('config');
        $this->dbforge->drop_column("users", "email_address");
        $this->dbforge->drop_column("users", "activation_hash");
        $this->dbforge->drop_column("users", "activation_id");
        $this->dbforge->drop_column("users", "email_activated");
        $this->dbforge->drop_column("users", "email_updates");
    }
}
