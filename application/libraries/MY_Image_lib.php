<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Image_lib extends CI_Image_lib
{
    /**
     * CI
     * @var
     */
    protected $CI;

    /**
     * Option: want to return a base64 image?
     *
     * @var bool
     */
    public $return_base64 = FALSE;

    /**
     * Place to house image handle
     *
     * @var resource
     */
    public $t_image_base64 = '';

    /**
     * Place to store base64 image for retrieval
     *
     * @var string
     */
    public $base64_image = '';

    /**
     * Dest Image
     *
     * @var
     */
    public $dest_image;
    /**
     * Construct
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * __get
     *
     * magic function.
     *
     * @param    object $object
     */
    public function __get($object)
    {
        $this->CI = & get_instance();
        return $this->CI->$object;
    }

    /**
     * initialize image preferences
     *
     * @param    array
     * @return    bool
     */
    public function initialize($props = array())
    {
        // Convert array elements into class variables
        if (count($props) > 0) {
            foreach ($props as $key => $val) {
                if (property_exists($this, $key)) {
                    if (in_array($key, array('wm_font_color', 'wm_shadow_color'))) {
                        if (preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $val, $matches)) {
                            /* $matches[1] contains our hex color value, but it might be
                             * both in the full 6-length format or the shortened 3-length
                             * value.
                             * We'll later need the full version, so we keep it if it's
                             * already there and if not - we'll convert to it. We can
                             * access string characters by their index as in an array,
                             * so we'll do that and use concatenation to form the final
                             * value:
                             */
                            $val = (strlen($matches[1]) === 6)
                                ? '#' . $matches[1]
                                : '#' . $matches[1][0] . $matches[1][0] . $matches[1][1] . $matches[1][1] . $matches[1][2] . $matches[1][2];
                        } else {
                            continue;
                        }
                    }

                    $this->$key = $val;
                }
            }
        }

        // Is there a source image? If not, there's no reason to continue
        if ($this->source_image === '') {
            $this->set_error('imglib_source_image_required');
            return FALSE;
        }

        /* Is getimagesize() available?
         *
         * We use it to determine the image properties (width/height).
         * Note: We need to figure out how to determine image
         * properties using ImageMagick and NetPBM
         */
        if (!function_exists('getimagesize')) {
            $this->set_error('imglib_gd_required_for_props');
            return FALSE;
        }

        $this->image_library = strtolower($this->image_library);

        /* Set the full server path
         *
         * The source image may or may not contain a path.
         * Either way, we'll try use realpath to generate the
         * full server path in order to more reliably read it.
         */
        if (($full_source_path = realpath($this->source_image)) !== FALSE) {
            $full_source_path = str_replace('\\', '/', $full_source_path);
        } else {
            $full_source_path = $this->source_image;
        }

        $x = explode('/', $full_source_path);
        $this->source_image = end($x);
        $this->source_folder = str_replace($this->source_image, '', $full_source_path);

        // Set the Image Properties
        if (!$this->get_image_properties($this->source_folder . $this->source_image)) {
            return FALSE;
        }

        /*
         * Assign the "new" image name/path
         *
         * If the user has set a "new_image" name it means
         * we are making a copy of the source image. If not
         * it means we are altering the original. We'll
         * set the destination filename and path accordingly.
         */
        if ($this->return_base64 === FALSE) {
            if ($this->new_image === '') {
                $this->dest_image = $this->source_image;
                $this->dest_folder = $this->source_folder;
            } elseif (strpos($this->new_image, '/') === FALSE) {
                $this->dest_folder = $this->source_folder;
                $this->dest_image = $this->new_image;
            } else {
                if (strpos($this->new_image, '/') === FALSE && strpos($this->new_image, '\\') === FALSE) {
                    $full_dest_path = str_replace('\\', '/', realpath($this->new_image));
                } else {
                    $full_dest_path = $this->new_image;
                }

                // Is there a file name?
                if (!preg_match('#\.(jpg|jpeg|gif|png)$#i', $full_dest_path)) {
                    $this->dest_folder = $full_dest_path . '/';
                    $this->dest_image = $this->source_image;
                } else {
                    $x = explode('/', $full_dest_path);
                    $this->dest_image = end($x);
                    $this->dest_folder = str_replace($this->dest_image, '', $full_dest_path);
                }
            }
        }

        /* Compile the finalized filenames/paths
         *
         * We'll create two master strings containing the
         * full server path to the source image and the
         * full server path to the destination image.
         * We'll also split the destination image name
         * so we can insert the thumbnail marker if needed.
         */
        if ($this->create_thumb === FALSE OR $this->thumb_marker === '') {
            $this->thumb_marker = '';
        }

        $this->full_src_path = $this->source_folder . $this->source_image;
        if ($this->return_base64 === FALSE) {
            $xp = $this->explode_name($this->dest_image);

            $filename = $xp['name'];
            $file_ext = $xp['ext'];

            $this->full_dst_path = $this->dest_folder . $filename . $this->thumb_marker . $file_ext;
        }

        /* Should we maintain image proportions?
         *
         * When creating thumbs or copies, the target width/height
         * might not be in correct proportion with the source
         * image's width/height. We'll recalculate it here.
         */
        if ($this->maintain_ratio === TRUE && ($this->width !== 0 OR $this->height !== 0)) {
            $this->image_reproportion();
        }

        /* Was a width and height specified?
         *
         * If the destination width/height was not submitted we
         * will use the values from the actual file
         */
        if ($this->width === '') {
            $this->width = $this->orig_width;
        }

        if ($this->height === '') {
            $this->height = $this->orig_height;
        }

        // Set the quality
        $this->quality = trim(str_replace('%', '', $this->quality));

        if ($this->quality === '' OR $this->quality === 0 OR !ctype_digit($this->quality)) {
            $this->quality = 90;
        }

        // Set the x/y coordinates
        is_numeric($this->x_axis) OR $this->x_axis = 0;
        is_numeric($this->y_axis) OR $this->y_axis = 0;

        // Watermark-related Stuff...
        if ($this->wm_overlay_path !== '') {
            $this->wm_overlay_path = str_replace('\\', '/', realpath($this->wm_overlay_path));
        }

        if ($this->wm_shadow_color !== '') {
            $this->wm_use_drop_shadow = TRUE;
        } elseif ($this->wm_use_drop_shadow === TRUE && $this->wm_shadow_color === '') {
            $this->wm_use_drop_shadow = FALSE;
        }

        if ($this->wm_font_path !== '') {
            $this->wm_use_truetype = TRUE;
        }

        return TRUE;
    }

    /**
     * Encode
     *
     * Temporarily create a base64 image from a file. This is used to
     * display captchas, and other images which will only be used once
     * and don't need to be stored in the database.. Returns FALSE if
     * the file cannot be found.
     *
     * @param        string $filename
     * @return        string/FALSE
     */
    public function b64encode_any_image($filename)
    {
        $filename = '/tmp/' . $filename;
        return ($file = file_get_contents($filename)) ? base64_encode($file) : FALSE;
    }

    /**
     * Initialize image properties
     *
     * Resets values in case this class is used in a loop
     *
     * @return    void
     */
    public function clear()
    {
        $props = array('thumb_marker', 'library_path', 'source_image', 'new_image', 'width', 'height', 'rotation_angle', 'x_axis', 'y_axis', 'wm_text', 'wm_overlay_path', 'wm_font_path', 'wm_shadow_color', 'source_folder', 'dest_folder', 'mime_type', 'orig_width', 'orig_height', 'image_type', 'size_str', 'full_src_path', 'full_dst_path');

        foreach ($props as $val) {
            $this->$val = '';
        }

        $this->image_library = 'gd2';
        $this->dynamic_output = FALSE;
        $this->return_base64 = FALSE;
        $this->quality = 90;
        $this->create_thumb = FALSE;
        $this->thumb_marker = '_thumb';
        $this->maintain_ratio = TRUE;
        $this->master_dim = 'auto';
        $this->wm_type = 'text';
        $this->wm_x_transp = 4;
        $this->wm_y_transp = 4;
        $this->wm_font_size = 17;
        $this->wm_vrt_alignment = 'B';
        $this->wm_hor_alignment = 'C';
        $this->wm_padding = 0;
        $this->wm_hor_offset = 0;
        $this->wm_vrt_offset = 0;
        $this->wm_font_color = '#ffffff';
        $this->wm_shadow_distance = 2;
        $this->wm_opacity = 50;
        $this->create_fnc = 'imagecreatetruecolor';
        $this->copy_fnc = 'imagecopyresampled';
        $this->error_msg = array();
        $this->wm_use_drop_shadow = FALSE;
        $this->wm_use_truetype = FALSE;
    }

    public function image_process_gd($action = 'resize')
    {
        $v2_override = FALSE;

        // If the target width/height match the source, AND if the new file name is not equal to the old file name
        // we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
        if ($this->dynamic_output === FALSE && $this->orig_width === $this->width && $this->orig_height === $this->height) {
            if ($this->return_base64 == FALSE && $this->source_image !== $this->new_image && @copy($this->full_src_path, $this->full_dst_path)) {
                @chmod($this->full_dst_path, 0666);
            }

            return TRUE;
        }

        // Let's set up our values based on the action
        if ($action === 'crop') {
            // Reassign the source width/height if cropping
            $this->orig_width = $this->width;
            $this->orig_height = $this->height;

            // GD 2.0 has a cropping bug so we'll test for it
            if ($this->gd_version() !== FALSE) {
                $gd_version = str_replace('0', '', $this->gd_version());
                $v2_override = ($gd_version === 2);
            }
        } else {
            // If resizing the x/y axis must be zero
            $this->x_axis = 0;
            $this->y_axis = 0;
        }

        //  Create the image handle
        if (!($src_img = $this->image_create_gd())) {
            return FALSE;
        }

        /* Create the image
         *
         * Old conditional which users report cause problems with shared GD libs who report themselves as "2.0 or greater"
         * it appears that this is no longer the issue that it was in 2004, so we've removed it, retaining it in the comment
         * below should that ever prove inaccurate.
         *
         * if ($this->image_library === 'gd2' && function_exists('imagecreatetruecolor') && $v2_override === FALSE)
         */
        if ($this->image_library === 'gd2' && function_exists('imagecreatetruecolor')) {
            $create = 'imagecreatetruecolor';
            $copy = 'imagecopyresampled';
        } else {
            $create = 'imagecreate';
            $copy = 'imagecopyresized';
        }

        $dst_img = $create($this->width, $this->height);

        if ($this->image_type === 3) // png we can actually preserve transparency
        {
            imagealphablending($dst_img, FALSE);
            imagesavealpha($dst_img, TRUE);
        }

        $copy($dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height);

        // Show the image
        if ($this->dynamic_output === TRUE) {
            $this->image_display_gd($dst_img);
        } elseif ($this->return_base64 === TRUE) {
            $this->t_image_base64 = $dst_img;
            $this->handle_base64_image();

        } elseif (!$this->image_save_gd($dst_img)) // Or save it
        {
            return FALSE;
        }

        // Kill the file handles
        imagedestroy($dst_img);
        imagedestroy($src_img);

        // Set the file to 666
        if (!$this->return_base64)
            @chmod($this->full_dst_path, 0666);

        return TRUE;
    }

    public function handle_base64_image()
    {
        ob_start();
        imagejpeg($this->t_image_base64);
        $image_data = base64_encode(ob_get_contents());
        ob_end_clean();
        $this->base64_image = $image_data;
        return TRUE;
    }
}

;