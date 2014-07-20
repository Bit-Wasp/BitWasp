<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Drop_bitcoin_public_keys extends CI_Migration
{

	// Purpose is to remove from the base SQL file - not going to change that any more..

    public function up()
    {
		### Drop table  ##
        $this->dbforge->drop_table("bitcoin_public_keys", TRUE);
    }

    public function down()
    {
		## Create Table
        $this->dbforge->add_field("`id` int(9) auto_increment PRIMARY KEY");

        $this->dbforge->add_field("`user_id` int(9) NOT NULL ");
        $this->dbforge->add_field("`public_key` var_char(150) NOT NULL ");
        $this->dbforge->create_table("bitcoin_public_keys", TRUE);
    }
}
