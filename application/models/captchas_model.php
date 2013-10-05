<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Captchas_model extends CI_Model {

	// Set a captcha.
	public function set($identifier, $solution) { 
		$data = array(	'key' => $identifier,
						'solution' => $solution,
						'time' => time() );
						
		if($this->db->insert('captchas', $data))
			return TRUE;
		
		return FALSE;
	}
	
	// Purge all expired captchas.
	public function purge_expired($time){
		$this->db->where('time <', $time);
		$delete = $this->db->delete('captchas');
		if($delete)
			return TRUE;
		
		return FALSE;
	}
	
	// Load a captcha by its random identifier.
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
	
	// Delete a captcha by it's id.
	public function delete($id) { 
		if($this->db->delete('captchas', array( 'id' => $id ) ))
			return TRUE;
		
		return FALSE;
	}

};
