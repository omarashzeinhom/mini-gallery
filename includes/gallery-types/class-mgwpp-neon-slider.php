<?php
class MGWPP_Neon_Slider {
    public static function render($post_id, $images) {
        ob_start(); ?>
        
        <style><?php include plugin_dir_path(__FILE__) . '../../public/css/neon-slider.css'; ?></style>
        
        <div class="hero-slider">
            <div class="slides">
                <?php foreach ($images as $index => $image): ?>
                <div class="slide <?php echo $index === 0 ? 'first-slide active' : ''; ?>">
                    <?php echo wp_get_attachment_image($image->ID, 'full', false, [
                        'alt' => esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true))
                    ]); ?>
                    <div class="slide-content">
                        <div class="property-header">
                            <h2 class="property-title"><?php echo get_the_title($post_id); ?></h2>
                            <div class="preview-images">
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

        <script><?php include plugin_dir_path(__FILE__) . '../../public/js/neon-slider.js'; ?></script>
        
        <?php
        return ob_get_clean();
    }
}