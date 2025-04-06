<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Full_Page_Slider
{
    public static function render($post_id, $images, $settings = [])
    {
        if (empty($images) || ! is_array($images)) {
            return ''; // Exit early if images are not valid
        }

        ob_start(); ?>
        <div class="mg-fullpage-slider">
            <div class="mg-fullpage-viewport">
                <?php foreach ($images as $index => $image) :
                    $image_id      = isset($image->ID) ? intval($image->ID) : 0;
                    $image_url_raw = $image_id ? wp_get_attachment_url($image_id) : '';
                    $image_url     = $image_id ? esc_url($image_url_raw) : '';
                    $image_alt_raw = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
                    $image_alt     = $image_id ? esc_attr($image_alt_raw) : '';
                    $image_title_raw   = $image_id ? get_the_title($image_id) : '';
                    $image_title   = $image_id ? esc_html($image_title_raw) : '';
                    $image_content_raw = $image_id ? get_post_field('post_content', $image_id) : '';
                    $image_content = $image_id ? wp_kses_post($image_content_raw) : '';
                ?>
                    <div class="mg-fullpage-slide <?php echo ($index === 0 ? 'mg-active' : ''); ?>">
                        <div class="mg-fullpage-overlay"></div>
                        <?php if ($image_url) : ?>
                            <img class="mg-fullpage-image"
                                src="<?php echo esc_url($image_url); ?>"
                                alt="<?php echo esc_attr($image_alt); ?>">
                        <?php endif; ?>
                        <div class="mg-fullpage-content">
                            <?php if ($image_title) : ?>
                                <h1 class="text-8xl font-bold mb-6"><?php echo esc_html($image_title); ?></h1>
                            <?php endif; ?>
                            <?php if ($image_content) : ?>
                                <p class="text-xl opacity-80 mb-8"><?php echo wp_kses_post($image_content); ?></p>
                            <?php endif; ?>
                            <button class="mg-fullpage-buy"><?php echo esc_html__('Explore Collection', 'mini-gallery'); ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="mg-fullpage-nav mg-prev">❮</button>
            <button class="mg-fullpage-nav mg-next">❯</button>
        </div>
<?php
        return ob_get_clean();
    }
}
?>