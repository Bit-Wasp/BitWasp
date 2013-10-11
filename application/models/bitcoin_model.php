<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bitcoin Model
 *
 * This class handles the database queries relating to orders.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Bitcoin
 * @author		BitWasp
 * 
 */

class Bitcoin_model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */		
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
	
	/**
	 * Current Balance
	 * 
	 * Loads the current users balance, or optionally another users balance.
	 *
	 * @access	public
	 * @param	NULL / string
	 * @return	int / FALSE
	 */			
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
	
	/**
	 * Unverified Transactions
	 * 
	 * Load the balance of unverified transactions.
	 *
	 * @access	public
	 * @param	string
	 * @return	int
	 */			
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
	
	/**
	 * User Transactions
	 * 
	 * Loads an array of user transactions.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
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
											 
	/**
	 * Get User Address
	 * 
	 * Load the bitcoin address for the specified user.
	 *
	 * @access	public
	 * @param	string
	 * @return	string / FALSE
	 */			
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
	
	/**
	 * Get Cashout Address
	 * 
	 * Load the cashout address for the user.
	 *
	 * @access	public
	 * @param	string
	 * @return	string / FALSE
	 */			
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
	
	/**
	 * Log User Address
	 * 
	 * Record all bitcoin addresses in case the user uses them again later.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */			
	public function log_user_address($user_hash, $address) {
		return ($this->db->insert('addresses', array('user_hash' => $user_hash, 'bitcoin_address' => $address))) ? TRUE : FALSE;
	}
	
	/**
	 * Set User Address
	 * 
	 * Record the users address to their profile and to the log.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
	public function set_user_address($user_hash, $address) {
		$this->db->where('user_hash', $user_hash);
		if($this->db->update('users', array('bitcoin_topup_address' => $address))) {
			$this->log_user_address($user_hash, $address);
			return TRUE;
		}
		
		return FALSE;	
	}
	
	/**
	 * Set Cashout Address
	 * 
	 * Sets a cashout address for the specified user.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */			
	public function set_cashout_address($user_hash, $address) {
		$this->db->where('user_hash', $user_hash);
		return ($this->db->update('users', array('bitcoin_cashout_address' => $address))) ? TRUE : FALSE;
	}
	
	/**
	 * Get Address Ownder
	 * 
	 * Called by Walletnotify, check to see who owns this address.
	 * Used to top up.
	 *
	 * @access	public
	 * @param	array
	 * @return	string / bool
	 */			
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
	
	/**
	 * Get Cashout Address Owner
	 * 
	 * Called by Walletnotify, get the owner of the cashout address.
	 * Used for cashouts.
	 *
	 * @access	public
	 * @param	string
	 * @return	string / FALSE
	 */		
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
	
	/**
	 * Add Pending Transaction
	 * 
	 * Records a transaction, with indexes of $array as column names.
	 * 
	 * $array = 
	 *	array( 'txn_id' => "...",
	 *		 'user_hash' => "...",
	 *		 'value' => (float)"...",
	 *		 'confirmations' => "...",
	 *		 'address' => "...",
	 *		 'category' => "...",
	 *		 'credited' => "...",
	 *		 'time' => "...");
	 * 
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
	public function add_pending_txn($array) {
		// A quick check to prevent code from adding transaction twice.
		if($this->user_transaction($array['user_hash'], $array['txn_id'], $array['category']) == FALSE){	
			if($this->db->insert('pending_txns', $array) == TRUE)
				return TRUE;
		}
		
		return FALSE;
	}

	/**
	 * User Transaction
	 * 
	 * Check if there is already a record of this transaction.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
	public function user_transaction($user_hash, $txn_id, $category){
		$this->db->select('id')
				 ->where('user_hash', $user_hash)
				 ->where('txn_id', $txn_id)
				 ->where('category', $category);
				
		$query = $this->db->get('pending_txns');
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
	
	// WalletNotify callback. 
	/* Should be deprecated in favour of user_transaction()... */
		
	/**
	 * Have Transaction
	 * 
	 * Checks if we have this transaction ID. Very very bad.
	 * Should be deprecated in favour of user_transaction()...
	 *
	 * @access	public
	 * @param	string
	 * @return	array / FALSE
	 */		
	public function have_transaction($txn_id) {
		$this->db->where('txn_id', $txn_id);
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0)
			return $query->row_array();
			
		return FALSE;
	}
	
	// BlockNotify (received) and WalletNotify (sent) callbacks
	
	/**
	 * Update Credits 
	 * 
	 * Called by Blocknotify when crediting accounts, and by bitcoin->cashout
	 * when sending accounts. And by escrow.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
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
	
	/**
	 * Set Credited
	 * 
	 * Records that this transaction HAS been credited to a users balance.
	 * Called by Blocknotify. 
	 * 
	 * @access	public
	 * @param	string
	 * @return	bool
	 */			
	public function set_credited($txn_id) {
		$this->db->where('txn_id', $txn_id);
		return ($this->db->update('pending_txns', array('credited' => '1'))) ? TRUE : FALSE;
	}
	
	/**
	 * Get Pending Transactions
	 * 
	 * Called by Blocknotify, loads the list of pending transactions.
	 *
	 * @access	public
	 * @return	array
	 */			
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
	
	/**
	 * Update Confirmations
	 * 
	 * $updates = array('0' => array('txn_id' => '...',
	 * 								 'confirmations' => '...')
	 * 							.		.			.
	 * 						.				.				.
	 *					);
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */			
	public function update_confirmations($updates) {
		foreach($updates as $update) {
			$this->db->where('txn_id', $update['txn_id']);
			$this->db->update('pending_txns', array('confirmations' => $update['confirmations']));
		}
	}	
	
	/**
	 * Add Block
	 * 
	 * Called by blocknotify to record the block & its height.
	 *
	 * @access	public
	 * @param	string
	 * @param	int
	 * @return	bool
	 */			
	public function add_block($block_hash, $number) {
		return ($this->db->insert('blocks', array('hash' => $block_hash, 'number' => $number))) ? TRUE : FALSE;
	}
	
	/**
	 * Have Block
	 * 
	 * Called by Blocknotify. Check if we have a block with that hash.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */		
	public function have_block($block_hash) {
		$this->db->select('id')
				 ->where('hash', $block_hash);
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Latest Block
	 * 
	 * Load information about the latest block on record.
	 *
	 * @access	public
	 * @return	array / FALSE
	 */			
	public function latest_block() {
		$this->db->select('hash, number')
				 ->order_by('id', 'desc')
				 ->limit(1);
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}
	
	/**
	 * Block List
	 * 
	 * Not really used.
	 *
	 * @access	public
	 * @return	bool
	 */			
	public function block_list(){
		$this->db->select('hash, number')
				 ->order_by('id', 'asc');
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
	}

};


/* End of file: bitcoin_model.php */
