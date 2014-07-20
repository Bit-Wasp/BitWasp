<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Images Model
 *
 * This class handles the database queries relating to images.
 *
 * @package        BitWasp
 * @subpackage    Models
 * @category    Images
 * @author        BitWasp
 *
 */
class Images_model extends CI_Model
{

    /**
     * Constructor
     *
     * @access    public
     * @return    void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get
     *
     * Load an image, identified by the $image_hash. If the record is found,
     * then return the row of information. If not, and once the requested
     * $image_hash !== 'default', try loading the 'default' image hash instead.
     *
     * @access    public
     * @param    int $image_hash
     * @return    array/FALSE
     */
    public function get($image_hash)
    {
        $query = $this->db->select('hash, encoded, height, width')->where('hash', $image_hash)->get('images');
        if ($query->num_rows() > 0)
            return $query->row_array();

        return ($image_hash !== 'default') ? $this->get('default') : FALSE;
    }

    /**
     * Add to Item
     *
     * Add's the base64 encoded image to the database as with Images_Model\Add(),
     *  and then records a link between the image and the item (by adding
     * a record to item_images). If the $main_image indicator is set to true,
     * then this $image_hash will be set as the main image for $item_hash.
     *
     * @access    public
     * @param    string $image_hash
     * @param    string $encoded_string
     * @param    string $item_hash
     * @param    boolean $main_image
     * @return    boolean
     */
    public function add_to_item($image_hash, $encoded_string, $item_hash, $main_image = FALSE)
    {
        $insert = $this->add($image_hash, $encoded_string);

        if ($insert == TRUE) {
            $link = array('image_hash' => $image_hash,
                'item_hash' => $item_hash);

            if ($this->db->insert('item_images', $link) == TRUE) {
                // If we need to update the main image, do it now.
                if ($main_image == TRUE)
                    $this->main_image($item_hash, $image_hash);

                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Add
     *
     * Add an encoded image to the images table. Specified by $file_name,
     * we load a base64 encoded version of the image, and enter it into the
     * table, along with the $image_hash. Returns TRUE if the record added
     * successfully, and FALSE on failure.
     *
     * @access    public
     * @param    string $image_hash
     * @param    string $encoded_string
     * @return    boolean
     */
    public function add($image_hash, $encoded_string)
    {
        $insert = array('hash' => $image_hash,
            'encoded' => $encoded_string);
        return $this->db->insert('images', $insert) == TRUE;
    }

    /**
     * Main Image
     *
     * Updates the item record identified by $item_hash, to set the items
     * main image as $image_hash. Returns TRUE if successful, false if unsuccessful.
     *
     * @access    public
     * @param    string $item_hash
     * @param    string $image_hash
     * @return    array / FALSE
     */
    public function main_image($item_hash, $image_hash)
    {
        $update = array('main_image' => $image_hash);
        $this->db->where('hash', $item_hash);
        return $this->db->update('items', $update) == TRUE;
    }

    /**
     * Get Item
     *
     * Search for the the $item_hash coresponding to an $image_hash.
     * Returns the item_hash string if the record is found, and FALSE
     * if the specified image does not exist.
     *
     * @access    public
     * @param    string $image_hash
     * @return    array/FALSE
     */
    public function get_item($image_hash)
    {
        $query = $this->db->select('item_hash')->where('image_hash', $image_hash)->get('item_images');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['item_hash'];
        }
        return FALSE;
    }

    /**
     * Delete Item Image
     *
     * Deletes an image from an item. The function will check if it just
     * deleted the main_image for the item, in which case it will either
     * take the first image from the items images and use that, or revert
     * to the default image. Removes related thumbnail/large images. Returns
     * TRUE on success, and FALSE on failure.
     *
     * @access    public
     * @param    string $item_hash
     * @param    string $image_hash
     * @return    array / FALSE
     */
    public function delete_item_img($item_hash, $image_hash)
    {
        // Delete link
        $this->db->where('image_hash', $image_hash);
        $link = $this->db->delete('item_images');

        // Get main_image
        $this->db->select('main_image')
            ->where('hash', $item_hash);
        $item = $this->db->get('items');
        $item = $item->row_array();

        // As the link has been deleted, it's safe to update main_image to a new one before deleting the image.
        if ($item['main_image'] == $image_hash) {
            $images = $this->by_item($item_hash);
            if (count($images) > 0) {
                $this->main_image($item_hash, $images[0]['hash']);
            } else {
                $this->main_image($item_hash, 'default');
            }
        }

        // Delete the regular and large image from the table.
        $this->db->where('hash', $image_hash);
        $image = $this->db->delete('images');
        $this->db->where('hash', $image_hash . "_l");
        $image_l = $this->db->delete('images');

        return ($link == TRUE) && ($image == TRUE) && ($image_l == TRUE);
    }

    /**
     * By Item
     *
     * Selects all image records associated with an item. If records are
     * found, we call $this->get() on each result to load further data about
     * the image. Results are compiled into an array, no images causes
     * an empty array to be returned.
     *
     * @access    public
     * @param    int $item_hash
     * @return    array
     */
    public function by_item($item_hash)
    {
        $query = $this->db->select('image_hash')->where('item_hash', $item_hash)->get('item_images');

        $results = array();
        if ($query->num_rows() > 0) {
            $this->db->select('hash, encoded, height, width');
            $hashes = array();
            foreach ($query->result_array() as $res) {
                $hashes[] = $res['image_hash'];
            }
            $this->db->where_in('hash', $hashes);
            $images = $this->db->get('images');
            if ($images->num_rows() > 0)
                $results = $images->result_array();
        }

        return $results;
    }
}

;

/* End of File: Images_model.php */
/* Location: application/models/images_model.php */