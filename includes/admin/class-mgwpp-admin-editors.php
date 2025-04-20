<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin_Editors
{

    /**
     * map gallery types → editor file & class
     **/
    private static $map = [
      'single_carousel'    => [ 'file' => 'single-gallery-editor-options.php',     'class' => 'MGWPP_Single_Gallery_Editor_Options' ],
      'grid'               => [ 'file' => 'grid-gallery-editor-options.php',       'class' => 'MGWPP_Grid_Gallery_Editor_Options' ],
      'mega_slider'        => [ 'file' => 'mega-slider-gallery-editor-options.php','class' => 'MGWPP_Mega_Slider_Gallery_Editor_Options' ],
      // …and so on for each type…
    ];

    public static function init()
    {
        // load dynamically on add_meta_boxes
        add_action('add_meta_boxes', [ __CLASS__, 'load_editor_for_type' ], 10, 2);
        // and hook into save_post
        add_action('save_post_mgwpp_soora', [ __CLASS__, 'save_editor_for_type' ], 10, 2);
    }

    public static function load_editor_for_type($post_type, $post)
    {
        if ($post_type !== 'mgwpp_soora') {
            return;
        }

        // 1) get gallery type or default
        $type = get_post_meta($post->ID, 'mgwpp_gallery_type', true) ?: 'single_carousel';

        // 2) if we have a mapping, require the file and call its registration
        if (isset(self::$map[ $type ])) {
            $info = self::$map[ $type ];
            $path = plugin_dir_path(__FILE__) . 'editors/class-mgwpp-' . $info['file'];
            if (file_exists($path)) {
                include_once $path;
                if (class_exists($info['class'])) {
                    call_user_func([ $info['class'], 'register_meta_boxes' ], $post);
                }
            }
        }
    }

    public static function save_editor_for_type($post_id, $post)
    {
        // basic autosave/permission guard
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $type = get_post_meta($post_id, 'mgwpp_gallery_type', true) ?: 'single_carousel';
        if (isset(self::$map[ $type ])) {
            $class = self::$map[ $type ]['class'];
            if (method_exists($class, 'save_meta')) {
                call_user_func([ $class, 'save_meta' ], $post_id);
            }
        }
    }
}

MGWPP_Admin_Editors::init();
