<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Escrow Model
 *
 * This class handles the buyer and vendor side of the order process.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Escrow
 * @author		BitWasp
 * 
 */
class Escrow_model extends CI_Model {
	
	/**
	 * Constructor
	 * 
	 * Load libs/models.
	 *
	 * @access	public
	 * @see		Models/Bitcoin_Model
	 * @see		Models/Accounts_Model
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('accounts_model');
		$this->load->model('bitcoin_model');
	}
	
	/**
	 * Add
	 * 
	 * Record an escrow agreement. Debits a buyers account. Add's the
	 * funds to the escrow account.
	 *
	 * $info = array('buyer_hash' => '...',
	 * 				 'amount' => '...'
	 * ................................
	 * ................................
	 * 
	 * @access	public
	 * @param	array $info
	 * @return	bool
	 */	
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

	/**
	 * Get
	 * 
	 * Load an escrow agreement from the table, by the order_id.
	 *
	 * @access	public
	 * @param	int	$order_id
	 * @return	array / FALSE;
	 */					
	public function get($order_id) {
		$this->db->where('order_id',$order_id);
		$query = $this->db->get('escrow');
		return ($query->num_rows() > 0) ? $query->row_array() : FALSE;
	}

	/**
	 * Delete
	 * 
	 * Delete an escrow agreement, by the order_id.
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */					
	public function delete($order_id) {
		$this->db->where('order_id', $order_id);
		return ($this->db->delete('escrow') == TRUE) ? TRUE : FALSE;
	}
	
	/**
	 * Finalize
	 * 
	 * I think this might be deprecated in favour of pay()
	 *
	 * @access	public
	 * @param	int $order_id
	 * @return	bool
	 */				
	public function finalize($order_id) {
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
	
	/**
	 * Balance
	 * 
	 * Load the current users escrow balance.
	 *
	 * @access	public
	 * @return	bool
	 */					
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
	
	/**
	 * Update Exchange Rates
	 * 
	 * Insert a new row of information about exchange rates.
	 *
	 * @access	public
	 * @param	int	$order_id
	 * @param	string 	$user
	 * @return	bool
	 */					
	public function pay($order_id, $user, $message = NULL) {
		// Abort if escrow record does not exist.
		$escrow = $this->get($order_id);
		if($escrow == FALSE)
			return FALSE;

		// Determine who is the sender/recipient.
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
			
			if($user == 'vendor'){
				$this->order_model->set_finalized($order_id); 
							
				$message = ($message == NULL) ? "Order #$order_id" : $message;
								 
				// Record the transaction in the senders account.
				$debit_txn = array( 'txn_id' => $message,
									'user_hash' => $sender['user_hash'],
									'value' => "-".(float)$escrow['amount'],
									'confirmations' => '>50',
									'address' => '[payment]',
									'category' => 'send',
									'credited' => '1',
									'time' => time());
				$this->bitcoin_model->add_pending_txn($debit_txn);						
			
				// Record the transaction in the recipients account.
				$credit_txn = array( 'txn_id' => $message,
									'user_hash' => $recipient['user_hash'],
									'value' => (float)$escrow['amount'],
									'confirmations' => '>50',
									'address' => '[payment]',
									'category' => 'receive',
									'credited' => '1',
									'time' => time());
				$this->bitcoin_model->add_pending_txn($credit_txn);
			}
			
			if($this->delete($order_id) == TRUE)
				return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Dispute
	 *
	 * Insert a dispute record into the database.
	 *
	 * @access	public
	 * @param	array	$info
	 * @return	bool
	 */					
	public function dispute($order_id, $info) {
		$info['admin_message'] = 'Awaiting Response';
		return ($this->db->insert('disputes', $info) == TRUE) ? TRUE : FALSE;
	}

	/**
	 * Get Dispute
	 * 
	 * Load a dispute record as specified by the $order_id.
	 *
	 * @access	public
	 * @param	int	$order_id
	 * @return	array/FALSE
	 */					
	public function get_dispute($order_id) {
		$this->db->where('order_id', $order_id);
		$query = $this->db->get('disputes');
		if($query->num_rows() > 0){
			$row = $query->row_array();
			$row['dispute_message'] = nl2br($row['dispute_message']);
			$row['last_update_f'] = $this->general->format_time($row['last_update']);
			return $row;
		}
		return FALSE;
	}
	
	/**
	 * Disputes List
	 * 
	 * Used in the admin panel to display a list of outstanding disputes
	 * 
	 * @access	public
	 * @return	array/FALSE
	 */
	public function disputes_list() {
		$this->db->order_by('last_update DESC');
		$query = $this->db->get('disputes');
		if($query->num_rows() > 0){
			$result = $query->result_array();
			foreach($result as &$dispute){
				$dispute['disputing_user'] = $this->accounts_model->get(array('id' => $dispute['disputing_user_id']));
				$dispute['other_user'] = $this->accounts_model->get(array('id' => $dispute['other_user_id']));
				$dispute['last_update_f'] = $this->general->format_time($dispute['last_update']);
			}
			return $result;
		}
		return FALSE;
	}
	
	/**
	 * Update Dispute
	 * 
	 * Update Dispute number $order_id with info $info.
	 *
	 * @access	public
	 * @param	int	$order_id
	 * @param	array	$info
	 * @return	bool
	 */					
	public function update_dispute($order_id, $info) {
		$this->db->where('order_id', $order_id);
		return ($this->db->update('disputes', $info) == TRUE) ? TRUE : FALSE;
	}
};


/* End of File: escrow_model.php */
