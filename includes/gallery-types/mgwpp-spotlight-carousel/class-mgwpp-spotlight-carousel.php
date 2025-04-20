<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Spotlight_Carousel
{
    public static function render($post_id, $images, $settings = [])
    {
        if (empty($images) || ! is_array($images)) {
            return '<div class="mgwpp-error">' . esc_html__('No images found in gallery', 'mini-gallery') . '</div>';
        }
        ob_start();
        ?>
        <div class="mgwpp-spotlight-carousel">
            <div class="mgwpp-light-overlay"></div>
            
            <div class="mgwpp-carousel-viewport">
                <?php foreach ($images as $index => $image) :
                    $image_id   = is_object($image) ? $image->ID : $image;
                    $image_data = wp_get_attachment_image_src($image_id, 'large');
                    $image_url  = esc_url($image_data[0]);
                    $image_alt  = esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true));
                    ?>
                    <div class="mgwpp-carousel-slide <?php echo ( $index === 0 ? 'mgwpp-active' : '' ); ?>">
                        <div class="mgwpp-slide-content">
                            <div class="mgwpp-text-content">
                                <?php if (get_the_title($image_id)) : ?>
                                    <h1 class="mgwpp-slide-title"><?php echo esc_html(get_the_title($image_id)); ?></h1>
                                <?php endif; ?>
                                <?php if (get_post_field('post_content', $image_id)) : ?>
                                    <p class="mgwpp-slide-subtitle"><?php echo wp_kses_post(get_post_field('post_content', $image_id)); ?></p>
                                <?php endif; ?>
                                <button class="mgwpp-cta-button"><?php echo esc_html__('Discover More', 'mini-gallery'); ?></button>
                            </div>
                            <div class="mgwpp-image-container">
                                <?php echo wp_get_attachment_image(
                                    $image_id,
                                    'large',
                                    false,
                                    [
                                    'class'   => 'mgwpp-carousel-image',
                                    'loading' => 'lazy',
                                    'alt'     => $image_alt,
                                     ]
                                ); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mgwpp-carousel-nav">
                <?php foreach ($images as $index => $image) : ?>
                    <button class="mgwpp-nav-btn <?php echo ( $index === 0 ? 'mgwpp-active' : '' ); ?>" 
                            data-index="<?php echo esc_attr($index); ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
