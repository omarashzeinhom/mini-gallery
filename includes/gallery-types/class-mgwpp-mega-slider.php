<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MGWPP_Mega_Slider {
    public static function render($post_id, $images) {
        ob_start(); ?>
        
        <style><?php include plugin_dir_path(__FILE__) . '../../public/css/mega-slider.css'; ?></style>
        
        <div class="mg-mega-slider">
            <div class="mg-mega-slider__track">
                <?php foreach ($images as $index => $image): ?>
                <div class="mg-mega-slider__slide <?php echo $index === 0 ? 'first-slide active' : ''; ?>">
                    <?php echo wp_get_attachment_image($image->ID, 'full', false, [
                        'alt' => esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true))
                    ]); ?>
                    <div class="mg-mega-slider__content">
                        <div class="mg-mega-slider__header">
                            <h2 class="mg-mega-slider__title"><?php echo get_the_title($post_id); ?></h2>
                            <div class="mg-mega-slider__thumbs">
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
            <div class="mg-mega-slider__dots"></div>
            <button class="mg-mega-slider__nav mg-mega-slider__nav--prev">‹</button>
            <button class="mg-mega-slider__nav mg-mega-slider__nav--next">›</button>
        </div>

        <script><?php include plugin_dir_path(__FILE__) . '../../public/js/mega-slider.js'; ?></script>
        
        <?php
        return ob_get_clean();
    }
}