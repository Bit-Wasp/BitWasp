<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Reviews Controller
 *
 * This class handles reviews of orders and vendors
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Reviews
 * @author        BitWasp
 *
 */
class Reviews extends MY_Controller
{

    /**
     * Constructor
     *
     * Load libs/models.
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('review_auth_model');
        $this->load->model('review_model');
        $this->load->library('form_validation');
    }

    /**
     * View
     *
     * This page displays ratings for the $review_type / $subject_hash.
     * If a third parameter is supplied, then it indicates if the
     * user is looking for only positive, or disputed reviews.
     *
     * @param string $review_type
     * @param string $subject_hash
     * @param mixed $disputed
     */
    public function view($review_type, $subject_hash, $disputed = FALSE)
    {
        if (!in_array($review_type, array('user', 'item')) || !in_array($disputed, array(FALSE, '0', '1')))
            redirect('/');

        $data['review_type'] = $review_type;
        $data['subject_hash'] = $subject_hash;
        $data['disputed'] = $disputed;
        $data['search_reviews'] = $this->review_model->random_reviews('all', $review_type, $subject_hash, $disputed);
        $data['review_count']['all'] = $this->review_model->count_reviews($review_type, $subject_hash);
        $data['review_count']['positive'] = $this->review_model->count_reviews($review_type, $subject_hash, 0);
        $data['review_count']['disputed'] = $this->review_model->count_reviews($review_type, $subject_hash, 1);

        if ($review_type == 'user') {
            $this->load->model('accounts_model');
            $account = $this->accounts_model->get(array('user_hash' => $subject_hash));
            if ($account !== FALSE)
                $data['name'] = $account['user_name'];
        } else if ($review_type == 'item') {
            $this->load->model('items_model');
            $item = $this->items_model->get($subject_hash);
            if ($item !== FALSE)
                $data['name'] = $item['name'];
        }

        // If the subject/type has some reviews, then load information about it.
        // Even if search_reviews is empty. This is handled by the view.
        if (isset($data['name'])) {
            $data['average'] = $this->review_model->current_rating($review_type, $subject_hash);
            $data['title'] = "Reviews for {$data['name']}";
            $data['page'] = "reviews/view";
        } else {
            $data['page'] = 'reviews/not_found';
            $data['title'] = 'Error';
        }
        $this->_render($data['page'], $data);
    }

    /**
     * Form
     *
     * This page displays the form for leaving feedback.
     *
     * @param        string $auth_token
     * @param        int $order_id
     */
    public function form($auth_token = NULL, $order_id = NULL)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<span class="help-inline">', '</span><br />');
        if ($auth_token == NULL || $order_id == NULL)
            redirect('/');

