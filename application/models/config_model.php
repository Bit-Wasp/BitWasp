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
	
	public function get(){
		return $this->config;
	}	
	
	public function update($records) {
		foreach($records as $key => $update){
			$this->db->where('id', '1');
			$this->db->update('config', array($key => $update));
		}
	}
		
};
