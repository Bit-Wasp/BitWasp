<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Shipping Costs Model
 *
 * Model to contain database queries for dealing with shipping costs.
 * 
 * @package		BitWasp
 * @subpackage	Models
 * @category	Items
 * @author		BitWasp
 * 
 */
class Shipping_costs_model extends CI_Model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get Item Hash
	 * 
	 * This helper function obtains the item hash for the item $id.
	 * Returns the hash if the item exists, or FALSE on failure.
	 * 
	 * @param	int $id
	 * @return	string/FALSE
	 */
	public function get_item_hash($id){
		$this->db->select('hash');
		$this->db->where('id', $id);
		$query = $this->db->get('items');
		if($query->num_rows > 0){
			$row = $query->row_array();
			return $row['hash'];
		} 
		return FALSE;
	}
	
	/**
	 * For Item Raw
	 * 
	 * Load the raw array of shipping information for the $item_id. Returning
	 * results for locations not currently on offer is possible by setting
	 * $all to TRUE.
	 * 
	 * This is used to display the shipping configuration format, where
	 * converting to bitcoins is not desired if the item has a different
	 * currency.
	 * This information returned by this functioncanbe parsed by the for_item()
	 * function.
	 * 
	 * @param	int	$item_id
	 * @param	boolean(optional) 
	 * @return	array/FALSE
	 */
	public function for_item_raw($item_id, $all = FALSE) {
		$this->load->model('items_model');
		$this->load->model('location_model');
		
		if($all == FALSE)
			$this->db->where('enabled', '1');
			
		$this->db->where('item_id', $item_id);
		$query = $this->db->get('shipping_costs');
		$results = array();
		foreach($query->result_array() as $res){
			$res['destination_f'] = $this->location_model->location_by_id($res['destination_id']);
			$results[] = $res;
		}
		return (count($results) > 0) ? $results : FALSE;
	}
	
	
	/**
	 * For Item
	 * 
	 * Loads the raw array of shipping costs for a specified item. Then
	 * it processes this, to convert the values to the BTC for universal use.
	 * Returns an array if successful, or FALSE if no entries exist.
	 * 
	 * @param	int	$item_id
	 * @return	array/FALSE
	 */
	public function for_item($item_id, $all = FALSE) {
		$query = $this->for_item_raw($item_id, $all);
		if($query !== FALSE) { 
			
			$item_hash = $this->get_item_hash($item_id);
			$item = $this->items_model->get($item_hash);
			
			$output = array();
			foreach($query as $res) {
				if($item['currency']['id'] !== '0'){
					$this->load->model('currencies_model');
					$currency = $this->currencies_model->get($item['currency']['id']);
					$btc_cost = $res['cost']/$currency['rate'];
				} else {
					$btc_cost = $res['cost'];
				}
				
				$output[$res['destination_id']] = array('cost' => round($btc_cost, 8, PHP_ROUND_HALF_UP),
														'currency' => $item['currency']['symbol'],
														'enabled' => $res['enabled'],
														'destination_id' => $res['destination_id'],
														'destination_f' => $this->location_model->location_by_id($res['destination_id']));
			}
			return $output;
		} else {
			return FALSE;
		}
		
	}

	/**
	 * Find Location Cost
	 * 
	 * This function will load the list of shipping costs, and check if 
	 * the supplied user location is allowed, by being a child of a
	 * location in the shipping costs list. Returns the cost info if 
	 * the user has an acceptable location, otherwise returns false.
	 * 
	 * @param	int	$item_id
	 * @param	int $user_location_id
	 * return	boolean
	 */
	public function find_location_cost($item_id, $user_location_id) {
		$this->load->model('location_model');
		$costs_list = $this->for_item($item_id);
		foreach($costs_list as $destination_id => $cost_info) {
			if($destination_id == 'worldwide')
				$worldwide = $cost_info;
				
			if($this->location_model->validate_user_child_location($destination_id, $user_location_id) !== FALSE)
				$btc_cost = $cost_info;
		}
		return (isset($worldwide)) ? $worldwide : FALSE;
	}
	
	/**
	 * Item List Cost
	 * 
	 * This function calculates the shipping cost for an array of items.
	 * It requires the users location.
	 * 
	 * @param	array	$items
	 * @param	int	$location
	 * @return	float
	 */
	 public function costs_to_location($item_list, $location) {
		 // Work out the cost in bitcoin.
		 $cost = 0.00000000;
		 foreach($item_list as $item) {
			 $costs = $this->for_item($item['id']);
			 // Try the users location_id as the index.. if that's not 
			 // there, the user must be buying a worldwide item.
			 $tmp = (isset($costs[$location])) ? $costs[$location]['cost'] : $costs['worldwide']['cost'];
			 $cost+= $tmp*$item['quantity'];
		 }
		 
		 return $cost;
	 }

	/**
	 * List by Location
	 * 
	 * Used by the items controller to find items available in a particular
	 * location.
	 * 
	 * @param	int	$location_id
	 * @return	array/FALSE
	 */
	public function list_IDs_by_location($location_id) {
		$this->db->where('destination_id', $location_id);
		$query = $this->db->get('shipping_costs');
		if ($query->num_rows() > 0) {
			$tmp = array();
			foreach($query->result_array() as $item){
				array_push($tmp, $item['item_id']);
			}
			return $tmp;
		}
		return FALSE;
	}
	
	/**
	 * Update Shipping Costs
	 * 
	 * Supply an array containing information about the costs for the
	 * item. This function will delete any costs currenty held, and 
	 * replace them with the current information.
	 * 
	 * @param	int	$item_id
	 * @param	array	$costs_array
	 * @return	boolean
	 */ 
	public function update($item_id, $costs_array) {
		$this->db->where('item_id', $item_id);
		$delete = $this->db->delete('shipping_costs');
		if(!$delete)
			return FALSE;
			
		foreach($costs_array as $location => $info) {
			$array = array('cost' => $info['cost'],
						   'destination_id' => $info['destination_id'],
						   'enabled' => $info['enabled'],
						   'item_id' => $item_id);
			if($this->db->insert('shipping_costs', $array) !== TRUE)
				return FALSE;
		}

		return TRUE;
	}
};

/* End of File: Shipping_costs_model.php */
