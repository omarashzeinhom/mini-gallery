<?php

if (! defined('ABSPATH')) exit;

class MGWPP_Testimonial_Carousel
{

    public static function init()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_assets()
    {
        wp_register_style(
            'mgwpp-testimonial-carousel-style',
            plugins_url('public/css/mgwpp-testimonial-carousel.css', __FILE__),
            [],
            '1.0.0'
        );

        wp_register_script(
            'mgwpp-testimonial-carousel',
            plugins_url('public/js/mgwpp-testimonial-carousel.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public static function render($post_id, $testimonials, $settings = [])
    {
        // Default settings
        $default_settings = [
            'autoplay' => 'yes',
            'interval' => 5000,
        ];
        $settings = wp_parse_args($settings, $default_settings);

        // Enqueue assets
        wp_enqueue_style('mgwpp-testimonial-carousel-style');
        wp_enqueue_script('mgwpp-testimonial-carousel');

        // Optionally, localize script settings if your JS uses them
        wp_localize_script('mgwpp-testimonial-carousel', 'mgwppTestimonialSettings_' . $post_id, [
            'autoplay' => $settings['autoplay'] === 'yes',
            'interval' => absint($settings['interval']),
        ]);

        ob_start(); ?>
        <div class="mgwpp-testimonial-carousel" data-autoplay="<?php echo esc_attr($settings['autoplay']); ?>" data-interval="<?php echo esc_attr($settings['interval']); ?>">
            <div class="mgwpp-carousel-inner">
                <div class="mgwpp-carousel-inner">
                    <?php foreach ($testimonials as $testimonial) : ?>
                        <div class="mgwpp-carousel-item">
                            <blockquote class="mgwpp-testimonial">
                                <p class="mgwpp-testimonial-content"><?php echo esc_html($testimonial->post_content); ?></p>
                                <footer class="mgwpp-testimonial-author">
                                    - <?php echo esc_html(get_post_meta($testimonial->ID, 'author', true)); ?>,
                                    <span><?php echo esc_html(get_post_meta($testimonial->ID, 'position', true)); ?></span>
                                </footer>
                            </blockquote>
                        </div>
                    <?php endforeach; ?>
                </div>


                <button class="mgwpp-carousel-prev">&lt;</button>
                <button class="mgwpp-carousel-next">&gt;</button>
            </div>
    <?php
        return ob_get_clean();
    }
}

MGWPP_Testimonial_Carousel::init();
