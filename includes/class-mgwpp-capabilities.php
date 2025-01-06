<?php 
class MGWPP_Gallery_ShortCode{
    public static function register_shortcode(){
        add_shortcode('mgwpp_gallery', array('MGWPP_Gallery_Shortcode', 'render_gallery'));
    }

    public static function render_gallery($atts){
        ob_start();
        ?>
        <div class="mgwpp-gallery-container">

        </div>
        <?php 
    }
}
;?>