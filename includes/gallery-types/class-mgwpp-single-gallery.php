<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Single {
    public static function render($post_id, $images, $settings = []) {
        wp_enqueue_style('mgwpp-single-gallery');
        wp_enqueue_script('mgwpp-single-gallery');

        ob_start(); ?>
        
        <div class="mgwpp-single-gallery">
            <div class="gallery-container">
                <?php if (!empty($images) && is_array($images)) : ?>
                    <div class="main-image">
                        <?php foreach ($images as $index => $image) : ?>
                            <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <?php echo wp_get_attachment_image(
                                    $image->ID,
                                    'medium',
                                    false,
                                    [
                                        'loading' => 'lazy',
                                        'class' => 'slide-image',
                                        'data-caption' => esc_attr(wp_get_attachment_caption($image->ID))
                                    ]
                                ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="gallery-controls">
                        <button class="nav-prev">❮</button>
                        <div class="image-counter">1/<?php echo count($images); ?></div>
                        <button class="nav-next">❯</button>
                    </div>
                <?php else : ?>
                    <div class="no-images"><?php _e('No images found', 'mini-gallery'); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}