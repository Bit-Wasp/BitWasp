<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use BitWasp\BitcoinLib\BitcoinLib;

/**
 * Administration Panel Controller
 *
 * @package        BitWasp
 * @subpackage    Controllers
 * @category    Admin
 * @author        BitWasp
 */
class Admin extends MY_Controller
{

    /**
     * Nav
     *
     * Stores the admin navigation bar
     */
    public $nav;

    /**
     * Constructor
     *
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('admin_model');

        // Define information for the navigation panel.
        $this->nav = array('' => array('panel' => '',
            'title' => 'General',
            'heading' => 'Admin Panel'),
            'bitcoin' => array('panel' => '/bitcoin',
                'title' => $this->bw_config->currencies[0]['name'],
                'heading' => $this->bw_config->currencies[0]['name'] . ' Panel'),
            'items' => array('panel' => '/items',
                'title' => 'Items',
                'heading' => 'Items Panel'),
            'users' => array('panel' => '/users',
                'title' => 'Users',
                'heading' => 'User Panel'),
            'autorun' => array('panel' => '/autorun',
                'title' => 'Autorun',
                'heading' => 'Autorun Panel'),
            'logs' => array('panel' => '/logs',
                'title' => 'Logs',
                'heading' => 'Logs Panel')
        );

    }

    /**
     * Load the General Information Panel.
     * URI: /admin
     *
     * Load general info about the site, like OpenSSL version, GPG version,
     * and other general site settings from the database. Has Admin Nav Bar.
     *
     * @see    Libraries/GPG
     * @see    Libraries/Bw_Config
     * @return    void
     */
    public function index()
    {
        $this->load->library('gpg');
        if ($this->gpg->have_GPG == TRUE)
            $data['gpg'] = 'gnupg-' . $this->gpg->version;
        $data['openssl'] = OPENSSL_VERSION_TEXT;
        $data['config'] = $this->bw_config->load_admin('');
        $data['encrypt_private_messages'] = $this->bw_config->encrypt_private_messages;

        $data['page'] = 'admin/index';
        $data['title'] = $this->nav['']['heading'];
        $data['nav'] = $this->generate_nav();
        $this->_render($data['page'], $data);
    }

    /**
     * Generate Nav
     *
     * Generates the navigation bar for the admin panel. Will display
     * an alert if the electrum backup option is set but no MPK supplied.
     * This will pave the way for further alerts to be shown to admins.
     *
     * @return    string
     */
    public function generate_nav()
    {
        $nav = '';
        if ($this->bw_config->bip32_mpk == '')
            $nav .= '<div class="alert alert-danger">You have not configured an electrum master public key. Please do so now ' . anchor('admin/edit/bitcoin', 'here') . '.</div>';

        $links = '';
        foreach ($this->nav as $entry) {
            $links .= '<li';
            if (uri_string() == 'admin' . $entry['panel'] || uri_string() == 'admin/edit' . $entry['panel']) {
                $links .= ' class="active" ';
                $self = $entry;
                $heading = $entry['heading'];
                $panel_url = $self['panel'];
            }
            $links .= '>' . anchor('admin' . $entry['panel'], $entry['title']) . '</li>';
        }

        $nav .= '
        <div class="row">
            <ul class="nav nav-tabs">
                <li class="col-xs-3"><h4>' . $self['heading'] . '</h4></li>
                <li class="col-xs-1">';

        if ($panel_url !== '/logs') $nav .= " " . anchor('admin/edit' . $panel_url, 'Edit', '');

        $nav .= '
                </li>
                <li class="col-xs-8">' . $links . '</li>
            </ul>
        </div>
        <div class="row">&nbsp;</div>';

        return $nav;
    }

    /**
     * Edit General Settings.
     * URI: /admin/edit
     *
     * Compare the POSTed fields with those on record. If there's any
     * difference, this will be submitted to the database. If it's the same
     * the variable is set to NULL, and filtered using array_filter.
     * Changes are commited and the user is redirected to the info panel.
     * Has Admin Nav Bar.
     *
     * @see        Libraries/GPG
     * @see        Libraries/Form_Validation
     * @see        Libraries/Bw_Config
     * @return    void
     */
    public function edit_general()
    {
        $data['config'] = $this->bw_config->load_admin('');

        if ($this->form_validation->run('admin_edit_') == TRUE) {
            // Determine which settings have changed. Filter unchanged.
            $changes = array();
            $changes['global_proxy_type'] = ($this->input->post('global_proxy_type') != $data['config']['global_proxy_type']) ? $this->input->post('global_proxy_type') : NULL;
            $changes['global_proxy_url'] = ($this->input->post('global_proxy_url') != $data['config']['global_proxy_url']) ? $this->input->post('global_proxy_url') : NULL;
            $changes['site_description'] = ($this->input->post('site_description') != $data['config']['site_description']) ? $this->input->post('site_description') : NULL;
            $changes['site_title'] = ($this->input->post('site_title') != $data['config']['site_title']) ? $this->input->post('site_title') : NULL;
            $changes['openssl_keysize'] = ($this->input->post('openssl_keysize') != $data['config']['openssl_keysize']) ? $this->input->post('openssl_keysize') : NULL;
            $changes['allow_guests'] = ($this->input->post('allow_guests') != $data['config']['allow_guests']) ? $this->input->post('allow_guests') : NULL;
            $changes = array_filter($changes, 'strlen');

            // If the global proxy is disabled, unset the type and url.
            if ($this->input->post('global_proxy_disabled') == '1') {
                if ($data['config']['global_proxy_type'] != '' || $data['config']['global_proxy_url'] != '') {
                    $changes['global_proxy_type'] = 'Disabled';
                    $changes['global_proxy_url'] = '';
                }
            } else {
                // Otherwise, if either the proxy type or url is changing,
                // then issue an override to the curl library and make
                // a test request. If this fails, prevent the update and
                // display an error.
                if (isset($changes['global_proxy_type']) || isset($changes['global_proxy_url'])) {
                    $override['proxy_type'] = (isset($changes['global_proxy_type'])) ? $changes['global_proxy_type'] : $data['config']['global_proxy_type'];
                    $override['proxy_url'] = (isset($changes['global_proxy_url'])) ? $changes['global_proxy_url'] : $data['config']['global_proxy_url'];
                    $this->load->library('bw_curl', $override);
                    $test = $this->bw_curl->get_request('https://duckduckgo.com');
                    if ($test == FALSE) {
                        unset($changes);
                        $data['proxy_error'] = 'Your proxy settings are incorrect, please check your entries.';
                    }
                }
            }

            if (isset($changes)) {
                if (count($changes) > 0 && $this->config_model->update($changes) == TRUE) {
                    $log = $this->admin_model->format_config_changes($changes);
                    $this->logs_model->add('Admin: General Panel', 'General site configuration updated', 'The general configuration of the site has been updated:<br />' . $log, 'Info');
                    $message = 'Your changes have been saved.';
                    $class = 'success';
                } else {
                    $message = 'No changes were made.';
                    $class = 'warning';
                }
                $this->current_user->set_return_message($message, $class);
                redirect('admin/edit');
            }
        }

        $data['page'] = 'admin/edit_';
        $data['title'] = $this->nav['']['heading'];
        $data['nav'] = $this->generate_nav();
        $this->_render($data['page'], $data);
    }

