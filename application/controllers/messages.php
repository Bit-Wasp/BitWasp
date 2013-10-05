<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
	
		$this->load->model('messages_model');
		$this->load->library('bw_messages');
		$this->load->library('openssl');
	
		// Automatically check if a PIN is required.
		if($this->bw_config->encrypt_private_messages == TRUE){
			if($this->current_user->message_password == NULL && uri_string() !== 'message/pin')
				redirect('message/pin');
		}			
	}
	 
	// Read a message.
	public function read($hash) {
		$data['page'] = 'messages/read';

		// Load the message
		$message = $this->messages_model->get($hash);
		if($message == FALSE)
			redirect('inbox');
		
		$message = $this->bw_messages->prepare_output(array($message));
		$data['message'] = $message[0];
		$data['title'] = $data['message']['subject'];
		
		$this->load->library('Layout', $data);
		
		// Mark message 'read' if it's currently unread.
		if($data['message']['viewed'] == '0')
			$this->messages_model->set_viewed($data['message']['id']);
		
		// If 'remove on read' is set, delete the message now that it's been displayed.
		if($data['message']['remove_on_read'] == '1')
			$this->messages_model->delete($data['message']['id']);
	}
		
	// Display a users messages.
	public function inbox() {
		$messages = $this->messages_model->inbox();
		$data['messages'] = $this->bw_messages->prepare_output($messages);	
		$data['page'] = 'messages/inbox';
		$data['title'] = 'Inbox';
		$this->load->library('Layout',$data);
	}
	
	// Delete a specific message, or all of them.
	public function delete($hash) {	
		if($hash == 'all') {
			if($this->messages_model->delete_all() == TRUE) {
				$this->session->set_flashdata('msgs_delete', 'true');
				redirect('message/deleted');
			} else {
				$data['returnMessage'] = 'Error deleting messages, try again later.';
			}
		} else {		
			$get = $this->messages_model->get($hash);
			if($get !== FALSE){
				if($this->messages_model->delete($get['id']) == TRUE){
					$this->session->set_flashdata('msg_delete', 'true');
					redirect('message/deleted');
				} else {
					$data['returnMessage'] = 'Error deleting message, try again later.';
				}	
			} else {
				redirect('inbox');
			}
		}
		
		$data['title'] = 'Inbox';
		$data['page'] = 'messages/inbox';
		$messages = $this->messages_model->inbox();
		$data['messages'] = $this->bw_messages->prepare_output($messages);
		
		$this->load->library('Layout', $data);
	}
	
	// Page to handle deleted messages, to avoid users refreshing a URI's.
	public function deleted() { 	
		
		$data['title'] = 'Inbox';
		$data['page'] = 'messages/inbox';	
		$messages = $this->messages_model->inbox();
		$data['messages'] = $this->bw_messages->prepare_output($messages);

		if($this->session->flashdata('msg_delete') == TRUE) {
			$data['returnMessage'] = 'Message has been deleted';
		} else if($this->session->flashdata('msgs_delete') == TRUE){
			$data['returnMessage'] = 'All messages have been deleted.';
		} else {
			redirect('inbox');
		}

		$this->load->library('Layout', $data);
	}  
	
	// Send a message.
	public function send($identifier = NULL){
		
		$this->load->library('form_validation');
	
		$data['to_name'] = '';
		$data['subject'] = '';
		$data['action_uri'] = uri_string();
		$data['public_key'] = '';
	
		$reply_info = $this->messages_model->reply_info($identifier);
		
		if($reply_info == NULL && $identifier !== NULL)
			redirect('message/send');
		
		if(is_array($reply_info)){
			$data['to_name'] = $reply_info['to_name'];
			
			$data['subject'] = $reply_info['subject'];
			
			if(isset($reply_info['public_key'])){
				$data['public_key'] = $reply_info['public_key'];
				$data['fingerprint'] = $reply_info['fingerprint'];
				$data['fingerprint_f'] = $reply_info['fingerprint_f'];
			}
		} 	
		
		if($data['public_key'] !== ''){
			// Include the required files for client side encryption
			$data['header_meta'] = $this->load->view('messages/encryption_header', NULL, true);
			$data['returnMessage'] = 'This message will be encrypted automatically if you have javascript enabled.<br />';
		}
	
		if ($this->form_validation->run('send_message') == TRUE) {
			$data['from'] = $this->current_user->user_id;
			$message = $this->bw_messages->prepare_input($data);
			if($this->messages_model->send($message)){
				$this->session->set_flashdata('msg_sent','true');
				redirect('message/sent');
			} 
		}
			
		$data['page'] = 'messages/send';
		$data['title'] = 'Send Message';
			
		$this->load->library('Layout', $data);	
	}

	// Catcher page for when a message is sent.
	public function sent() { 
		$messages = $this->messages_model->inbox();
		$data['messages'] = $this->bw_messages->prepare_output($messages);
		$data['title'] = 'Inbox';
		$data['page'] = 'messages/inbox';

		if($this->session->flashdata('msg_sent') == TRUE){
			$data['returnMessage'] = 'Message has been sent';
		} else {
			redirect('inbox');
		}	
		$this->load->library('Layout', $data);
	}

	// Prompt for a users pin if messages are encrypted.
	public function enter_pin(){	
		$this->load->model('users_model');
		$this->load->library('form_validation');

		$this->load->helper(array('form'));
	
		if ($this->form_validation->run('message_pin_form') == FALSE){
			$data['title'] = 'Message PIN';
			$data['page'] = 'messages/pin';
		} else {
			// Load the users salt, public key, and private key.
			$user = $this->users_model->message_data(array('user_hash' => $this->current_user->user_hash));
			$message_password = $this->general->hash($this->input->post('pin'),$user['salt']);
		
			// Encrypt with public key, attempt to decrypt with private key & password.
			$solution = $this->general->generate_salt();
			$challenge = $this->openssl->encrypt($solution, $user['public_key']);
			$answer = $this->openssl->decrypt($challenge, $user['private_key'], $message_password);
			
			if($answer == $solution){
				$this->current_user->set_message_password($message_password);
				unset($message_password);
				redirect('inbox');
			} else {
				$data['title'] = 'Message PIN';
				$data['page'] = 'messages/pin';
				$data['returnMessage'] = 'The PIN you entered was incorrect. Please try again';
			}
		}
		$this->load->library('Layout',$data);
	}
			   
	// Callback functions for form validation.
	public function check_delete_on_read($param) {
		if($this->general->matches_any($param, array(NULL,'1')))
			return TRUE;
			
		return FALSE;
	}
	
	public function user_exists($param) {
		$this->load->model('users_model');
		if($this->users_model->get(array('user_name' => $param)) !== FALSE)
			return TRUE;
			
		return FALSE;
	}
		
};
/* End of file Messages.php */
