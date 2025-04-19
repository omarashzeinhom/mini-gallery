<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Single {
    /**
     * Render the single gallery.
     *
     * @param int   $post_id  Post ID (optional, for extended use).
     * @param array $images   Array of image objects.
     * @param array $settings Optional settings for custom behavior.
     *
     * Supported $settings keys:
     *   - bg_color: Background color for the gallery container (default: transparent).
     *   - transition_speed: Slide fade transition speed (default: 0.5s).
     *   - auto_rotate_speed: Auto-rotate interval in milliseconds (default: 5000).
     *   - show_nav: Whether to show navigation buttons (default: true).
     *   - swipe_threshold: Pixel threshold for swipe detection (default: 30).
     *
     * @return string HTML output of the gallery.
     */
    public static function render($post_id, $images, $settings = []) {
        wp_enqueue_style('mgwpp-single-carousel');
        wp_enqueue_script('mgwpp-single-carousel');
    
        $defaults = [
            'bg_color' => 'transparent',
            'transition_speed' => '0.5s',
            'auto_rotate_speed' => 5000,
            'show_nav' => true,
            'swipe_threshold' => 30
        ];
        $settings = wp_parse_args($settings, $defaults);
    
        ob_start(); ?>
        <div class="mgwpp-single-carousel" 
             data-auto-rotate="<?php echo esc_attr($settings['auto_rotate_speed']); ?>"
             data-swipe-threshold="<?php echo esc_attr($settings['swipe_threshold']); ?>"
             style="--mgwpp-bg-color: <?php echo esc_attr($settings['bg_color']); ?>;
                   --mgwpp-transition-speed: <?php echo esc_attr($settings['transition_speed']); ?>">
            
            <div class="mgwpp-single-carousel__container">
                <?php if (!empty($images)) : ?>
                    <div class="mgwpp-single-carousel__main">
                        <?php foreach ($images as $index => $image) : 
                            // Ensure we have a valid attachment ID
                            $image_id = is_object($image) ? $image->ID : $image;
                            ?>
                            <div class="mgwpp-single-carousel__slide <?php echo $index === 0 ? 'mgwpp-single-carousel__slide--active' : ''; ?>">
                                <?php echo wp_get_attachment_image($image_id, 'large', false, [
                                    'class' => 'mgwpp-single-carousel__image',
                                    'loading' => 'eager',
                                    'data-caption' => esc_attr(wp_get_attachment_caption($image_id))
                                ]); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($settings['show_nav']) : ?>
                        <div class="mgwpp-single-carousel__controls">
                            <button class="mgwpp-single-carousel__nav mgwpp-single-carousel__nav--prev" aria-label="Previous">❮</button>
                            <div class="mgwpp-single-carousel__counter">1/<?php echo count($images); ?></div>
                            <button class="mgwpp-single-carousel__nav mgwpp-single-carousel__nav--next" aria-label="Next">❯</button>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="mgwpp-single-carousel__empty"><?php esc_html_e('No images found', 'mini-gallery'); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
