<?php

if (! defined('ABSPATH')) exit;

class MGWPP_Testimonial_Carousel {

    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_shortcode('mgwpp_testimonials', [__CLASS__, 'shortcode_handler']);
    }

    public static function register_assets() {
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

    public static function shortcode_handler($atts) {
        $atts = shortcode_atts([
            'autoplay' => 'yes',
            'interval' => 5000,
            'count' => 5
        ], $atts);

        $testimonials = get_posts([
            'post_type' => 'testimonial',
            'posts_per_page' => absint($atts['count']),
            'suppress_filters' => false
        ]);

        return self::render(0, $testimonials, [
            'autoplay' => $atts['autoplay'],
            'interval' => absint($atts['interval'])
        ]);
    }

    public static function render($post_id, $testimonials, $settings = []) {
        if (empty($testimonials)) return '';

        $default_settings = [
            'autoplay' => 'yes',
            'interval' => 5000,
        ];
        $settings = wp_parse_args($settings, $default_settings);

        wp_enqueue_style('mgwpp-testimonial-carousel-style');
        wp_enqueue_script('mgwpp-testimonial-carousel');

        ob_start(); ?>
        <div class="mgwpp-testimonial-carousel" 
             data-autoplay="<?php echo esc_attr($settings['autoplay']); ?>" 
             data-interval="<?php echo esc_attr($settings['interval']); ?>">
            
            <div class="mgwpp-carousel-inner">
                <?php foreach ($testimonials as $testimonial) : 
                    $author = get_post_meta($testimonial->ID, '_mgwpp_testimonial_author', true);
                    $position = get_post_meta($testimonial->ID, '_mgwpp_testimonial_position', true);
                ?>
                    <div class="mgwpp-carousel-item">
                        <div class="mgwpp-testimonial-card">
                            <?php if (has_post_thumbnail($testimonial->ID)) : ?>
                                <div class="mgwpp-testimonial-image">
                                    <?php echo get_the_post_thumbnail($testimonial->ID, 'medium'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mgwpp-testimonial-content">
                                <blockquote>
                                    <?php echo wpautop(esc_html($testimonial->post_content)); ?>
                                </blockquote>
                                
                                <div class="mgwpp-testimonial-meta">
                                    <?php if ($author) : ?>
                                        <div class="mgwpp-testimonial-author"><?php echo esc_html($author); ?></div>
                                    <?php endif; ?>
                                    <?php if ($position) : ?>
                                        <div class="mgwpp-testimonial-position"><?php echo esc_html($position); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="mgwpp-carousel-prev" aria-label="<?php esc_attr_e('Previous', 'mini-gallery'); ?>">&lt;</button>
            <button class="mgwpp-carousel-next" aria-label="<?php esc_attr_e('Next', 'mini-gallery'); ?>">&gt;</button>
        </div>
        <?php
        return ob_get_clean();
    }
}

MGWPP_Testimonial_Carousel::init();