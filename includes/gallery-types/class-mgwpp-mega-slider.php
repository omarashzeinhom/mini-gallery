<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Mega_Slider
{
    public static function render($post_id, $images)
    {
        ob_start(); ?>
        <div class="mg-mega-carousel">
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
                <button class="mg-carousel__nav mg-prev" aria-label="Previous slide">
                    <svg class="mg-carousel__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M15 18l-6-6 6-6" />
                    </svg>
                </button>

                <button class="mg-carousel__nav mg-next" aria-label="Next slide">
                    <svg class="mg-carousel__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M9 18l6-6-6-6" />
                    </svg>
                </button>

                <div class="mg-dots-container"></div>
            </div>
        </div>

<?php
        return ob_get_clean();
    }
}
