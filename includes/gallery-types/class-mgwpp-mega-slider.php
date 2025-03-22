<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Mega_Slider
{
    public static function render($post_id, $images)
    {
        ob_start(); ?>
        <div class="mg-material-carousel">
            <div class="mg-carousel__viewport">
                <?php foreach ($images as $index => $image): ?>
                    <div class="mg-carousel__slide <?php echo $index === 0 ? 'mg-active' : ''; ?>">
                        <?php echo wp_get_attachment_image($image->ID, 'full', false, [
                            'class' => 'mg-carousel__image',
                            'alt' => esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true))
                        ]); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mg-carousel__controls">
                <button class="mg-carousel__nav mg-prev">‹</button>
                <button class="mg-carousel__nav mg-next">›</button>
                <div class="mg-dots-container"></div>
            </div>
        </div>
        <script>
            <?php include plugin_dir_path(__FILE__) . '../../public/js/mg-mega-carousel.js'; ?>
        </script>

<?php
        return ob_get_clean();
    }
}
