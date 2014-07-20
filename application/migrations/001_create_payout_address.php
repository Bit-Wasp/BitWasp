<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_payout_address extends CI_Migration
{

    public function up()
    {
        // id int(9) AUTO_INCREMENT PRIMARY_KEY
        // address varchar(45)
        // user_id
        // time
        //

        ## Create Table bw_alerts
        $this->dbforge->add_field("`id` int(9) auto_increment PRIMARY KEY");

        $this->dbforge->add_field("`address` varchar(40) NOT NULL ");
        $this->dbforge->add_field("`user_id` int(9) NOT NULL ");
        $this->dbforge->add_field("`time` int(11) NOT NULL ");
        $this->dbforge->create_table("payout_address", TRUE);
    }

    public function down()
    {
        ### Drop table bw_alerts ##
        $this->dbforge->drop_table("payout_address", TRUE);
    }
}
