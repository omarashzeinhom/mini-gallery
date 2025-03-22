<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MGWPP_Neon_Carousel {
   // Change the MGWPP_Neon_Carousel render method to:
public static function render($post_id, $images) {
    ob_start(); 
    $unique_id = 'neon-slider-' . bin2hex(random_bytes(4)); // Generate unique ID
    ?>
    
    <div class="neon-slider" id="<?php echo $unique_id; ?>">
        <div class="neon-slides">
            <?php foreach ($images as $index => $image): ?>
            <div class="neon-slide <?php echo $index === 0 ? 'first-neon-slide active' : ''; ?>">
                <?php echo wp_get_attachment_image($image->ID, 'full', false, [
                    'alt' => esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true)),
                    'class' => 'neon-slide-image'
                ]); ?>
                <div class="neon-slide-content">
                    <div class="neon-header">
                        <h2 class="neon-title"><?php echo esc_html(get_the_title($post_id)); ?></h2>
                        <div class="neon-preview-images">
                            <?php foreach ($images as $thumb_index => $thumb): ?>
                            <img 
                                src="<?php echo esc_url(wp_get_attachment_image_url($thumb->ID, 'thumbnail')); ?>" 
                                alt="<?php echo esc_attr(get_post_meta($thumb->ID, '_wp_attachment_image_alt', true)); ?>"
                                data-slide-index="<?php echo $thumb_index; ?>"
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

    <script>
        (function() {
            const sliderContainer = document.getElementById('<?php echo $unique_id; ?>');
            new NeonSlider(sliderContainer);
        })();
    </script>
    
    <?php
    return ob_get_clean();
}
}