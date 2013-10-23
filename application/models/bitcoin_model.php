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

	/**
	 * Current Balance
	 * 
	 * Will load the current users balance if $user_hash is not set. If
	 * $user_hash is set, we will load that users balance. Once the 
	 * required user record is found, return the bitcoin balance. Returns
	 * FALSE if there is no record.
	 *
	 * @access	public
	 * @param	NULL/string	$user_hash
	 * @return	int/FALSE
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
	 * Load the balance of unverified transactions to do with the specified
	 * user's account.
	 *
	 * @access	public	
	 * @param	string	$user_hash
	 * @return	int
	 */			
	public function unverified_transactions($user_hash) {
		$this->db->select('value')
				 ->where('user_hash', $user_hash)
				 ->where('txn_id !=', "Fee's Payment")
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
	 * Loads an array of user transactions, by searching for the users hash.
	 * If there are records, we format this information, and build up the
	 * results array. Returns an array if successful, returns FALSE on failure.
	 *
	 * @access	public
	 * @param	string	$user_hash
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
	 * Search for the bitcoin topup address for the specified user. Once
	 * the user exists, check if the bitcoin_topup_address is unset for 
	 * any reason (maybe bitcoind was down). If so, generate a new one.
	 * Return a bitcoin topup address if at all possible, otherwise
	 * return FALSE;
	 * 
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @return	string/FALSE
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
	 * Load the cashout address for the user. If the record exists, then
	 * return the bitcoin address. Otherwise, return FALSE.
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @return	string/FALSE
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
	 * Log the bitcoin address so we can search for it in future. This is
	 * in case the user tries to top up using one of their old addresses.
	 * Returns TRUE if the insert was successful, FALSE if it failed.
	 * 
	 * @access	public
	 * @param	string	$user_hash
	 * @param	string	$address
	 * @return	bool
	 */			
	public function log_user_address($user_hash, $address) {
		return ($this->db->insert('addresses', array('user_hash' => $user_hash, 'bitcoin_address' => $address))) ? TRUE : FALSE;
	}
	
	/**
	 * Set User Address
	 * 
	 * Set the address on the users profile. If the update is successful,
	 * log the address to the table. If unsuccessful, return FALSE.
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @param	string	$address
	 * @return	bool
	 */			
	public function set_user_address($user_hash, $address) {
		if($address == NULL)
			return FALSE;
			
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
	 * Update the users profile to record their cashout address. Returns
	 * TRUE if the update was successful. FALSE on failure. 
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @param	string	$address
	 * @return	bool
	 */			
	public function set_cashout_address($user_hash, $address) {
		$this->db->where('user_hash', $user_hash);
		return ($this->db->update('users', array('bitcoin_cashout_address' => $address))) ? TRUE : FALSE;
	}
	
	/**
	 * Get Address Owner
	 * 
	 * Called by Walletnotify, check to see who owns this address.
	 * Used to associate a transaction with a user_hash. Returns FALSE
	 * if no record is found, or returns the user_hash if successful.
	 *
	 * @access	public
	 * @param	string	$address
	 * @return	string/FALSE
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
	 * Used for cashouts. Returns the user_hash if successful, returns
	 * FALSE on failure.
	 *
	 * @access	public
	 * @param	string	$address
	 * @return	string/FALSE
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
	 * Is added once for a bitcoin topup/cashout. Also used to record
	 * the outcome of an order. Returns TRUE if successful. Returns FALSE
	 * if unsuccessful.
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
	 * @param	array	$array
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
	 * Check if there is already a record of this transaction. Looks the
	 * transaction up based on the user's hash, transaction ID, and the
	 * category. Makes the search more specific, to avoid issues. Returns
	 * TRUE if the transaction is on record already, FALSE if it's not.
	 *
	 * @access	public
	 * @param	string	$user_hash
	 * @param	string	$txn_hash
	 * @param	string	$category
	 * @return	bool
	 */			
	public function user_transaction($user_hash, $txn_hash, $category){
		$this->db->select('id')
				 ->where('user_hash', $user_hash)
				 ->where('txn_id', $txn_hash)
				 ->where('category', $category);
				
		$query = $this->db->get('pending_txns');
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
			
	/**
	 * Have Transaction
	 * 
	 * Checks if we have this transaction ID. Very very bad.
	 * Should be deprecated in favour of user_transaction()...
	 * Not specific enough. Returns the transaction if successful, or
	 * FALSE if it's not found. Wallet Notify callback.
	 *
	 * @access	public
	 * @param	string	$txn_id
	 * @return	array/FALSE
	 */		
	/*public function have_transaction($txn_id) {
		$this->db->where('txn_id', $txn_id);
		$query = $this->db->get('pending_txns');
		if($query->num_rows() > 0)
			return $query->row_array();
			
		return FALSE;
	}*/
	
	/**
	 * Update Credits 
	 * 
	 * Called by Blocknotify when crediting accounts, and by bitcoin::cashout()
	 * when sending accounts. And by escrow_model::pay() to issue payment. 
	 * And orders::place() to deduct the buyers account.
	 * Requires an array, where each update is contained as an entry in that array.
	 * Often only suppling one update. The value can be negative.
	 * Called by Wallet Notify when debiting accounts.
	 * Eg: 
	 * $updates = array('0' => array('user_hash' => '...',
	 * 								 'value' => '...'),
	 * 					'1' => array('user_hash' => '...',
	 * 								 'value' => '...'));
	 * @access	public
	 * @param	array
	 * @return	bool
	 */			
	public function update_credits($updates) {
		$code = TRUE;
		// Loop through each update, and update accordingly. 
		
		foreach($updates as $update) {
			// Calculate the new balance. 
			$balance = $this->current_balance($update['user_hash'])+$update['value'];	
			
			// Update the users balance.
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
	 * @param	string	$txn_id
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
	 * Returns an array of the transactions. Returns an empty array if 
	 * there are no records. Script then checks each of these for updates.
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
		return ($query->num_rows() > 0) ? $query->result_array() : $res;
	}
	
	/**
	 * Update Confirmations
	 * 
	 * An array of updates is supplied, taking the following format. 
	 * $updates = array('0' => array('txn_id' => '...',
	 * 								 'confirmations' => '...'),
	 * 					.	.	.	.	.	.	.	.	.	
	 *					);
	 * We update every transaction on record every time there's a new block
	 * This makes sure a reorg doesn't cause an orphaned transaction to 
	 * go through. 
	 * 
	 * @access	public
	 * @param	array	$updates
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
	 * Called by blocknotify to record the block & its height. Returns TRUE
	 * if the insertion was successful, otherwise returns FALSE.
	 *
	 * @access	public
	 * @param	string	$block_hash
	 * @param	int	$number
	 * @return	bool
	 */			
	public function add_block($block_hash, $height) {
		return ($this->db->insert('blocks', array('hash' => $block_hash, 'number' => "$height")) == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Have Block
	 * 
	 * Called by Blocknotify. Check if we have a block with that hash.
	 * Returns TRUE if we do, returns FALSE otherwise.
	 *
	 * @access	public
	 * @param	string	$block_hash
	 * @return	boolean
	 */		
	public function have_block($block_hash) {
		$this->db->select('id')
				 ->where('hash', "$block_hash");
		$query = $this->db->get('blocks');
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Latest Block
	 * 
	 * Load information about the latest block on record. Not really used
	 * apart from testing purposes. Could display information on the admin
	 * panel. Might start storing the block generation time also?
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
