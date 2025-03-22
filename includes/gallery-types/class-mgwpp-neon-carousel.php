<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MGWPP_Neon_Carousel {
    public static function render($post_id, $images) {
        ob_start(); ?>
        
      
        <div class="neon-slider">
            <div class="neon-slides">
                <?php foreach ($images as $index => $image): ?>
                <div class="neon-slide <?php echo $index === 0 ? 'first-neon-slide active' : ''; ?>">
                    <?php echo wp_get_attachment_image($image->ID, 'full', false, [
                        'alt' => esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true))
                    ]); ?>
                    <div class="slide-content">
                        <div class="neon-header">
                            <h2 class="neon-title"><?php echo esc_html(get_the_title($post_id)); ?></h2>
                            <div class="neon-preview-images">
                                <?php foreach ($images as $thumb_index => $thumb): ?>
                                <img 
                                    src="<?php echo esc_url(wp_get_attachment_image_url($thumb->ID, 'thumbnail')); ?>" 
                                    alt="<?php echo esc_attr(get_post_meta($thumb->ID, '_wp_attachment_image_alt', true)); ?>"
                                >
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="dots-container"></div>
        </div>

        <script><?php include plugin_dir_path(__FILE__) . '../../public/js/mg-neon-carousel.js'; ?></script>
        
        <?php
        return ob_get_clean();
    }
}