<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Pro_Carousel
{
    public static function render($post_id, $images, $settings = [])
    {
        if (empty($images) || !is_array($images)) {
            return '<div class="mgwpp-pro-carousel__error">' . esc_html__('Add images to create a gallery', 'mini-gallery') . '</div>';
        }

        $placeholder = !empty($settings['placeholder_image']['url']) ? esc_url($settings['placeholder_image']['url']) : '';
        $image_size = !empty($settings['image_size']) ? sanitize_key($settings['image_size']) : 'large';

        ob_start();
        ?>
        <div class="mgwpp-pro-carousel" 
            data-carousel-id="<?php echo absint($post_id); ?>"
            role="region" 
            aria-label="<?php esc_attr_e('Image Carousel', 'mini-gallery'); ?>">
            
            <button class="mgwpp-pro-carousel__nav mgwpp-pro-carousel__nav--prev" 
                    aria-label="<?php esc_attr_e('Previous slide', 'mini-gallery'); ?>">
                ‹<span class="mgwpp-pro-carousel__screen-reader-text"><?php esc_html_e('Previous', 'mini-gallery'); ?></span>
            </button>
            <button class="mgwpp-pro-carousel__nav mgwpp-pro-carousel__nav--next" 
                    aria-label="<?php esc_attr_e('Next slide', 'mini-gallery'); ?>">
                ›<span class="mgwpp-pro-carousel__screen-reader-text"><?php esc_html_e('Next', 'mini-gallery'); ?></span>
            </button>
            
            <div class="mgwpp-pro-carousel__container">
                <div class="mgwpp-pro-carousel__track" role="list">
                    <?php foreach ($images as $image) :
                        if (empty($image->ID)) {
                            continue;
                        }
                        
                        $image_url = wp_get_attachment_image_url($image->ID, $image_size);
                        $image_alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
                        $image_url = $image_url ?: $placeholder;
                        $image_alt = $image_alt ?: esc_html__('Gallery image', 'mini-gallery');
                        ?>
                    <div class="mgwpp-pro-carousel__card" role="listitem">
                        <?php if ($image_url) : ?>
                            <img class="mgwpp-pro-carousel__image" 
                                src="<?php echo esc_url($image_url); ?>" 
                                alt="<?php echo esc_attr($image_alt); ?>"
                                loading="lazy"
                                <?php echo wp_get_attachment_image_srcset($image->ID, $image_size) ? 'srcset="' . esc_attr(wp_get_attachment_image_srcset($image->ID, $image_size)) . '"' : ''; ?>
                                sizes="(max-width: 768px) 100vw, 50vw">
                        <?php else : ?>
                            <div class="mgwpp-pro-carousel__placeholder">
                                <?php esc_html_e('Image missing', 'mini-gallery'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}