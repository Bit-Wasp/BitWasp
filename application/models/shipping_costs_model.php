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
	 * For Item
	 * 
	 * Loads the array of shipping costs for a specified item. Returns
	 * the array if successful, or FALSE if no entries exist.
	 * 
	 * @param	int	$item_id
	 * @return	array/FALSE
	 */
	public function for_item($item_id) {
		$this->db->where('item_id', $item_id);
		$query = $this->db->get('shipping_costs');
		if($query->num_rows() > 0) { 
			$results = $query->result_array();
			$output = array();
			foreach($results as $res) {
				$output[$res['destination_id']] = array('cost' => $res['cost'],
														'enabled' => $res['enabled'],
														'destination_id' => $res['destination_id'],
														'destination_f' => $this->general_model->location_by_id($res['destination_id']));
			}
			return $output;
		} 
		return FALSE;
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
