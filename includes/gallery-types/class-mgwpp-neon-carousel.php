<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MGWPP_Neon_Carousel {
    public static function render($post_id, $images) {
        // Validate inputs
        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return esc_html__('Invalid gallery ID', 'mini-gallery');
        }

        if (!is_array($images) || empty($images)) {
            return esc_html__('No images found in gallery', 'mini-gallery');
        }

        // Generate secure unique ID
        $unique_id = 'neon-slider-' . $post_id . '-' . uniqid();

        ob_start();
        ?>
        <div class="neon-slider" id="<?php echo esc_attr(sanitize_key($unique_id)); ?>">
            <div class="neon-slides">
                <?php foreach ($images as $index => $image) : 
                    if (!isset($image->ID)) continue;
                    $image_id = absint($image->ID);
                    $alt_text = sanitize_text_field(
                        get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: ''
                    );
                ?>
                <div class="neon-slide <?php echo esc_attr(($index === 0) ? 'first-neon-slide active' : ''); ?>">
                    <?php echo wp_get_attachment_image(
                        $image_id,
                        'full',
                        false,
                        [
                            'alt'   => $alt_text,
                            'class' => 'neon-slide-image',
                            'loading' => 'lazy',
                            'decoding' => 'async'
                        ]
                    ); ?>
                    <div class="neon-slide-content">
                        <div class="neon-header">
                            <?php if ($post_title = get_the_title($post_id)) : ?>
                            <h2 class="neon-title"><?php echo esc_html(sanitize_text_field($post_title)); ?></h2>
                            <?php endif; ?>
                            <div class="neon-preview-images">
                                <?php foreach ($images as $thumb_index => $thumb) :
                                    if (!isset($thumb->ID)) continue;
                                    $thumb_id = absint($thumb->ID);
                                    $thumb_alt = sanitize_text_field(
                                        get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: ''
                                    );
                                ?>
                                <img 
                                    src="<?php echo esc_url(wp_get_attachment_image_url($thumb_id, 'thumbnail')); ?>" 
                                    alt="<?php echo esc_attr($thumb_alt); ?>"
                                    data-slide-index="<?php echo absint($thumb_index); ?>"
                                    class="neon-preview-thumb"
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
        <?php
        
        $output = ob_get_clean();
        
        // Allow safe HTML elements
        return wp_kses($output, [
            'div' => [
                'class' => true,
                'id' => true,
                'data-*' => true
            ],
            'img' => [
                'src' => true,
                'alt' => true,
                'class' => true,
                'loading' => true,
                'decoding' => true,
                'data-slide-index' => true
            ],
            'h2' => ['class' => true],
            // Add other allowed tags as needed
        ]);
    }
}