    /**
     * Load the Logs Information Panel.
     * URI: /admin/autorun
     *
     * User is shown the amount of transcations, order's, and messages on
     * record.
     *
     * @see        Libraries/Bw_Config
     * @see        Models/Autorun_Model
     * @return    void
     */
    public function autorun()
    {
        $this->load->model('autorun_model');
        $data['page'] = 'admin/autorun';
        $data['autorum_cmd'] = '*/1 * * * * php ' . __DIR__ . '/index.php callback autorun';
        $data['title'] = $this->nav['autorun']['heading'];
        $data['jobs'] = $this->autorun_model->load_all();
        $data['config'] = $this->bw_config->load_admin('autorun');
        $data['nav'] = $this->generate_nav();

        if ($this->form_validation->run('admin_edit_autorun') == TRUE) {
            $this->load->library('autorun', FALSE);

            // Load the POST array of jobs, and the specified intervals.
            $jobs = $this->input->post('jobs');
            $update = FALSE;

            // Load the array of disabled jobs.
            $disabled_jobs = $this->input->post('disabled_jobs');

            foreach ($jobs as $index => $interval) {
                // Intervals should always be numeric.
                if (!is_numeric($interval))
                    redirect('admin/autorun');

                // Set the interval to zero if a job is disabled.
                if ($data['jobs'][$index] !== '0' && (isset($disabled_jobs[$index]) && $disabled_jobs[$index] == '1')) {
                    if ($this->autorun_model->set_interval($index, '0') == TRUE) {
                        $update = TRUE;
                    }
                } else {
                    // If the job exists, and the interval has changed..
                    if (isset($data['jobs'][$index]) && $data['jobs'][$index]['interval'] !== $interval) {
                        // Update the interval.
                        if ($this->autorun_model->set_interval($index, $interval) == TRUE) {
                            $update = TRUE;

                        }

                        // If the interval has changed, rerun the job??
                        if ($interval !== '0') {
                            $this->autorun->jobs[$index]->job();
                        }
                    }
                }
            }

            // If the update happened successfully, redirect!
            if ($update) {
                $this->current_user->set_return_message('Your changes have been saved', 'success');
                redirect('admin/autorun');
            }
        }

        $this->_render('admin/autorun', $data);
    }

    /**
     * Load the Bitcoin Information Panel.
     * URI: /admin/bitcoin
     *
     * This panel displays information about the accounts in the bitcoin
     * wallet, the number of transactions processed to date, the source
     * of the bitcoin exchange rates, and the latest block.
     * Has Admin Nav Bar.
     *
     * @see        Libraries/Bw_Bitcoin
     * @see        Models/Bitcoin_Model
     * @return    void
     */
    public function bitcoin()
    {
        $this->load->library('bw_bitcoin');
        $this->load->model('bitcoin_model');
        $this->load->model('transaction_cache_model');

        $data['page'] = 'admin/bitcoin';
        $data['title'] = $this->nav['bitcoin']['heading'];
        $data['nav'] = $this->generate_nav();
        $data['config'] = $this->bw_config->load_admin('bitcoin');
        $data['bitcoin_index'] = $this->bw_config->price_index;
        $data['bitcoin_info'] = $this->bw_bitcoin->getinfo();
        $data['key_usage_count'] = $this->bitcoin_model->count_key_usage();
        $data['block_cache'] = $this->transaction_cache_model->count_cache_list();
        $this->_render($data['page'], $data);
    }

    /**
     * Edit the Bitcoin Settings.
     * URI: /admin/edit/bitcoin
     *
     * If the user submitted the Price Index form, we check for updates.
     * If the source specified exists, then update the config setting.
     * + If the source was previously disabled, re-setup the periodic updates.
     * + Trigger a new update from the new price index.
     * If the source is set to disabled, then disable the periodic updates.
     * by setting the interval to zero.
     *
     * If the user submitted the form to transfer coins, check that the
     * sending account has sufficient balance. If so, transfer coins, and
     * redirect to Bitcoin Information Page. If not, display an error.
     * Has Admin Nav Bar.
     *
     * @see        Libraries/Bw_Bitcoin
     * @see        Libraries/Bw_Config
     * @see        Libraries/Form_Validation
     * @return    void
     */
    public function edit_bitcoin()
    {
        $this->load->library('bw_bitcoin');
        $this->load->model('bitcoin_model');
        $this->load->model('autorun_model');

        $data['config'] = $this->bw_config->load_admin('bitcoin');
        $data['price_index'] = $this->bw_config->price_index;

        // If the Settings form was submitted:
        if ($this->input->post('submit_edit_bitcoin') == 'Update') {
                if ($this->form_validation->run('admin_edit_bitcoin') == TRUE) {
                $changes = array();
                // Check if the selection exists.
                if ($data['price_index'] != $this->input->post('price_index')) {
                    if (is_array($data['config']['price_index_config'][$this->input->post('price_index')])
                        OR $this->input->post('price_index') == 'Disabled'
                    ) {

                        $update = array('price_index' => $this->input->post('price_index'));
                        $this->config_model->update($update);

                        if ($this->input->post('price_index') !== 'Disabled') {
                            // If the price index was previously disabled, set the auto-run script interval back up..
                            if ($data['price_index'] == 'Disabled')
                                $this->autorun_model->set_interval('price_index', '15');

                            // And request new exchange rates.
                            $this->bw_bitcoin->ratenotify();
                        } else {
                            // When disabling BPI updates, set the interval to 0.
                            $this->autorun_model->set_interval('price_index', '0');
                        }
                        // Redirect when complete.
                        redirect('admin/bitcoin');
                    }
                }

                if ($data['config']['bip32_iteration'] !== $this->input->post('bip32_iteration'))
                    $changes['bip32_iteration'] = $this->input->post('bip32_iteration');

                $changes = array_filter($changes, 'strlen');

                // Since electrum mpk may be empty, do this after strlen filter
                if ($data['config']['bip32_mpk'] !== $this->input->post('bip32_mpk'))
                    $changes['bip32_mpk'] = $this->input->post('bip32_mpk');

                if (count($changes) > 0 && $this->config_model->update($changes) == TRUE) {
                    $log = $this->admin_model->format_config_changes($changes);
                    $this->logs_model->add("Admin: {$this->bw_config->currencies[0]['name']} Panel", $this->bw_config->currencies[0]['name'] . ' configuration updated', 'The ' . $this->bw_config->currencies[0]['name'] . ' configuration of the site has been updated:<br />' . $log, 'Info');
                    $message = 'Your changes were saved.';
                    $class = 'success';
                } else {
                    $message = 'No changes were made.';
                    $class = 'warning';
                }
                $this->current_user->set_return_message($message, $class);

                redirect('admin/bitcoin');
            }
        }

        $data['page'] = 'admin/edit_bitcoin';
        $data['title'] = $this->nav['bitcoin']['heading'];
        $data['nav'] = $this->generate_nav();
        $this->_render($data['page'], $data);
    }

