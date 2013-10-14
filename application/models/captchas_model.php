<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Captchas Model
 *
 * This class handles the creation, removal, retrieval, and purging of
 * catpcha's for authentication requests.
 * 
 * @package		BitWasp
 * @subpackage	Model
 * @category	Captcha
 * @author		BitWasp
 * 
 */
class Captchas_model extends CI_Model {

	/**
	 * Constructor
	 * 
	 * Load the CodeIgniter framework
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set
	 * 
	 * Sets a captcha based on the key (held in the session), solution, and time. 
	 *
	 * @access	public
	 * @param	string	$identifier
	 * @param	string	$solution
	 * @return	bool
	 */			
	public function set($identifier, $solution) { 
		$data = array(	'key' => $identifier,
						'solution' => $solution,
						'time' => time() );
						
		return ($this->db->insert('captchas', $data)) ? TRUE : FALSE;
	}
	
	/**
	 * Purge Expired
	 * 
	 * Purge categories older than the specified time.
	 *
	 * @access	public
	 * @param	int	$time
	 * @return	bool
	 */			
	public function purge_expired($time){
		$this->db->where('time <', $time);
		$delete = $this->db->delete('captchas');
		return ($delete) ? TRUE : FALSE;
	}
	
	/**
	 * Get
	 * 
	 * Load a captcha by its identifier.
	 *
	 * @access	public
	 * @param	string	$identifier
	 * @return	bool
	 */			
	public function get($identifier) {
		$this->db->select('id, solution')
				 ->from('captchas')
				 ->where('key', $identifier);
				 
		$query = $this->db->get();
		if($query->num_rows() == 0){
			// Failure; key invalid, captcha has expired.
			return NULL;
		} else {
			$row = $query->row_array();
			$this->delete($row['id']);			// Delete the used captcha!
			return $row;
		}
	}
	
	/**
	 * Delete
	 * 
	 * Delete a captcha as specified by the ID.
	 *
	 * @access	public
	 * @param	int	$id
	 * @return	bool
	 */			
	public function delete($id) { 
		return ($this->db->delete('captchas', array( 'id' => $id ) )) ? TRUE : FALSE;
	}

};
