<?php 
class MGWPP_Gallery_ShortCode {
    public static function mgwpp_gallery_register_shortcode(){
        // Register shortcode to call the render_gallery method
        add_shortcode('mgwpp_gallery', array('MGWPP_Gallery_ShortCode', 'mgwpp_render_gallery'));
    }

  
}

add_action('init', array('MGWPP_Gallery_ShortCode', 'mgwpp_gallery_register_shortcode'));
?>