    /**
     * Load the Users Information Panel.
     * URI: /admin/users
     *
     * Display user count, and global user configuration settings like
     * session timeout, the captcha length, whether users can register on
     * the register form, whether vendors may register on the site, whether
     * PM's are encrypted using RSA, whether vendors should be forced to
     * have a PGP associated with their account, or how long it takes before
     * banning a user due to inactivity.
     *
     * @see        Libraries/Bw_Config
     * @see        Libraries/Form_Validation
     * @return    void
     */
    public function users()
    {
        $data['nav'] = $this->generate_nav();
        $data['user_count'] = $this->general_model->count_entries('users');
        $data['config'] = $this->bw_config->load_admin('users');
        $data['page'] = 'admin/users';
        $data['title'] = $this->nav['users']['heading'];
        $this->_render($data['page'], $data);
    }

    /**
     * Edit the User Settings.
     * URI: /admin/edit/users
     *
     * Alter the user settings. Work out which fields are different, and
     * set the corresponding $changes[] entry to the POST fields. Unchanged
     * entries are set to NULL and filtered. Changes are saved and the user
     * redirected back to the User Info Page. Has Admin Nav Bar.
     *
     * @see        Libraries/Bw_Bitcoin
     * @see        Libraries/Bw_Config
     * @see        Libraries/Form_Validation
     * @return    void
     */
    public function edit_users()
    {
        $this->load->library('form_validation');
        $data['nav'] = $this->generate_nav();
        $data['config'] = $this->bw_config->load_admin('users');

        if ($this->form_validation->run('admin_edit_users') == TRUE) {
            // Determine what changes, if any, to make.
            $changes['login_timeout'] = ($this->input->post('login_timeout') != $data['config']['login_timeout']) ? $this->input->post('login_timeout') : NULL;
            $changes['captcha_length'] = ($this->input->post('captcha_length') != $data['config']['captcha_length']) ? $this->input->post('captcha_length') : NULL;
            $changes['request_emails'] = ($this->input->post('request_emails') != $data['config']['request_emails']) ? $this->input->post('request_emails') : NULL;
            $changes['registration_allowed'] = ($this->input->post('registration_allowed') != $data['config']['registration_allowed']) ? $this->input->post('registration_allowed') : NULL;
            $changes['vendor_registration_allowed'] = ($this->input->post('vendor_registration_allowed') != $data['config']['vendor_registration_allowed']) ? $this->input->post('vendor_registration_allowed') : NULL;
            $changes['encrypt_private_messages'] = ($this->input->post('encrypt_private_messages') != $data['config']['encrypt_private_messages']) ? $this->input->post('encrypt_private_messages') : NULL;
            $changes['force_vendor_pgp'] = ($this->input->post('force_vendor_pgp') != $data['config']['force_vendor_pgp']) ? $this->input->post('force_vendor_pgp') : NULL;
            $changes = array_filter($changes, 'strlen');

            // If being set set to disabled, and payment amount > 0, hard set  to zero.
            // Otherwise allow amounts to be updated:
            // - Set payment amount to zero if (i) checkbox is checked and config amount not already zero,
            //   or (ii) admin hasn't checked disabled, but set post amount to 0.
            // - Otherwise, if the payment amount differs from what we have, create the change.
            if ($this->input->post('entry_payment_buyer_disabled') == '1' AND $this->bw_config->entry_payment_buyer !== '0'
                OR $this->input->post('entry_payment_buyer_disabled') == null AND $this->input->post('entry_payment_buyer') == '0'
            ) {
                echo 'a';
                $changes['entry_payment_buyer'] = '0';
            } else {
                if ($this->input->post('entry_payment_buyer_disabled') == null AND $this->input->post('entry_payment_buyer') != $data['config']['entry_payment_buyer']) {
                    echo 'b';
                    $changes['entry_payment_buyer'] = $this->input->post('entry_payment_buyer');
                }
            }
            // Same for vendor amount
            if ($this->input->post('entry_payment_vendor_disabled') == '1' AND $this->bw_config->entry_payment_vendor !== '0'
                OR $this->input->post('entry_payment_vendor_disabled') == null AND $this->input->post('entry_payment_vendor') == '0'
            ) {
                echo 'c';
                $changes['entry_payment_vendor'] = '0';
            } else {
                if ($this->input->post('entry_payment_vendor_disabled') == null AND $this->input->post('entry_payment_vendor') != $data['config']['entry_payment_vendor']) {
                    echo 'd';
                    $changes['entry_payment_vendor'] = $this->input->post('entry_payment_vendor');
                }
            }

            if (count($changes) > 0 && $this->config_model->update($changes) == TRUE) {
                $log = $this->admin_model->format_config_changes($changes);
                $this->logs_model->add('Admin: Users Panel', 'Users configuration updated', 'The users configuration of the site has been updated:<br />' . $log, 'Info');


                $this->current_user->set_return_message('Your changes have been saved', 'success');

            } else {
                $this->current_user->set_return_message('No changes were made', 'warning');
            }

            redirect('admin/users');
        }

        $data['config'] = $this->bw_config->load_admin('users');
        $data['page'] = 'admin/edit_users';
        $data['title'] = $this->nav['users']['heading'];
        $this->_render($data['page'], $data);
    }

    /**
     * Load the Items Information Panel.
     * URI: /admin/items
     *
     * @see        Libraries/Bw_Bitcoin
     * @see        Libraries/Bw_Config
     * @see        Models/Categories_Model
     * @return    void
     */
    public function items()
    {
        $this->load->model('categories_model');
        $data['nav'] = $this->generate_nav();
        $data['item_count'] = $this->general_model->count_entries('items');
        $data['config'] = $this->bw_config->load_admin('items');
        $data['categories'] = $this->categories_model->list_all();
        $data['trusted_user'] = array('order_count' => $this->bw_config->trusted_user_order_count,
            'rating' => $this->bw_config->trusted_user_rating,
            'review_count' => $this->bw_config->trusted_user_review_count);
        $data['page'] = 'admin/items';
        $data['title'] = $this->nav['items']['heading'];
        $this->_render($data['page'], $data);
    }

