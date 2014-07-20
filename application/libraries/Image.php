<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Image Library
 *
 * @package		BitWasp
 * @subpackage	Libraries
 * @category	Image
 * @author		BitWasp
 */
class Image {
	/** 
	 * Use Library
	 * 
	 * Variable which tracks which image extension to use
	 */
	public $use_library;
	
	/**
	 * Output Format
	 * 
	 * Configure image output format
	 */
	public $output_format = 'png';
	
	/**
	 * Imagick Import
	 * 
	 * If using the Imagick extension, store the imported file somewhere 
	 * where we can keep accessing it later if performing several actions.
	 */
	protected $imagick_import;
	/**
	 * GD Import
	 * 
	 * If using PHP's GD extension, store the imported file somewhere
	 * where we can keep accessing it later if performing several actions.
	 */
	protected $gd_import;
	
	/**
	 * Import
	 * 
	 * Is set to TRUE when an image has been imported.
	 */
	protected $import = FALSE;

	/**
	 * Constructor
	 * 
	 * Determines which extension to use for modifying the image
	 */
	public function __construct() {
		// If magickwand extension is present
		if(extension_loaded('magickwand') && function_exists("NewMagickWand")) {
			/* ImageMagick is installed and working */
			$this->use_library = 'magickwand';
		} elseif (extension_loaded('gd') && function_exists('gd_info')) {
			$this->use_library = 'gd';
		} 
	}
	
	// This function imports the image and stores it in the object. 
	// Need full path, file ext, raw_name
	/**
	 * Import
	 * 
	 * $image_info = array('full_path' => '..',
	 * 					   'file_ext' => '...',
	 * 						'raw_name' => '..');
	 * 
	 * This function imports the image and stores it in $this->{ext}_import,
	 * where {ext} can be imagick or gd, depending on what's available.
	 * 
	 * @param	array	$image_info
	 */
	public function import($image_info) {
		if($this->use_library == 'magickwand') {
			$this->imagick_import = new Imagick($image_info['full_path']);
			$this->import = $image_info;
		} else if($this->use_library == 'gd') {
			
			if($image_info['file_ext'] == '.png') {
				// Load PNG image.
				$this->gd_import = imagecreatefrompng($image_info['full_path']);				
			} elseif($image_info['file_ext'] == '.jpeg' || $image_info['file_ext'] == '.jpg') {
				// Load JPEG image.
				$this->gd_import = imagecreatefromjpeg($image_info['full_path']);
			} elseif($image_info['file_ext'] == '.gif' ) {
				// Load GIF image
				$this->gd_import = imagecreatefromgif($image_info['full_path']);
			}
			$this->import = $image_info;	
		}
	}
	
	/**
	 * Resize
	 * 
	 * General function to resize an imported image, and to export the
	 * image into a new filename. Returns an array with information on
	 * a successful resize, or FALSE if there is nothing to import.
	 *
	 * @param		int	$width
	 * @param		int	$height
	 * @param		string	$new_name
	 * @return		array/FALSE
	 */	
	public function resize($width, $height, $new_name) {
		// Abort if the import wasn't done.
		if($this->import == FALSE)
			return FALSE;
	
		$results['file_name'] = "{$new_name}.{$this->output_format}";
		$results['file_ext'] = $this->output_format;
		$results['encoded_string'] = '';
		$results['file_path'] = $this->import['file_path'];
		$results['full_path'] = $this->import['file_path'].$results['file_name'];
		$results['raw_name'] = "{$new_name}";
					
		if($this->use_library == "magickwand") {
			$copy = $this->imagick_import;

			// Strip EXIF data.
			$copy->stripImage();
			$copy->setImageFormat( $this->output_format );
			$copy->setImageOpacity(1.0);
	     	$copy->resizeImage($width, $height, imagick::FILTER_CATROM, 0.9, true);
			$copy->writeImage($results['full_path']);		

			// Generate the encoded string without writing the file.
			$results['encoded_string'] = $this->capture_image($copy);
			
		} else if($this->use_library == "gd") {
			
			$our_ratio = $width/$height;
			
			// Extract the width and heightfrom the new image.
			list($curr_width, $curr_height) = getimagesize($this->import['full_path']);

			// Calculate the ratio of the new image.
    		$curr_ratio = $curr_width / $curr_height;
			
        	if ($curr_ratio < $our_ratio) {
				// Dimensions for the thumbnail. Height is max, and relative width calculated.
				$new_height = $height;
				$new_width = $height*$our_ratio;
       		} else {
				// In this case the ratio is greater (in favour of the width) 
				$new_width = $width;
				$new_height = $width/$our_ratio;
			}	

   			$new_image = imagecreatetruecolor($new_width, $new_height);
   			imagecopyresampled($new_image, $this->gd_import, 0, 0, 0, 0, $new_width, $new_height, $curr_width, $curr_height);
   			
   			// Generate the encoded string without writing the file.
			$results['encoded_string'] = $this->capture_image($new_image);
			
		}
			
		return $results;
	}

	/** 
	 * Capture Image
	 * 
	 * Take a supplied image, and whether its an imagick or gd image, 
	 * will capture the contents, instead of writing to a file. Converts 
	 * to base64. Parameter $image_png can be either an Imagick object, 
	 * or a GD image resource.
	 * 
	 * @param	Imagick/GD  $image_png
	 * @return	string/FALSE
	 */
	public function capture_image($image_png) {
		if($this->use_library == 'magickwand') {
			$image_data = $image_png->getimageblob();
			ob_start();
			echo $image_data;
			$image_data = ob_get_contents();
			ob_end_clean();
		} else if($this->use_library == 'gd') {
			ob_start();
			imagepng($image_png);
			$image_data = ob_get_contents();
			ob_end_clean();
		}
		
		return (isset($image_data)) ? base64_encode($image_data) : FALSE;
	}

	/**
	 * Encode
	 * 
	 * Temporarily create a base64 image from a file. This is used to 
	 * display captchas, and other images which will only be used once
	 * and don't need to be stored in the database.. Returns FALSE if
	 * the file cannot be found.
	 * 
	 * @param		string	$filename
	 * @return		string/FALSE
	 */
	public function encode($filename) {
		$filename = '/tmp/'.$filename;
		return ($file = file_get_contents($filename)) ? base64_encode($file) : FALSE;	
	}
		
	/**
	 * Temp
	 * 
	 * Create the HTML string that will display the image temporarily.
	 * @param	string	$filename
	 * @return	string
	 */
	public function temp($filename) {
		$image = $this->encode($filename);
		$html = "<img src=\"data:image/png;base64,{$image}\" />\n";
		return $html;
	}

};

/* End of file: Image.php */
/* Location: application/libraries/Image.php */
