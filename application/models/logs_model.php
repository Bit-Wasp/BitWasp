<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Logs Model
 *
 * This model is used to record and display logs and debugging information
 * to Admins about the activity taking place on the server.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Logs
 * @author		BitWasp
 * 
 */
class Logs_Model extends CI_Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	public function __construct() {
	}
	
	public function fetch($hash = NULL) {
		$query = ($hash == NULL) ? $this->db->get('logs') : $this->db->get_where('logs', array('hash', $hash));

		if($query->num_rows() > 0) {
			$results = $query->result_array();
			foreach($results as &$result) {
				$result['time_f'] = $this->general->format_time($result['time']);
			}
			return ($hash == NULL) ? $results[0] : $results;
		}
		return FALSE;
	}
	
	public function add($caller, $title, $message, $level){
		return ($this->db->insert('logs', array('caller' => $caller,
												'title' => $title, 
												'level' => $level,
												'hash' => $this->general->unique_hash('logs','hash'),
												'message' => $message) == TRUE)
				) ? TRUE : FALSE;
	}
};

/* End of file logs_model.php */
