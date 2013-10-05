<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bitcoin_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}
	
	public function reset_a(){
		$this->db->where('id !=', 1);
		$this->db->delete('blocks');
			
		$this->db->where('user_hash !=', '');
		$this->db->delete('pending_txns');
		
		$this->db->where('user_name', 'asdf');
		$this->db->update('users', array('bitcoin_balance' => 0.00000000));
		
		$this->db->where('user_name', 'buyer');
		$this->db->update('users', array('bitcoin_balance' => 0.00000000));
		
		$this->db->where('user_name', 'buyer1');
		$this->db->update('users', array('bitcoin_balance' => 0.00000000));
	}

	// User information.
	public function current_balance($user_hash = NULL ) {
		$this->db->select('bitcoin_balance');
		
		($user_hash == NULL) ? $this->db->where('user_hash', $this->current_user->user_hash) : $this->db->where('user_hash', $user_hash);
				 
		$query = $this->db->get('users');
		if($query->num_rows() >  0) {
			$row = $query->row_array();
			return $row['bitcoin_balance'];
		}
		
		return FALSE;
	}
	
	
	// User information.
	public function unverified_transactions($user_hash) {
		$this->db->select('value')
				 ->where('user_hash', $user_hash)
				 ->where('category', 'receive')
				 ->where('credited', '0');
				 
		$query = $this->db->get('pending_txns');
		if($query->num_rows() == 0) 
			return 0.00000000;
		
		$balance = 0.00000000;
		foreach($query->result_array() as $row) {
			$balance += (float)$row['value'];
		}
		return $balance;
	}
	
	// User information.
	public function user_txns($user_hash) {
		$this->db->where('user_hash', $user_hash)
				 ->order_by('time','desc');
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0) {
			$res = array();
			foreach($query->result_array() as $row) {
				$row['value_f'] = (float)$row['value'];
				$row['time_f'] = $this->general->format_time($row['time']);
				$row['txn_id_f'] = substr($row['txn_id'], 0, 16);
				array_push($res, $row);
			}
			return $res;
		}
		return FALSE;
	}
											 
	// User information.
	public function get_user_address($user_hash) {
		$this->db->select('bitcoin_topup_address')
				 ->where('user_hash', $user_hash);
		$query = $this->db->get('users');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			if( $this->general->matches_any($row['bitcoin_topup_address'], array('0',''))){
				return $this->bw_bitcoin->new_address($user_hash);
			}
			return $row['bitcoin_topup_address'];
		}
		
		return FALSE;
	}
	
	// Return the current cashout address for this user.
	public function get_cashout_address($user_hash) {
		$this->db->select('bitcoin_cashout_address')
				 ->where('user_hash', $user_hash);
		$query = $this->db->get('users');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['bitcoin_cashout_address'];
		}
		
		return FALSE;
	}
	
	// Associate address with the user when they register/receive coins.
	public function log_user_address($user_hash, $address) {
		if($this->db->insert('addresses', array('user_hash' => $user_hash, 
											    'bitcoin_address' => $address)))
			return TRUE;
	
		return FALSE;
	}
	
	// Called by new_address, creates a new topup address for the user, and add's it to the log.
	public function set_user_address($user_hash, $address) {
		$this->db->where('user_hash', $user_hash);
		if($this->db->update('users', array('bitcoin_topup_address' => $address))) {
			$this->log_user_address($user_hash, $address);
			return TRUE;
		}
		
		return FALSE;	
	}
	
	// Set a new cashout address.
	public function set_cashout_address($user_hash, $address) {
		$this->db->where('user_hash', $user_hash);
		if($this->db->update('users', array('bitcoin_cashout_address' => $address))) 
			return TRUE;
	
		return FALSE;
	}
	
	// WalletNotify callback.
	public function get_address_owner($address) {
		$this->db->select('user_hash');
		$this->db->where('bitcoin_address', $address);
		$query = $this->db->get('addresses');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['user_hash'];
		}
		
		return FALSE;
	}
	
	// WalletNotify callback
	public function get_cashout_address_owner($address) {
		$this->db->select('user_hash');
		$this->db->where('bitcoin_cashout_address', $address);
		$query = $this->db->get('users');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['user_hash'];
		}
		
		return FALSE;
	}
	
	// WalletNotify callback.
	public function add_pending_txn($array) {
		// A quick check to prevent code from adding transaction twice.
		if($this->user_transaction($array['user_hash'], $array['txn_id'], $array['category']) == FALSE){	
			if($this->db->insert('pending_txns', $array) == TRUE)
				return TRUE;
		}
		
		return FALSE;
	}

	// WalletNotify, check if a user has a transaction with those details
	public function user_transaction($user_hash, $txn_id, $category){
		$this->db->select('id')
				 ->where('user_hash', $user_hash)
				 ->where('txn_id', $txn_id)
				 ->where('category', $category);
				
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0)
			return TRUE;
		
		return FALSE;
	}
	
	// WalletNotify callback. 
	/* Should be deprecated in favour of user_transaction()... */
	public function have_transaction($txn_id) {
		$this->db->where('txn_id', $txn_id);
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0)
			return $query->row_array();
			
		return FALSE;
	}
	
	// BlockNotify (received) and WalletNotify (sent) callbacks
	public function update_credits($updates) {
		$code = TRUE;
		foreach($updates as $update) {
			$balance = $this->current_balance($update['user_hash'])+$update['value'];	
			
			$this->db->where('user_hash', $update['user_hash']);
			if($this->db->update('users', array('bitcoin_balance' => $balance) ) == FALSE)
				$code = FALSE;
		}
		return $code;
	}
	
	// BlockNotify callback.
	public function set_credited($txn_id) {
		$this->db->where('txn_id', $txn_id);
		if($this->db->update('pending_txns', array('credited' => '1')))
			return TRUE;
		
		return FALSE;
	}

	// BlockNotify callback.
	public function get_pending_txns() {
		$res = array();
		// add time to the table
		$this->db->where('confirmations !=', '>50');
		$this->db->where('address !=', '[payment]');
		
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0) 
			$res = $query->result_array();
		
		return $res;
	}
	
	// BlockNotify callback.
	public function update_confirmations($updates) {
		foreach($updates as $update) {
			$this->db->where('txn_id', $update['txn_id']);
			$this->db->update('pending_txns', array('confirmations' => $update['confirmations']));
		}
	}	
	
	// BlockNotify callback.
	public function add_block($block_hash, $number) {
		if($this->db->insert('blocks', array('hash' => $block_hash, 'number' => $number)))
			return TRUE;
			
		return FALSE;
	}
	
	// BlockNotify callback.
	public function have_block($block_hash) {
		$this->db->select('id')
				 ->where('hash', $block_hash);
		$query = $this->db->get('blocks');
		if($query->num_rows() > 0)
			return TRUE;
		
		return FALSE;
	}
	
	// Load information about the latest block on record.
	public function latest_block() {
		$this->db->select('hash, number')
				 ->order_by('id', 'desc')
				 ->limit(1);
		$query = $this->db->get('blocks');
		if($query->num_rows() > 0) 
			return $query->row_array();
		
		return FALSE;
	}
	
	// Not really used, but could be later on..
	public function block_list(){
		$this->db->select('hash')
				 ->order_by('id', 'asc');
		$query = $this->db->get('blocks');
		if($query->num_rows() > 0) 
			return $query->result_array();
			
		return FALSE;
	}

};


/* End of file: bitcoin_model.php */
