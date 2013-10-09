<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Escrow_model extends CI_Model {
	
	public function __construct() {
		parent::__construct();
		$this->load->model('accounts_model');
		$this->load->model('bitcoin_model');
	}
	
	public function add($info) {
		$update = array('user_hash' => $info['buyer_hash'],
						'value' => (0-$info['amount']));
		unset($info['buyer_hash']);
		// Debit buyers account. 
		if($this->bitcoin_model->update_credits(array($update)) == TRUE){
			// If successful, add the funds to escrow.
			if($this->db->insert('escrow', $info))
				return TRUE;
		}
		
		return FALSE;
	}
	
	public function get($order_id) {
		$this->db->where('order_id',$order_id);
		$query = $this->db->get('escrow');
		if($query->num_rows() > 0){
			return $query->row_array();
		}
		return FALSE;
	}
	
	public function delete($order_id) {
		$this->db->where('order_id', $order_id);
		if($this->db->delete('escrow') == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	public function finalize() {
		$escrow = $this->get($order_id);
		if($escrow == FALSE)
			return FALSE;
			
		$vendor = $this->accounts_model->get(array('id' => $escrow['vendor_id']));			
		
		$update = array('user_hash' => $vendor['user_hash'],
						'value' => $escrow['amount']);
		if($this->update_credits(array($update)) == TRUE) {
			$this->delete($order_id);
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function balance() {

		switch(strtolower($this->current_user->user_role)) {
			case 'buyer':
				$this->db->where('buyer_id', $this->current_user->user_id);
				break;
			case 'vendor':
				$this->db->where('vendor_id', $this->current_user->user_id);
				break;
			default:
				return FALSE;
				break;
		}
		
		$this->db->select('amount');
		$query = $this->db->get('escrow');
		if($query->num_rows() == 0)
			return 0.00000000;
			
		$balance = 0.00000000;
		foreach($query->result_array() as $row) {
			$balance += (float)$row['amount'];
		}
		return $balance;		
	}
	
	public function pay($order_id, $user) {
		
		$escrow = $this->get($order_id);
		if($escrow == FALSE)
			return FALSE;

		switch($user){
			case 'buyer':
				$recipient = $escrow['buyer_id'];
				$sender = $escrow['vendor_id']; // Need to add this for disputes later.
				break;
			case 'vendor': 
				$recipient = $escrow['vendor_id'];// explicitly set a current_user flag here
				$sender = $escrow['buyer_id'];
				break;
			default:
				return FALSE;
				break;
		}
		
		$recipient = $this->accounts_model->get(array('id' => $recipient));
		$sender = $this->accounts_model->get(array('id' => $sender));
		
		$credits = array('user_hash' => $recipient['user_hash'],
						 'value' => (float)$escrow['amount']);
		if( $this->bitcoin_model->update_credits(array($credits)) == TRUE) {
			
			if($user == 'vendor')
				$this->order_model->set_finalized($order_id); 
								 
			$debit_txn = array( 'txn_id' => "Order #$order_id",
								 'user_hash' => $sender['user_hash'],
								 'value' => "-".(float)$escrow['amount'],
								 'confirmations' => '>50',
								 'address' => '[payment]',
								 'category' => 'send',
								 'credited' => '1',
								 'time' => time());
			$this->bitcoin_model->add_pending_txn($debit_txn);						
			
			$credit_txn = array( 'txn_id' => "Order #$order_id",
								 'user_hash' => $recipient['user_hash'],
								 'value' => (float)$escrow['amount'],
								 'confirmations' => '>50',
								 'address' => '[payment]',
								 'category' => 'receive',
								 'credited' => '1',
								 'time' => time());
			$this->bitcoin_model->add_pending_txn($credit_txn);

			if($this->delete($order_id) == TRUE)
				return TRUE;
		}
		
		return FALSE;
	}
	
	public function dispute($info) {
		if($this->db->insert('disputes', $info) == TRUE)
			return TRUE;
			
		return FALSE;
	}
	
	public function get_dispute($order_id) {
		$this->db->where('order_id', $order_id);
		$query = $this->db->get('disputes');
		if($query->num_rows() > 0){
			$row = $query->row_array();
			$row['last_update_f'] = $this->general->format_time($row['last_update']);
			return $row;
		}
		return FALSE;
	}
	
	public function update_dispute($order_id, $info) {
		$this->db->where('order_id', $order_id);
		if($this->db->update('disputes', $info))
			return TRUE;
			
		return FALSE;
	}
};


/* End of File: escrow_model.php */
