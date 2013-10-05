<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model {
	
	// Check the required authorization for the URI. (first segment).
	public function check_auth($URI) {
		$this->db->select('auth_level')
				 ->where(array('URI' => $URI));
				 
		$query = $this->db->get('page_authorization');
		
		if($query->num_rows() > 0){
			$row = $query->row_array();
			return $row['auth_level'];
		}
		
		return FALSE;
	}
	
	// Check the timeout associated with the auth_req for this page.
	public function check_auth_timeout($URI) {
		$this->db->select('timeout')
				 ->where(array('URI' => $URI));

		$query = $this->db->get('page_authorization');
		
		if($query->num_rows() > 0){
			$row = $query->row_array();
			return $row['timeout']*60;
		}
		
		return FALSE;
	}
	
	// Check the supplied solution to a two-factor auth.
	public function check_two_factor_token($token) {
		$result = FALSE;
		
		$this->db->select('id')
				 ->where('user_id', $this->current_user->user_id)
				 ->where('solution', $token);
				 
		$query = $this->db->get('two_factor_tokens');
		if($query->num_rows() > 0) 	
			$result = TRUE;
		
		$this->db->where('user_id', $this->current_user->user_id);
		$this->db->delete('two_factor_tokens');
		
		return $result;
	}
	
	// Add a two factor token.
	public function add_two_factor_token($token) {
		$array = array( 'solution' => $token,
						'user_id' => $this->current_user->user_id);			
		if($this->db->insert('two_factor_tokens', $array) == TRUE)
			return TRUE;
		
		return FALSE;
	}
};
