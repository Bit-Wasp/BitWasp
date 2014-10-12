<?php

/**
 * Activate Controller
 *
 * This controller handles email verification - allows users to visit
 * a link, or submit a token manually to verify their email.
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Activate
 * @author        BitWasp
 *
 */
class Activate extends MY_Controller
{
    protected $activation_return_message;
    protected $change_email_return_message;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    /**
     * Email
     *
     * This page is only for users activating their email for the first time.
     *
     * Users visiting the page via the link sent by email will be redirected if
     * successfully confirmed or already verified. If visiting the link fails, it
     * shows same form as displayed when they didn't provide tokens.
     *
     * @param mixed $activation_id
     * @param mixed $activation_hash
     */
    public function email($activation_id = null, $activation_hash = null)
    {
        if ($this->current_user->logged_in()) redirect('');

        $this->load->model('users_model');

        if ($activation_id == null OR $activation_hash == null) {
            if ($this->input->post('submit_email_activation') AND $this->form_validation->run('submit_email_activation') == TRUE) {
                // Set return message containing error if we're not redirected.
                $this->_handle_email_activation('email_address', $this->input->post('email_address'), $this->input->post('activation_hash'));
                $data['returnMessage'] = $this->activation_return_message;
            }
        } else {
            // User clicked link in email
            $this->_handle_email_activation('activation_id', $activation_id, $activation_hash);
            $data['returnMessage'] = $this->activation_return_message;
        }

        // Load the form for user input
        $data['page'] = 'activate/email_form';
        $data['title'] = 'Verify Email';
        $this->_render($data['page'], $data);
    }

    /**
     * Handle Email Activation
     *
     * This function is called with the credentials the user has supplied:
     *
     *  $identifier sets which database field the user is identifying themselves with.
     *   'email_address' if the source of the input is the form, or 'activation_id'
     *   if it's the link.
     *  $activation_id - Either the email address or activation_id, based on $identifier.
     *  $activation_hash - Challenge user must supply to verify account.
     *
     * If account is activated, redirect to the login page and display a message.
     * Otherwise, set $this->activation_return_message, which will be shown to the user.
     *
     * @param $identifier
     * @param $activation_id
     * @param $activation_hash
     * @return bool
     */
    protected function _handle_email_activation($identifier, $activation_id, $activation_hash)
    {
        $attempt = $this->users_model->attempt_email_activation($identifier, $activation_id, $activation_hash);

        if ($attempt === FALSE) {
            // Verification failed
            $this->activation_return_message = ($identifier == 'email_address')
                ? 'The details you entered were incorrect. Please try again, or request the email again'
                : 'The verification link was incorrect. You can request another, or try pasting the verification token below:';
            return FALSE;
        } else {
            // Otherwise, user is verified. Determine message
            $this->current_user->set_return_message(
                (($attempt === 'activated') ? 'Your email has already been verified' : 'Your email has been verified, please log in below!')
                , (($attempt === 'activated') ? 'warning' : 'success'));
            redirect('login');
        }
    }

    /**
     * Change Email
     *
     * This page is only used when users wish to alter their email.
     *
     * Similar to above, a challenge is emailed to the user which they supply by
     * clicking the link or manually pasting it onto the page.
     *
     * @param mixed $activation_id
     * @param mixed $activation_hash
     */
    public function change_email($activation_id = null, $activation_hash = null)
    {
        $this->load->model('email_update_model');
        $this->load->model('users_model');

        if ($activation_id == null OR $activation_hash == null) {
            if ($this->input->post('submit_email_activation') AND $this->form_validation->run('submit_email_activation') == TRUE) {
                // Set return message containing error if we're not redirected.
                $this->_handle_email_change('email_address', $this->input->post('email_address'), $this->input->post('activation_hash'));
                $data['returnMessage'] = $this->change_email_return_message;
            }
        } else {
            // User clicked link in email
            $this->_handle_email_change('activation_id', $activation_id, $activation_hash);
            $data['returnMessage'] = $this->change_email_return_message;
        }

        // Load the form for user input
        $data['page'] = 'activate/change_email_form';
        $data['title'] = 'Verify Email';
        $this->_render($data['page'], $data);
    }

    /**
     * Handle Email Change
     *
     * This function is called with the credentials the user has supplied:
     *
     *  $identifier sets which database field the user is identifying themselves with.
     *   'email_address' if the source of the input is the form, or 'activation_id'
     *   if it's the link.
     *  $activation_id - Either the email address or activation_id, based on $identifier.
     *  $activation_hash - Challenge user must supply to verify account.
     *
     * If account is activated, redirect to the login page and display a message.
     * Otherwise, set $this->activation_return_message, which will be shown to the user.
     *
     * @param $identifier
     * @param $activation_id
     * @param $activation_hash
     * @return bool
     */
    protected function _handle_email_change($identifier, $activation_id, $activation_hash)
    {
        $attempt = $this->email_update_model->attempt_email_activation($identifier, $activation_id, $activation_hash);

        if ($attempt === FALSE) {
            // Verification failed
            $this->change_email_return_message = ($identifier == 'email_address')
                ? 'The details you entered were incorrect. Please try again, or request the email again'
                : 'The verification link was incorrect. You can request another, or try pasting the verification token below:';
            return FALSE;
        } else {
            if ($this->current_user->logged_in()) {
                // User is logged in, different message and redir destination
                $this->current_user->set_return_message(
                    (($attempt === 'activated') ? 'Your email has already been verified' : 'Your email has been verified.')
                    , FALSE);

                redirect('account');
            } else {
                // Inform user of outcome, redirect to login.
                $this->current_user->set_return_message(
                    (($attempt === 'activated') ? 'Your email has already been verified' : 'Your email has been verified, please log in below!')
                    , FALSE);

                redirect('login');
            }
        }
    }

}

;

/* End of file: Activate.php */
/* Location; application/controllers/Activate.php */
