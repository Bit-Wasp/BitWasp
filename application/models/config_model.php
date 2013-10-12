<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Config_model extends CI_Model {
	
	public $config;
	
	// Load from several possible configurations. Default is row 1.
	public function __construct($config = 1){	
		$query = $this->db->get_where('config', array('id' => $config));
		if($query->num_rows() > 0){
			$this->config = $query->row_array();
		} else {
			$this->config = FALSE;
		}
	}

	/**
	 * Get
	 * 
	 * Return the current config as loaded from the database.
	 *
	 * @access	public
	 * @return	array
	 */				
	public function get(){
		return $this->config;
	}	
	
	/**
	 * Update
	 * 
	 * Update column in the config row. Indexes are column name.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */				
	public function update($records) {
		$success = TRUE;
		foreach($records as $key => $update){
			$this->db->where('id', '1');
			if($this->db->update('config', array($key => $update)) !== TRUE)
				$success = FALSE;
		}
		return $success;
	}

	
};
