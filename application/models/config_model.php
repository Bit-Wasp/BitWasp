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

	public function load_autorun_intervals() {
		$query = $this->db->get('purge_intervals');
		return ($query->num_rows() > 0) ? $query->result_array() : FALSE;
	}
	
	public function set_autorun_updated($index) {
		$this->db->where('index', $index);
		return ($this->db->update('purge_intervals', array('last_update' => time()))) ? TRUE : FALSE ;
	}
	
	public function set_autorun_interval($index, $interval) {
		$this->db->where('index', $index);
		return ($this->db->update('purge_intervals', array('interval' => "$interval"))) ? TRUE : FALSE ;
	}	
	
};