    /**
     * Edit the Items Settings.
     * URI: /admin/edit/items
     *
     * Edit Item settings. Mainly just add/rename/delete categories.
     *
     * @see        Models/Categories_Model
     * @see        Libraries/Bw_Config
     * @see        Libraries/Form_Validation
     * @return    void
     */
    public function edit_items()
    {
        $this->load->library('form_validation');
        $this->load->model('categories_model');
        $data['nav'] = $this->generate_nav();
        $data['categories_add_select'] = $this->categories_model->generate_select_list('category_parent', 'form-control', FALSE, array('root' => TRUE));
        $data['categories_rename_select'] = $this->categories_model->generate_select_list('rename_id', 'form-control');
        $data['categories_delete_select'] = $this->categories_model->generate_select_list('delete_id', 'form-control');
        $data['config'] = $this->bw_config->load_admin('items');

        // If the Add Category form has been submitted:
        if ($this->input->post('add_category') == 'Add') {
            if ($this->form_validation->run('admin_add_category') == TRUE) {
                // Add the category.
                $category = array('name' => $this->input->post('create_name'),
                    'hash' => $this->general->unique_hash('categories', 'hash'),
                    'parent_id' => $this->input->post('category_parent'));
                if ($this->categories_model->add($category) == TRUE) {
                    $this->current_user->set_return_message('Your category has been saved.', 'success');
                    redirect('admin/edit/items');
                } else {
                    $data['returnMessage'] = 'Error saving category!';
                }
            }
        }

        // If the Rename Category form has been submitted:
        if ($this->input->post('rename_category') == 'Rename') {
            if ($this->form_validation->run('admin_rename_category') == TRUE) {
                // Rename the category.
                if ($this->categories_model->rename($this->input->post('rename_id'), $this->input->post('category_name')) == TRUE) {
                    $this->current_user->set_return_message('Your category has been renamed.', 'success');
                    redirect('admin/edit/items');
                }
            }
        }

        // If the Delete Category form has been submitted:
        if ($this->input->post('delete_category') == 'Delete') {
            if ($this->form_validation->run('admin_delete_category') == TRUE) {
                $category = $this->bw_config->categories[$this->input->post('delete_id')];

                // Check if items or categories are orphaned by this action, redirect to move these.
                if ($category['count_child_items'] > 0 OR $category['count_child_cats'] > 0) {
                    redirect('admin/category/orphans/' . $category['hash']);
                } else {
                    // Otherwise it's empty and can be deleted.
                    if ($this->categories_model->delete($category['id']) == TRUE) {
                        $this->current_user->set_return_message("The chosen category has been deleted.", 'success');
                        redirect('admin/edit/items');
                    }
                }
            }
        }
        $data['page'] = 'admin/edit_items';
        $data['title'] = $this->nav['items']['heading'];
        $this->_render($data['page'], $data);
    }

    /**
     * Trusted User
     * URI: admin/trusted_user
     *
     * This page allows the admin to configure what defines a trusted user.
     * Trusted users are allowed to request up-front payment when a
     * particular item is ordered.
     */
    public function trusted_user()
    {
        $data['config'] = $this->bw_config->load_admin('items');

        if ($this->input->post('trusted_user_update') == 'Update') {
            if ($this->form_validation->run('admin_trusted_user_update') == TRUE) {
                $changes = array();
                $changes['trusted_user_rating'] = ($data['config']['trusted_user_rating'] != $this->input->post('trusted_user_rating')) ? $this->input->post('trusted_user_rating') : NULL;
                $changes['trusted_user_review_count'] = ($data['config']['trusted_user_review_count'] != $this->input->post('trusted_user_review_count')) ? $this->input->post('trusted_user_review_count') : NULL;
                $changes['trusted_user_order_count'] = ($data['config']['trusted_user_order_count'] != $this->input->post('trusted_user_order_count')) ? $this->input->post('trusted_user_order_count') : NULL;
                $changes = array_filter($changes, 'strlen');

                if ((count($changes) > 0 AND $this->config_model->update($changes) == TRUE)) {
                    $this->current_user->set_return_message('Your changes have been saved', 'success');
                } else {
                    $this->current_user->set_return_message('No changes were made to the settings.', 'warning');
                }

                redirect('admin/items');
            }
        }
        $data['page'] = 'admin/trusted_user';
        $data['title'] = 'Trusted User Settings';
        $this->_render($data['page'], $data);
    }

    /**
     * Logs
     *
     * Either displays a list of logs (when $record = NULL) or a specific
     * log record.
     *
     * @param    string $record
     */
    public function logs($record = NULL)
    {

        if ($record == NULL) {
            $data['nav'] = $this->generate_nav();
            $data['page'] = 'admin/logs_list';
            $data['title'] = 'Logs';
            $data['logs'] = $this->logs_model->fetch();

        } else {
            // If the record doesn't exist, redirect to the list.
            $data['log'] = $this->logs_model->fetch($record);
            if ($data['log'] == FALSE)
                redirect('admin/logs');

            $data['page'] = 'admin/log';
            $data['title'] = "Log Record: {$data['log']['id']}";
        }
        $this->_render($data['page'], $data);
    }

