<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_order_field_vendor_payout extends CI_Migration
{

    public function up()
    {
        // bw_orders.vendor_payout varchar(45)
        $this->dbforge->add_column("orders", array('vendor_payout' => array('type' => 'varchar', 'constraint' => '45')));
        $this->dbforge->add_column("orders", array('buyer_payout' => array('type' => 'varchar', 'constraint' => '45')));

    }

    public function down()
    {
        ### Drop table bw_alerts ##
        $this->dbforge->drop_column("orders", "vendor_payout");
        $this->dbforge->drop_column("orders", "buyer_payout");
    }
}
