<?php 	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currencies Model
 *
 * This class handles handles database queries regarding currencies.
 * 
 * @package		BitWasp
 * @subpackage	Controllers
 * @category	Currencies
 * @author		BitWasp
 * 
 */

class Currencies_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * Load the config model
	 * 
	 * @see		Libraries/Bw_Config
	 * @access	public
	 * @return	void
	 */		
	public function __construct() { 
		parent::__construct();
	}
	
	/**
	 * Get
	 * 
	 * Load a specific currency if the $id parameter is set as an argument.
	 * If not, then load all catgories.
	 *
	 * @access	public
	 * @param	int	$id
	 * @return	array/FALSE
	 */				
	public function get($id = NULL) {
		
		if($id == NULL) {
			$this->db->select('id, code, name, symbol');	// Duplicated to avoid a stupid error..
			$query = $this->db->get('currencies');
		} else {
			$this->db->select('id, code, name, symbol');
			$query = $this->db->get_where('currencies', array('id' => "$id"));
		}
		
		$results = array();
		if($query->num_rows() > 0) {
			if($id == NULL)
				return $query->result_array();
			
			$row = $query->row_array();
			$row['rate'] = $this->get_exchange_rate(strtolower($row['code']));
			return $row;
		}
		
		return FALSE;
	}

	/**
	 * Get all Exchange Rates
	 * 
	 * Load the latest set of exchange rates from the exchange_rates table.
	 * Formats the timestamp into a nicer looking format. Returns an array
	 * on a successful run, otherwise, returns FALSE.
	 *
	 * @access	public
	 * @return	boolean/array
	 */				
	public function get_exchange_rates() {
		$this->db->select('time, usd, eur, gbp');
		$this->db->order_by('id desc');
		$this->db->limit('1');
		
		$query = $this->db->get('exchange_rates');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			$row['time_f'] = $this->general->format_time($row['time']);
			
			$result = array('bpi' => array('usd' => number_format($row['usd'],4,".",","),
											'eur' => number_format($row['eur'],4,".",","),
											'gbp' => number_format($row['gbp'],4,".",",") ),
							'time' => $row['time'],
							'time_f' => $this->general->format_time($row['time']));
			return $result;
		}
		return FALSE;
	}
	
	/**
	 * Get Exchange Rate
	 * 
	 * Load the rate of a specific currency. If the entry exists, return
	 * the rate. Otherwise return FALSE.
	 *
	 * @access	public
	 * @param	string	$code
	 * @return	int / FALSE
	 */					
	public function get_exchange_rate($code) {
		$this->db->order_by('id desc');
		$this->db->limit('1');
		
		$query = $this->db->get('exchange_rates');
		if($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row[$code];
		}
		return FALSE;
	}
	
	/**
	 * Update Exchange Rates
	 * 
	 * Insert a new row of information about exchange rates. Returns TRUE 
	 * if the insert was successful, FALSE if it failed.
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 */					
	public function update_exchange_rates($update) {
		return ($this->db->insert('exchange_rates', $update) == TRUE) ? TRUE : FALSE;
	}
};