    /**
     * Fix orphan categories/items.
     * URI: /admin/category/orphans/$hash
     *
     * If a category is to be deleted, where the result would orphan
     * any items or categories, they need to be looked after. Calculate
     * what we have to say to the user. If there's nothing to do for this
     * category then redirect away from this form.
     *
     * If the form is submitted correctly, then update the records.
     * Finally, if the category is successfully removed, return TRUE,
     * otherwise return FALSE on failure.
     *
     * @param        string $hash
     */
    public function category_orphans($hash)
    {
        $this->load->model('categories_model');

        // Abort if the category does not exist.
        $data['category'] = $this->categories_model->get(array('hash' => $hash));
        if ($data['category'] == FALSE)
            redirect('admin/items');

        // Load all the categories from memory.
        $data['categories'] = $this->bw_config->categories;

        // Function to load all child categories for a parent.
        $childList = function ($pos) use (& $data) {
            if (count($data) == 0)
                return array();

            $children = array();
            // Loop through categories, adding any which have the $pos id
            // as a parent.
            foreach ($data['categories'] as $cat) {
                if ($cat['parent_id'] == $pos)
                    $children[] = $cat['id'];
            }
            return $children;
        };

        // Begin removing cats, and loop through them, adding more child
        // categories to the $remove_cats list as they are found.
        $remove_cats = array($data['category']['id']);
        while (count($remove_cats) > 0) {
            $remove_pos = $remove_cats[0];
            unset($data['categories'][$remove_pos]);
            unset($remove_cats[0]);
            $remove_cats = array_merge($remove_cats, $childList($remove_pos));
        }

        $data['allow_root'] = FALSE;
        // Calculate what text to display.
        if ($data['category']['count_child_items'] > 0 && $data['category']['count_child_cats'] > 0) {
            $data['list'] = "categories and items";
        } else {
            if ($data['category']['count_child_cats'] > 0) {
                $data['allow_root'] = TRUE;
                $data['list'] = 'categories';
            }

            if ($data['category']['count_child_items'] > 0)
                $data['list'] = 'items';
        }

        // If there is nothing to be done for this category, redirect.
        if (!isset($data['list']))
            redirect('admin/edit/items');

        if ($this->form_validation->run('admin_category_orphans') == TRUE) {
            // Update records accordingly.
            if ($data['list'] == 'items') {
                $this->categories_model->update_items_category($data['category']['id'], $this->input->post('category_id'));
            } else if ($data['list'] == 'categories') {
                $this->categories_model->update_parent_category($data['category']['id'], $this->input->post('category_id'));
            } else if ($data['list'] == 'categories and items') {
                $this->categories_model->update_items_category($data['category']['id'], $this->input->post('category_id'));
                $this->categories_model->update_parent_category($data['category']['id'], $this->input->post('category_id'));
            }

            // Finally, delete the category and redirect.
            if ($this->categories_model->delete($data['category']['id']) == TRUE) {
                $this->current_user->set_return_message('Categories have been moved.','success');
                redirect('admin/edit/items');
            }
        }

        $data['page'] = 'admin/category_orphans';
        $data['title'] = 'Fix Orphans';
        $this->_render($data['page'], $data);
    }

    /**
     * Manage User Invite Tokens.
     * URI: /admin/tokens
     *
     * @see        Models/Users_Model
     * @see        Libraries/Form_Validation
     * @see        Libraries/General
     * @return    void
     */
    public function user_tokens()
    {
        $this->load->model('users_model');
        $this->load->library('form_validation');

        if ($this->input->post('delete_token') == 'Delete Token') {

            if ($this->form_validation->run('admin_delete_token')) {
                // Abort if the token does not exist.
                $token = $this->users_model->check_registration_token($this->input->post('delete_token_content'));
                if ($token == FALSE) {
                    $this->current_user->set_return_message('This token does not exist', 'warning');
                    redirect('admin/user_tokens');
                }

                $data['returnMessage'] = 'Unable to delete the specified token, please try again.';
                if ($this->users_model->delete_registration_token($token['id']) == TRUE) {
                    // Display a message if the token is successfully deleted.

                    $this->current_user->set_return_message('The selected token has been deleted', 'success');
                    redirect('admin/user_tokens');
                }
            }
        }

        // If the Create Token form has been submitted:
        if ($this->input->post('create_token') == "Create") {
            if ($this->form_validation->run('admin_create_token') == TRUE) {
                // Get the registration fee for the chosen user role, and
                // if it does not exist then set the default to 0.0000000 ($config_val)
                // If the admin has chosen the default fee, use that $config_val,
                // otherwise use the number they've given.
                $var = 'entry_payment_' . strtolower($this->general->role_from_id($this->input->post('user_role')));
                $config_val = (isset($this->bw_config->$var)) ? $this->bw_config->$var : 0.00000000;
                $entry_payment = ($this->input->post('entry_payment') == 'default') ? $config_val : $this->input->post('entry_payment');
                // Generate a unique has as the token.

                $data['returnMessage'] = 'Unable to create your token at this time.';
                if ($this->users_model->add_registration_token(array('user_type' => $this->input->post('user_role'),
                        'token_content' => $this->general->unique_hash('registration_tokens', 'token_content', 128),
                        'comment' => $this->input->post('token_comment'),
                        'entry_payment' => $entry_payment)) == TRUE
                ) {
                    $this->current_user->set_return_message('Your token has been created.', 'success');
                    redirect('admin/user_tokens');
                }
            }
        }

        // Load a list of registration tokens.
        $data['tokens'] = $this->users_model->list_registration_tokens();
        $data['page'] = 'admin/user_tokens';
        $data['title'] = 'Registration Tokens';
        $this->_render($data['page'], $data);
    }

    /**
     * Delete an Item, sending the vendor an explanation.
     * URI: /admin/delete_item/$hash
     *
     * @param    string $hash
     */
    public function delete_item($hash)
    {
        $this->load->library('form_validation');
        $this->load->model('items_model');
        $this->load->model('messages_model');

        $data['item'] = $this->items_model->get($hash);
        if ($data['item'] == FALSE)
            redirect('items');

        $data['title'] = 'Delete Item';
        $data['page'] = 'admin/delete_item';

        if ($this->form_validation->run('admin_delete_item') == TRUE) {
            if ($this->items_model->delete($data['item']['id']) == TRUE) {

                $info['from'] = $this->current_user->user_id;
                $details = array('username' => $data['item']['vendor']['user_name'],
                    'subject' => "Listing '{$data['item']['name']}' has been removed");
                $details['message'] = "Your listing has been removed from the marketplace. <br /><br />\n";
                $details['message'] = "Reason for removal:<br />\n" . $this->input->post('reason_for_removal');
                $message = $this->bw_messages->prepare_input($info, $details);
                $this->messages_model->send($message);

                $this->current_user->set_return_message('The selected item has been removed', 'success');
                redirect('items');
            } else {
                $data['returnMessage'] = 'Unable to delete that item at this time.';
            }
        }
        $this->_render($data['page'], $data);
    }

    /**
     * Alter a users ban toggle.
     * URI: /admin/ban_user/$hash
     *
     * @param    string $hash
     * @see        Models/Messages_Model
     * @see        Models/Items_Model
     * @see        Libraries/Form_Validation
     */
    public function ban_user($hash)
    {
        $this->load->library('form_validation');
        $this->load->model('accounts_model');

        $data['user'] = $this->accounts_model->get(array('user_hash' => $hash));
        if ($data['user'] == FALSE)
            redirect('admin/edit/users');

        $data['title'] = 'Ban User';
        $data['page'] = 'admin/ban_user';

        if ($this->input->post('submit_ban_toggle') == 'Submit') {
            if ($this->form_validation->run('admin_ban_user') == TRUE) {
                if ($this->input->post('ban_user') == '1') {
                    $new = (string)((int)$data['user']['banned'] + 1) % 2;
                    if ($this->accounts_model->toggle_ban($data['user']['id'], "$new")) {
                        $m = "{$data['user']['user_name']} has now been " . (($new == 1) ? 'banned.' : 'unbanned.');
                        $this->current_user->set_return_message($m, 'success');
                        redirect('user/' . $data['user']['user_hash']);
                    } else {
                        $data['returnMessage'] = 'Unable to alter this user right now, please try again later.';
                    }
                } else {
                    redirect('user/' . $data['user']['user_hash']);
                }
            }
        }

        $this->_render($data['page'], $data);
    }

