<?php
if (!defined('ABSPATH')) exit;
class MGWPP_3D_Carousel
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_assets()
    {
        wp_register_style(
            'mg-3d-carousel-style',
            plugins_url('public/css/mg-threed-carousel.css', __FILE__),
            [],
            '1.0.0'
        );

        wp_register_script(
            'mg-3d-carousel',
            plugins_url('public/js/mg-threed-carousel.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public static function render($post_id, $images, $settings = [])
    {
        // Default settings with numeric values where applicable
        $default_settings = [
            'radius'         => 240,
            'auto_rotate'    => 'yes',
            'rotate_speed'   => -60,
            'img_width'      => '250px',
            'img_height'     => '250px',
            'bg_music'       => '',
            'bg_music_controls' => 'yes'
        ];

        $settings = wp_parse_args($settings, $default_settings);

        $radius         = absint($settings['radius']);
        $auto_rotate    = $settings['auto_rotate'] === 'yes';
        $rotate_speed   = absint($settings['rotate_speed']);
        $img_width      = absint($settings['img_width']);
        $img_height     = absint($settings['img_height']);
        $bg_music       = esc_url($settings['bg_music']);
        $bg_music_controls = $settings['bg_music_controls'] === 'yes';

        wp_enqueue_style('mg-3d-carousel-style');
        wp_enqueue_script('mg-3d-carousel');

        wp_localize_script('mg-3d-carousel', 'mg3dSettings_' . $post_id, [
            'radius'         => $radius,
            'autoRotate'     => $auto_rotate,
            'rotateSpeed'    => $rotate_speed,
            'imgWidth'       => $img_width,
            'imgHeight'      => $img_height,
            'bgMusic'        => $bg_music,
            'bgMusicControls' => $bg_music_controls
        ]);

        ob_start(); ?>
        <div class="mg-3d-carousel-container" data-post-id="<?php echo esc_attr($post_id); ?>">
            <div class="mg-drag-container">
                <div class="mg-spin-container">
                    <?php foreach ($images as $image) :
                        $img_url = wp_get_attachment_url($image->ID);
                        $img_alt = esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true));
                    ?>
                        <div class="mg-3d-item">
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo $img_alt; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mg-3d-ground"></div>
            </div>
            <?php if ($bg_music) : ?>
                <div class="mg-music-container">
                    <audio src="<?php echo esc_url($bg_music); ?>" <?php echo $bg_music_controls ? 'controls' : ''; ?> loop>
                        <p><?php esc_html_e('Your browser does not support the audio element.', 'mini-gallery'); ?></p>
                    </audio>
                </div>
            <?php endif; ?>
        </div>
<?php
        return ob_get_clean();
    }
}

MGWPP_3D_Carousel::init();
