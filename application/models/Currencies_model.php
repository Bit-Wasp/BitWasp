<?php 	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currencies Model
 *
 * This class handles handles database queries regarding currencies.
 * 
 * @package		BitWasp
 * @subpackage	Models
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
	 * If not, then load all currencies
	 *
	 * @access	public
	 * @param	int	$id
	 * @return	array/FALSE
	 */				
	public function get($id = NULL) {
		if($id == NULL)
		{
			$this->db->select('id, code, name, symbol, crypto_magic_byte');	// Duplicated to avoid a stupid error..
			$query = $this->db->get('currencies');
		}
		else
		{
			$this->db->select('id, code, name, symbol, crypto_magic_byte');
			$query = $this->db->get_where('currencies', array('id' => "$id"));
		}
		
		$results = array();
		if ($query->num_rows() > 0)
		{
			if ($id == NULL)
				return $query->result_array();
				
			$row = $query->row_array();
			$row['rate'] = $this->bw_config->exchange_rates[(strtolower($row['code']))];
			return $row;
		}
		
		return FALSE;
	}

	/**
	 * Get Rates
	 * 
	 * Loads the exchange rates for the currencies in memory, but accepts
	 * an $override array containing currency codes 'USD'/'EUR', etc. 
	 * 
	 * @param		array	$override
	 * @return		array
	 */
	public function get_rates($override = NULL) {
		$currencies = ($override == NULL) ? $this->bw_config->currencies : $override ;
		
		$this->db->select('time');
		foreach ($currencies as $currency) 
		{
			$this->db->select(strtolower($currency['code']));
		}
		
		$query = $this->db->order_by('id desc')->limit('1')->get('exchange_rates');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			$row['time_f'] = $this->general->format_time($row['time']);
			return $row;
		}
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
	 * @param	array	$update
	 * @return	bool
	 */					
	public function update_exchange_rates($update) {
		return ($this->db->insert('exchange_rates', $update) == TRUE) ? TRUE : FALSE;
	}
};