    /**
     * Dispute
     *
     * This controller shows either the disputes list (if $order_id is unset)
     * or a specified disputed order (set by $order_id).
     *
     * @param        int $order_id
     */
    public function dispute($order_id = NULL)
    {
        $this->load->library('form_validation');
        $this->load->model('order_model');
        $this->load->model('disputes_model');

        // If no order is specified, load the list of disputes.
        if ($order_id == NULL) {
            $data['page'] = 'admin/disputes_list';
            $data['title'] = 'Active Disputes';
            $data['disputes'] = $this->disputes_model->disputes_list();

        } else {
            $data['dispute'] = $this->disputes_model->get_by_order_id($order_id);
            // If the dispute cannot be found, redirect to the dispute list.
            if ($data['dispute'] == FALSE)
                redirect('admin/disputes');

            $data['page'] = 'admin/dispute';
            $data['title'] = "Disputed Order #{$order_id}";
            // Load the order information.
            $data['current_order'] = $this->order_model->get($order_id);

            // Work out whether the vendor or buyer is disputing.
            $data['disputing_user'] = ($data['dispute']['disputing_user_id'] == $data['current_order']['buyer']['id']) ? $data['current_order']['buyer'] : $data['current_order']['vendor'];
            $data['other_user'] = ($data['dispute']['other_user_id'] == $data['current_order']['buyer']['id']) ? $data['current_order']['buyer'] : $data['current_order']['vendor'];

            // If the message is updated:
            if ($this->input->post('post_dispute_message') == 'Post Message') {
                if ($this->form_validation->run('add_dispute_update') == TRUE) {
                    // Update the dispute record.
                    $update = array('posting_user_id' => $this->current_user->user_id,
                        'order_id' => $order_id,
                        'dispute_id' => $data['dispute']['id'],
                        'message' => $this->input->post('update_message'));
                    if ($this->disputes_model->post_dispute_update($update) == TRUE)
                        redirect('admin/dispute/' . $order_id);
                }
            }

            // Resolution:
            $data['transaction_fee'] = 0.0001;
            $data['admin_fee'] = $data['current_order']['fees'] + $data['current_order']['extra_fees'] - $data['transaction_fee'];
            $data['user_funds'] = (float)($data['current_order']['order_price'] - $data['admin_fee'] - $data['transaction_fee']);

            if ($this->input->post('resolve_dispute') !== null) {

                if ($this->form_validation->run('admin_resolve_dispute') == TRUE) {
                    if ($this->input->post('resolve_dispute_id') == $data['current_order']['id']) {
                        if ($this->input->post('relinquish_fee') == '1') {
                            $data['admin_fee'] = 0;
                            $data['user_funds'] = (float)($data['current_order']['order_price'] - $data['admin_fee'] - $data['transaction_fee']);
                        }

                        if ($data['current_order']['vendor_selected_escrow'] == '1') {
                            $pay_buyer_amount = $this->input->post('pay_buyer');

                            $pay_vendor_amount = $this->input->post('pay_vendor');
                            $sum = $pay_buyer_amount + $pay_vendor_amount;

                            $epsilon = 0.00000001;

                            // Must total user funds available
                            if (abs($sum - $data['user_funds']) < $epsilon) {
                                $tx_outs = array();

                                // Add outputs for the sites fee, buyer, and vendor.
                                if ($data['admin_fee'] > 0) {
                                    $admin_address = BitcoinLib::public_key_to_address($data['current_order']['public_keys']['admin']['public_key'], $this->bw_config->currencies[0]['crypto_magic_byte']);
                                    $tx_outs[$admin_address] = (float)$data['admin_fee'];
                                }
                                if ($pay_buyer_amount > 0) {
                                    $tx_outs[$data['current_order']['buyer_payout']] = (float)$pay_buyer_amount;
                                }
                                if ($pay_vendor_amount > 0) {
                                    $tx_outs[$data['current_order']['vendor_payout']] = (float)$pay_vendor_amount;
                                }

                                // Create spend transaction and redirect, otherwise display an error
                                $create_spend_transaction = $this->order_model->create_spend_transaction($data['current_order']['address'], $tx_outs, $data['current_order']['redeemScript']);
                                if ($create_spend_transaction == TRUE) {
                                    // Notify users by way of a dispute update
                                    $this->disputes_model->post_dispute_update(array('posting_user_id' => '',
                                        'order_id' => $order_id,
                                        'dispute_id' => $data['dispute']['id'],
                                        'message' => 'New transaction on order page.'));

                                    redirect('admin/dispute/' . $order_id);
                                } else {
                                    $data['returnMessage'] = $create_spend_transaction;
                                }
                            } else {
                                $data['amount_error'] = 'The User Funds amount must be completely spread between both users.';
                            }
                        } else {
                            if ($this->order_model->progress_order($data['current_order']['id'], '6') == TRUE) {
                                $update = array('posting_user_id' => '',
                                    'order_id' => $order_id,
                                    'dispute_id' => $data['dispute']['id'],
                                    'message' => 'Dispute closed by admin.');
                                $this->disputes_model->post_dispute_update($update);
                                $this->disputes_model->set_final_response($data['current_order']['id']);
                                redirect('admin/dispute/' . $order_id);
                            }
                        }
                    }
                }
            }
        }
        $this->_render($data['page'], $data);
    }

    /**
     * Key Usage
     *
     * Shows the addresses/public keys the site has created.
     *
     * @param        int $start
     */
    public function key_usage($start = 0)
    {
        if (!($start > 0 && is_numeric($start)))
            $start = 0;

        $this->load->library('pagination');
        $this->load->model('bitcoin_model');

        $data['page'] = 'admin/key_usage';
        $data['title'] = 'Key Usage';
        $data['count'] = $this->bitcoin_model->count_key_usage();

        $pagination = array();
        $pagination["base_url"] = site_url("admin/bitcoin/key_usage");
        $pagination["total_rows"] = $data['count'];
        $pagination["per_page"] = 40;
        $pagination["uri_segment"] = 4;
        $pagination["num_links"] = round($pagination["total_rows"] / $pagination["per_page"]);
        $this->pagination->initialize($pagination);

        $data['links'] = $this->pagination->create_links();
        $data['records'] = $this->bitcoin_model->get_key_usage_page($pagination['per_page'], $start);
        $this->_render($data['page'], $data);
    }