        $data['action_page'] = "reviews/form/$auth_token/$order_id";
        $data['cancel_page'] = ($this->current_user->user_role == 'Vendor') ? "orders" : "purchases";
        $data['review_state'] = $this->review_auth_model->load_review_state($auth_token, $order_id);
        if ($data['review_state'] == FALSE) {
            $data['page'] = 'reviews/no_review_state';
            $data['title'] = 'Error';
            // Display an error
        } else {

            // Process the form
            $data['page'] = 'reviews/form';
            $data['title'] = 'Review Order #' . $data['review_state']['order_id'];
            $data['review_info'] = $this->review_model->review_information($order_id, $data['review_state']['review_type']);

            $all_reviews = array();

            // Allow access to the buyer_submit_form if the review_state is for a buyer.
            if ($data['review_state']['review_type'] == 'buyer') {
                if ($this->input->post('buyer_submit_review') == 'Submit Review') {
                    // Always need to validate the review_length and vendor data.
                    $this->form_validation->set_rules('review_length', 'Review Length', 'required|check_review_length');
                    $this->form_validation->set_rules('vendor_communication', $data['review_info']['vendor']['user_name'] . "'s communication", 'required|check_valid_rating_choice');
                    $this->form_validation->set_rules('vendor_shipping', 'orders shipping', 'required|check_valid_rating_choice');
                    $this->form_validation->set_rules('vendor_comments_source', 'comments source', 'required|check_review_comments_source');

                    // Determine what rule to apply depending on the comments source, knowing it too will be validated.
                    if ($this->input->post('vendor_comments_source') == 'prepared')
                        $this->form_validation->set_rules('vendor_prepared_comments', 'Vendor Comments', 'required|check_prepared_comments_vendor');
                    // If user wishes to type in their own data?
                    if ($this->input->post('vendor_comments_source') == 'input')
                        $this->form_validation->set_rules('vendor_free_comments', 'Vendor Comments', 'required|max_length[150]|htmlspecialchars');


                    if ($this->form_validation->run() == TRUE) {
                        // Prepare vendor review.
                        $vendor_comments = ($this->input->post('vendor_comments_source') == 'prepared')
                            ? $this->input->post('vendor_prepared_comments')
                            : $this->input->post('vendor_free_comments');

                        $rating_array = array('communication' => $this->input->post('vendor_communication'),
                            'shipping' => $this->input->post('vendor_shipping'));

                        $all_reviews[] = $this->review_model->prepare_review_array('user', $data['review_info']['vendor']['user_hash'], $data['review_info']['disputed'], $rating_array, $vendor_comments);

                        // Process Item Reviews

                        // Short review. Apply same item feedback to each item.
                        if ($this->input->post('review_length') == 'short') {
                            // Set up some rules
                            $this->form_validation->set_rules('short_item_quality', 'Item Quality', 'required|check_valid_rating_choice');
                            $this->form_validation->set_rules('short_item_matches_desc', 'Item Matches Description', 'required|check_valid_rating_choice');
                            $this->form_validation->set_rules('short_item_comments_source', 'Item Comments Source', 'required|check_review_comments_source');

                            // Check comments source, again this will also be validated.
                            // Is the comment prepared:
                            if ($this->input->post('short_item_comments_source') == 'prepared')
                                $this->form_validation->set_rules('short_item_prepared_comments', 'Item Comments', 'check_prepared_comments_item');
                            // Is the comment free-format?
                            if ($this->input->post('short_item_comments_source') == 'input')
                                $this->form_validation->set_rules('short_item_free_comments', 'Item Comments', 'max_length[150]|htmlentities');

                            if ($this->form_validation->run() == TRUE) {
                                // Apply the single item feedback to all items.
                                $comments = ($this->input->post('short_item_comments_source') == 'prepared') ? $this->input->post('short_item_prepared_comments') : $this->input->post('short_item_free_comments');
                                $rating_array = array('quality' => $this->input->post('short_item_quality'),
                                    'matches description' => $this->input->post('short_item_matches_desc'));
                                foreach ($data['review_info']['items'] as $item) {
                                    $all_reviews[] = $this->review_model->prepare_review_array('item', $item['hash'], $data['review_info']['disputed'], $rating_array, $comments);
                                }
                            }
                            // Done for now. Store reviews and redirect.
                        }

                        $full_item_post = $this->input->post("item");

                        // If the review is the long format:
                        if ($this->input->post('review_length') == 'long') {
                            $c = 0;
                            // Loop through each item, and set up form validation rules.
                            foreach ($data['review_info']['items'] as $item) {
                                $this->form_validation->set_rules("item[{$c}][quality]", "item " . ($c + 1) . "'s quality", 'required|check_valid_rating_choice');
                                $this->form_validation->set_rules("item[{$c}][matches_desc]", "item " . ($c + 1) . "'s matches description", 'required|check_valid_rating_choice');
                                $this->form_validation->set_rules("item[{$c}][comments_source]", "item " . ($c + 1) . "'s comments source", "required|check_review_comments_source");

                                $item_post = $full_item_post[$c];
                                // Comments source will determine what rule to apply
                                if (isset($item_post['comments_source'])) {
                                    if ($item_post['comments_source'] == 'input')
                                        $this->form_validation->set_rules("item[{$c}][free_comments]", "item " . ($c + 1) . "'s comments", "max_length[150]|htmlentities");
                                    if ($item_post['comments_source'] == 'prepared')
                                        $this->form_validation->set_rules("item[{$c}][prepared_comments]", "item " . ($c + 1) . "'s comments", "check_item_prepared_comments");
                                }

                                // Execute form validation, if successful store the review for this item.
                                if ($this->form_validation->run() == TRUE) {
                                    $comments = ($item_post['comments_source'] == 'prepared') ? $item_post['prepared_comments'] : $item_post['free_comments'];
                                    $rating_array = array('quality' => $item_post['quality'],
                                        'matches description' => $item_post['matches_desc']);
                                    $all_reviews[] = $this->review_model->prepare_review_array('item', $item['hash'], $data['review_info']['disputed'], $rating_array, $comments);
                                }
                                $c++;
                            }
                        }
                    }

                    // If the generated rules are adhered to, we can proceed to store the reviews.
                    if ($this->form_validation->run() == TRUE) {
                        if ($this->review_model->publish_reviews($all_reviews, 'buyer') == TRUE) {
                            // If published, clear authorization to review this order.
                            $this->review_auth_model->clear_user_auth($data['review_state']['order_id']);
                            $this->current_user->set_return_message('Your feedback for this order has been saved!','success');
                            redirect($data['cancel_page']);
                        } else {
                            $data['returnMessage'] = 'Error publishing reviews, please try again.';
                        }
                    }
                }
            } else if ($data['review_state']['review_type'] == 'vendor') {
                // Allow access to the vendor_submit_form if the review_state is for a vendor.

                if ($this->input->post('vendor_submit_review') == 'Submit Review') {
                    // Do form validation on static buyer review form
                    if ($this->form_validation->run('vendor_submit_review') === TRUE) {

                        //$this->load->library('form_validation', array(), 'comments_validation');
                        ($this->input->post('buyer_comments_source') == 'prepared')
                            ? $this->form_validation->set_rules('buyer_prepared_comments', 'Buyer Comments', 'required|check_prepared_comments_buyer')
                            : $this->form_validation->set_rules('buyer_free_comments', 'Buyer Comments', 'required|htmlspecialchars|max_length[150]');

                        if ($this->form_validation->run() === TRUE) {
                            $comments = ($this->input->post('buyer_comments_source') == 'prepared')
                                ? $this->input->post('buyer_prepared_comments')
                                : $this->input->post('buyer_free_comments');

                            $rating_array = array('communication' => $this->input->post('buyer_communication'),
                                'cooperation' => $this->input->post('buyer_cooperation'));
                            $all_reviews[] = $this->review_model->prepare_review_array('user', $data['review_info']['buyer']['user_hash'], $data['review_info']['disputed'], $rating_array, $comments);

                            if ($this->review_model->publish_reviews($all_reviews, 'vendor') == TRUE) {
                                $this->review_auth_model->clear_user_auth($data['review_state']['order_id']);
                                $this->current_user->set_return_message('Your feedback for this user has been saved!','success');
                                redirect($data['cancel_page']);
                            } else {
                                $data['returnMessage'] = 'Error publishing review, please try again.';
                            }
                        }
                    }
                }
            }
        }

        $this->_render($data['page'], $data);
    }

};


/* End of File: Reviews.php */
/* Location: application/controllers/Reviews.php */
