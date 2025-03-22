<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Pro_Carousel {
    public static function render($post_id, $images) {
        if (empty($images) || !is_array($images)) {
            return '<div class="mg-error">Add images to create a gallery</div>';
        }

        ob_start();
        ?>
        <div class="mg-pro-carousel" data-carousel-id="<?php echo absint($post_id); ?>">
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--prev">‹</button>
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--next">›</button>
            
            <div class="mg-pro-carousel__container">
                <div class="mg-pro-carousel__track">
                    <?php foreach ($images as $image): 
                        $image_url = wp_get_attachment_image_url($image->ID, 'large');
                        $alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                    ?>
                    <div class="mg-pro-carousel__card">
                        <img class="mg-pro-carousel__image" 
                            src="<?php echo esc_url($image_url); ?>" 
                            alt="<?php echo esc_attr($alt_text); ?>"
                            loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}