    /**
     * Fee's
     *
     * This controller allows the administrator to view or edit the
     * fee's charged for an order price within a specified price range.
     */
    public function fees()
    {
        $this->load->library('form_validation');
        $this->load->model('fees_model');
        $data['config'] = $this->bw_config->load_admin('fees');

        if ($this->input->post('update_config') == 'Update') {
            if ($this->form_validation->run('admin_update_fee_config') == TRUE) {
                $changes['minimum_fee'] = ($data['config']['minimum_fee'] != $this->input->post('minimum_fee')) ? $this->input->post('minimum_fee') : NULL;
                $changes['default_rate'] = ($data['config']['default_rate'] != $this->input->post('default_rate')) ? $this->input->post('default_rate') : NULL;
                $changes['escrow_rate'] = ($data['config']['escrow_rate'] != $this->input->post('escrow_rate')) ? $this->input->post('escrow_rate') : NULL;
                $changes = array_filter($changes, 'strlen');

                if (count($changes) > 0 && $this->config_model->update($changes) == TRUE) {
                    $log = $this->admin_model->format_config_changes($changes);
                    $this->logs_model->add('Admin: Fees Panel', 'Fees configuration updated', 'The fees configuration of the site has been updated:<br />' . $log, 'Info');

                    $this->current_user->set_return_message('Your changes have been saved.','success');
                } else {
                    $this->current_user->set_return_message('No changes have been made.','warning');
                }

                redirect('admin/items/fees');
            }
        }

        if ($this->input->post('delete_rate') == 'Delete') {
            if ($this->form_validation->run('admin_delete_fee_rate') == TRUE) {
                $key = array_keys($this->input->post('delete_rate'));
                $id = $key[0];
                if ($this->fees_model->delete($id) == TRUE) {
                    $this->current_user->set_return_message('The selected fee has been deleted.', 'success');
                    redirect('admin/items/fees');
                }
            }
        }

        if ($this->input->post('create_fee') == 'Add') {
            if ($this->form_validation->run('admin_add_fee') == TRUE) {
                if ($this->input->post('upper_limit') > $this->input->post('lower_limit')) {
                    $rate = array('low' => $this->input->post('lower_limit'),
                        'high' => $this->input->post('upper_limit'),
                        'rate' => $this->input->post('percentage_fee'));
                    if ($this->fees_model->add($rate) == TRUE) {
                        $this->current_user->set_return_message('Basic settings have been updated.', 'success');
                        redirect('admin/items/fees');
                    }
                } else {
                    $data['returnMessage'] = 'Upper limit must be less than lower limit!';
                }
            }
        }

        $data['config'] = $this->bw_config->load_admin('fees');
        $data['fees'] = $this->fees_model->fees_list();
        $data['page'] = 'admin/fees';
        $data['title'] = 'Order Fees';
        $this->_render($data['page'], $data);
    }

    /**
     * Maintenance
     *
     * This controller is used to put the site into maintenance mode.
     * When this happens, the sites configuration is backed up in an
     * entry in the bw_config table, and replaces them with safer defaults.
     * Maintenance mode prevents anyone but an admin from logging in, and
     * will disable any bitcoin related functionality.
     * This can be triggered by bitcoind alerts, or issues reported via
     * github.
     */
    public function maintenance()
    {
        $this->load->library('form_validation');
        $this->load->model('admin_model');

        $data['config'] = $this->bw_config->status();

        // Check if form was submitted.
        if ($this->input->post('set_maintenance_mode') == 'Update') {
            if ($this->form_validation->run('admin_maintenance_mode') == TRUE) {
                // Load the submitted value
                $maintenance_mode = ($this->input->post('maintenance_mode') == '0') ? FALSE : TRUE;

                // If different to the stored value, change the site mode.
                if ($data['config']['maintenance_mode'] !== $maintenance_mode) {
                    $result = ($maintenance_mode == FALSE) ? $this->admin_model->set_mode('online') : $this->admin_model->set_mode('maintenance');
                    if ($result == TRUE)
                        redirect('admin/maintenance');
                }
            }
        }

        $data['title'] = 'Maintenance Settings';
        $data['page'] = 'admin/maintenance';
        $this->_render($data['page'], $data);
    }

    /**
     * Orders
     *
     * This function shows a list of all the orders on the site.
     *
     * @param        int $start
     */
    public function orders($start = 0)
    {
        if (!($start > 0 && is_numeric($start)))
            $start = 0;

        $this->load->model('order_model');
        $this->load->library('pagination');

        $pagination = array();
        $pagination["base_url"] = site_url("admin/orders/");
        $pagination["total_rows"] = $this->order_model->admin_count_orders();
        $pagination["per_page"] = 50;
        $pagination["uri_segment"] = 4;
        $pagination["num_links"] = round($pagination["total_rows"] / $pagination["per_page"]);
        $this->pagination->initialize($pagination);
        $data['links'] = $this->pagination->create_links();
        $data['orders'] = $this->order_model->admin_order_page($pagination['per_page'], $start);
        $data['page'] = 'admin/order_list';
        $data['title'] = 'Order List';
        $this->_render($data['page'], $data);
    }

