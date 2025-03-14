<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MGWPP_Pro_Carousel {
    public static function render($post_id, $images) {
     

        ob_start();
        ?>
        <div class="mg-pro-carousel">
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--prev" aria-label="<?php esc_attr_e('Previous', 'mini-gallery'); ?>">‹</button>
            <button class="mg-pro-carousel__nav mg-pro-carousel__nav--next" aria-label="<?php esc_attr_e('Next', 'mini-gallery'); ?>">›</button>
            <div class="mg-pro-carousel__container">
                <div class="mg-pro-carousel__track">
                    <?php foreach ($images as $image): ?>
                    <div class="mg-pro-carousel__card">
                        <img 
                            class="mg-pro-carousel__image" 
                            src="<?php echo esc_url(wp_get_attachment_image_url($image->ID, 'large')); ?>" 
                            alt="<?php echo esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true)); ?>"
                        >
                        <div class="mg-pro-carousel__content">
                            <h3 class="mg-pro-carousel__title"><?php echo esc_html($image->post_title); ?></h3>
                            <p class="mg-pro-carousel__caption"><?php echo esc_html(wp_trim_words($image->post_content, 15)); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mg-pro-carousel__thumbs">
                <?php foreach ($images as $thumb): ?>
                <img 
                    class="mg-pro-carousel__thumb" 
                    src="<?php echo esc_url(wp_get_attachment_image_url($thumb->ID, 'thumbnail')); ?>" 
                    alt="<?php echo esc_attr(get_post_meta($thumb->ID, '_wp_attachment_image_alt', true)); ?>"
                >
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}