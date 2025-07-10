<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_3D_Carousel
{

    public static function render($post_id, $images, $settings = [])
    {
        $default_settings = [
            'radius' => 240,
            'auto_rotate' => 'yes',
            'rotate_speed' => -60,
            'img_width' => 250,
            'img_height' => 250,
            'bg_music' => '',
            'bg_music_controls' => 'yes',
            'mobile_scale' => 0.7
        ];

        $settings = wp_parse_args($settings, $default_settings);

        wp_enqueue_style('mg-3d-carousel-style');
        wp_enqueue_script('mg-3d-carousel');

        wp_localize_script('mg-3d-carousel', 'mg3dSettings_' . $post_id, [
            'radius' => absint($settings['radius']),
            'autoRotate' => $settings['auto_rotate'] === 'yes',
            'rotateSpeed' => absint($settings['rotate_speed']),
            'imgWidth' => absint($settings['img_width']),
            'imgHeight' => absint($settings['img_height']),
            'bgMusic' => esc_url($settings['bg_music']),
            'bgMusicControls' => $settings['bg_music_controls'] === 'yes',
            'mobileScale' => floatval($settings['mobile_scale'])
        ]);

        ob_start(); ?>
        <div class="mg-3d-carousel-container" data-post-id="<?php echo esc_attr($post_id); ?>">
            <div class="mg-3d-reflection"></div>
            <div class="mg-drag-container">
                <div class="mg-spin-container">
                    <?php foreach ($images as $image) :
                        $img_alt = esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true));
                        ?>
                        <div class="mg-3d-item">
                            <?php echo wp_get_attachment_image(
                                $image->ID,
                                'full',
                                false,
                                [
                                    'alt' => $img_alt,
                                    'loading' => 'lazy',
                                    'class' => 'mg-carousel-image'
                                ]
                            ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($settings['bg_music']) : ?>
                <div class="mg-music-container">
                    <audio src="<?php echo esc_url($settings['bg_music']); ?>"
                        <?php echo $settings['bg_music_controls'] === 'yes' ? 'controls' : ''; ?>
                        loop>
                        <p><?php esc_html_e('Your browser does not support the audio element.', 'mini-gallery'); ?></p>
                    </audio>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