    /**
     * User List
     *
     * Used to display a user list for administrator. Includes a search
     * option where an administrator can search for a user by name, or
     * instead they can ask for a list of : all users, buyers only,
     * vendors only, admins only.. additional parameters can be supplied,
     * such as whether or not the user is banned, or has their account
     * activated/paid for. Lists can be ordered by particular values,
     * such as the user id, last login time, registration time, etc.
     * The order can be randomized, or just ordered ascending or descending.
     *
     * @param        int $start
     */
    public function user_list($start = 0)
    {
        $this->load->library('pagination');
        $this->load->model('users_model');

        $user_params = array();

        $pagination = array();
        $pagination["base_url"] = site_url("admin/users/list");
        $pagination["total_rows"] = $this->users_model->count_user_list($user_params);
        $pagination["per_page"] = 40;
        $pagination["uri_segment"] = 4;
        $pagination["num_links"] = round($pagination["total_rows"] / $pagination["per_page"]);
        $this->pagination->initialize($pagination);

        $data['links'] = $this->pagination->create_links();
        $data['users'] = $this->users_model->user_list($user_params, $pagination['per_page'], $start);

        // If the user is searching for by username.
        if ($this->input->post('search_username') == 'Search') {
            if ($this->form_validation->run('admin_search_username') == TRUE) {

                // Search for the user.
                $user_name = $this->input->post('user_name');
                $data['users'] = $this->users_model->search_user($user_name);
                $data['links'] = '';

                // If the search fails, indicate the failed search error.
                if ($data['users'] == FALSE)
                    $data['search_fail'] = TRUE;
            }

        } else if ($this->input->post('list_options') == 'Advanced Search') {
            // If the user is listing users
            $data['links'] = '';
            if ($this->form_validation->run('admin_search_user_list') == TRUE) {

                // Gather the search terms.
                $search_for = $this->input->post('search_for');
                $with_property = $this->input->post('with_property');
                $order_by = $this->input->post('order_by');
                $list = $this->input->post('list');

                if ($search_for !== '') {
                    // set which role to look for in the search.
                    switch ($search_for) {
                        case 'all_users':
                            break;
                        case 'buyers':
                            $params['user_role'] = 'Buyer';
                            break;
                        case 'vendors':
                            $params['user_role'] = 'Vendor';
                            break;
                        case 'admins':
                            $params['user_role'] = 'Admin';
                            break;
                        default:
                            break;
                    }

                    // If this property is set, the search is narrowed down.
                    if ($with_property !== '') {
                        switch ($with_property) {
                            case 'activated':
                                $params['entry_paid'] = '1';
                                break;
                            case 'not_activated':
                                $params['entry_paid'] = '0';
                                break;
                            case 'banned':
                                $params['banned'] = '1';
                                break;
                            case 'not_banned':
                                $params['entry_paid'] = '0';
                                break;
                            default:
                                break;
                        }
                    }

                    // The column to order by is set, or uses a default.
                    $params['order_by'] = ($order_by !== '') ? $order_by : 'id';
                    // The list order is set, or uses a default.
                    $params['list'] = ($list !== '') ? $list : 'ASC';
                }

                // If the order by term isn't set, check if we need to set it.
                if (!isset($params['order_by']) && $order_by !== '')
                    $params['order_by'] = ($order_by !== '') ? $order_by : 'id';

                // Load the user list based on the generated parameters.
                $data['users'] = $this->users_model->user_list($params, $pagination["per_page"], $start);

                // If the search fails, show the failed search error.
                if ($data['users'] == FALSE)
                    $data['search_fail'] = TRUE;
            }

        }

        $data['page'] = 'admin/user_list';
        $data['title'] = 'User List';
        $this->_render($data['page'], $data);
    }

    /**
     * Terms Of Service
     *
     * This function allows an administrator to configure a terms of
     * service which registering users must agree to in order to register
     * an account. The admin can disable the option by selecting the
     * 'disable' checkbox on the page. If this is not selected, then the
     * user can alter the textbox containing the agreement to display.
     */
    public function tos()
    {
        $data['config'] = $this->bw_config->load_admin('');
        $data['tos'] = $this->bw_config->terms_of_service;

        if ($this->input->post('tos_update') == 'Update') {
            if ($this->form_validation->run('admin_tos') == TRUE) {

                $changes = array();

                // If the toggle has changed, record it.
                if ($data['tos'] != $this->input->post('terms_of_service_toggle'))
                    $changes['terms_of_service_toggle'] = $this->input->post('terms_of_service_toggle');

                // If the TOS is enabled, update the record if it has changed.
                if (($data['config']['terms_of_service_toggle'] == TRUE AND $this->input->post('terms_of_service_toggle') == '1') OR $this->input->post('terms_of_service_toggle') == '1')
                    $changes['terms_of_service'] = ($data['config']['terms_of_service'] != htmlentities($this->input->post('terms_of_service'))) ? htmlentities($this->input->post('terms_of_service')) : NULL;

                $changes = array_filter($changes, 'strlen');
                if (count($changes) > 0)
                    if ($this->config_model->update($changes) == TRUE)
                        redirect('admin/tos');
            }
        }

        $data['title'] = 'Terms Of Service';
        $data['page'] = 'admin/tos';
        $this->_render($data['page'], $data);
    }

    /**
     * Locations
     *
     * This page allows adminstrators to configure the sites location source.
     * A default list of countries, or a user-defined list can be chosen.
     * Admins can add locations to a multi-dimensional list, or delete them.
     * A graphical representation of this array of locations is displayed
     * at the bottom of the page.
     *
     */
    public function locations()
    {

        $this->load->library('form_validation');
        $this->load->model('location_model');

        if ($this->input->post('update_location_list_source') == 'Submit') {
            if ($this->form_validation->run('admin_update_location_list_source') == TRUE) {
                $changes = array();
                $changes['location_list_source'] = ($this->input->post('location_source') != $this->bw_config->location_list_source) ? $this->input->post('location_source') : NULL;

                if ($changes['location_list_source'] && count($this->location_model->get_list('Custom')) == 0) {
                    $data['returnMessage'] = 'There are no locations on this list - add some first!';
                    unset($changes['location_list_source']);
                }

                if (count($changes) > 0 && $this->config_model->update($changes) == TRUE) {
                    $this->current_user->set_return_message('Changed location list.', 'success');
                    redirect('admin/locations');
                } else {
                    $data['returnMessage'] = 'No changes were made.';
                }
            }
        }

        if ($this->input->post('add_custom_location') == 'Submit') {
            if ($this->form_validation->run('admin_add_custom_location') == TRUE) {
                $location = array('location' => $this->input->post('create_location'),
                    'hash' => $this->general->unique_hash('locations_custom_list', 'hash'),
                    'parent_id' => $this->input->post('location'));
                if ($this->location_model->add_custom_location($location) == TRUE) {
                    $this->current_user->set_return_message('Your new location has been saved', 'success');
                    redirect('admin/locations');
                }
            }
        }

        if ($this->input->post('delete_custom_location') == 'Submit') {
            if ($this->form_validation->run('admin_delete_custom_location') == TRUE) {
                if ($this->location_model->delete_custom_location($this->input->post('location_delete')) == TRUE) {
                    $this->current_user->set_return_message('Your location has been deleted', 'success');
                    redirect('admin/locations');
                }
            }
        }

        $data['list_source'] = $this->bw_config->location_list_source;
        $data['locations_parent'] = $this->location_model->generate_select_list('Custom', 'location', 'form-control', FALSE, array('root' => TRUE));
        $custom_locations_array = $this->location_model->get_list('Custom', TRUE);
        $data['locations_human_readable'] = $this->location_model->menu_human_readable($custom_locations_array, 0, 'form-control');
        $data['locations_delete'] = $this->location_model->generate_select_list('Custom', 'location_delete', 'form-control');
        $data['page'] = 'admin/locations';
        $data['title'] = 'Configure Locations';
        $this->_render($data['page'], $data);
    }

}

;

/* End of file: Admin.php */
/* Location: application/controllers/Admin.php */
