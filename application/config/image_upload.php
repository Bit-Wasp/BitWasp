<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Image Upload
 * 
 * These are predefined settings for image upload rules. 
 */

// Build the configuration settings for the upload library.
$config['upload_path'] = '/tmp/';    // Path to upload to. 
$config['allowed_types'] = 'gif|jpg|jpeg|png';  // Allowed file types
$config['max_size']	= '2048';
$config['max_width']  = '2000';
$config['max_height']  = '2000';
$config['encrypt_name'] = true;
