<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Pro_Carousel {
    public static function render($post_id, $images, $settings = []) {
        if (empty($images) || !is_array($images)) {
            return '<div class="mg-error">Add images to create a gallery</div>';
        }

        // Get the placeholder image from settings if set
        $placeholder = isset($settings['placeholder_image']['url']) ? $settings['placeholder_image']['url'] : '';

        ob_start();
        ?>
        <div class="mg-pro-carousel" data-carousel-id="<?php echo absint($post_id); ?>">
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--prev">‹</button>
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--next">›</button>
            
            <div class="mg-pro-carousel__container">
                <div class="mg-pro-carousel__track">
                    <?php foreach ($images as $image): 
                        // Get image URL and fallback to placeholder if necessary
                        $image_url = wp_get_attachment_image_url($image->ID, 'large') ?: $placeholder;
                        // For dynamic tags, you could allow the alt text to be overridden by a dynamic control; 
                        // here we use the image’s alt text
                        $alt_text  = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
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
