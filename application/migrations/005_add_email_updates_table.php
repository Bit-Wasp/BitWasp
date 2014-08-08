<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_email_updates_table extends CI_Migration
{

    public function up()
    {
		$this->dbforge->add_field("`id` int(9) auto_increment PRIMARY KEY");
        $this->dbforge->add_field("`user_id` int(9) NOT NULL ");
        $this->dbforge->add_field("`email_address` varchar(150) NOT NULL ");
        $this->dbforge->add_field("`time` varchar(20) NOT NULL ");
        $this->dbforge->add_field("`expire_time` varchar(20) NOT NULL ");
        $this->dbforge->add_field("`activation_hash` varchar(64) NOT NULL ");
        $this->dbforge->add_field("`activation_id` varchar(64) NOT NULL ");
        $this->dbforge->add_field("`activated` enum('0','1') DEFAULT '0' ");
        $this->dbforge->create_table("email_update_requests", TRUE);
    }

    public function down()
    {
		$this->dbforge->drop_table("email_update_requests", TRUE);
    }
}
