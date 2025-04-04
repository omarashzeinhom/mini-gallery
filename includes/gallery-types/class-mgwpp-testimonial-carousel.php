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
            '1.0.1'
        );

        wp_register_script(
            'mgwpp-testimonial-carousel',
            plugins_url('public/js/mgwpp-testimonial-carousel.js', __FILE__),
            ['jquery'],
            '1.0.1',
            true
        );
    }

    public static function shortcode_handler($atts) {
        $atts = shortcode_atts([
            'autoplay' => 'yes',
            'interval' => 5000,
            'count' => 5,
            'theme' => 'auto' // 'light', 'dark', or 'auto'
        ], $atts);

        $testimonials = get_posts([
            'post_type' => 'testimonial',
            'posts_per_page' => absint($atts['count']),
            'suppress_filters' => false
        ]);

        return self::render(0, $testimonials, [
            'autoplay' => $atts['autoplay'],
            'interval' => absint($atts['interval']),
            'theme' => $atts['theme']
        ]);
    }

    public static function render($post_id, $testimonials, $settings = []) {
        if (empty($testimonials)) return '';

        $default_settings = [
            'autoplay' => 'yes',
            'interval' => 5000,
            'theme' => 'auto'
        ];
        $settings = wp_parse_args($settings, $default_settings);

        wp_enqueue_style('mgwpp-testimonial-carousel-style');
        wp_enqueue_script('mgwpp-testimonial-carousel');

        // Generate a unique ID for this carousel
        $carousel_id = 'mgwpp-carousel-' . uniqid();
        
        // Determine theme class
        $theme_class = '';
        if ($settings['theme'] === 'dark') {
            $theme_class = 'theme-dark';
        }

        ob_start(); ?>
        <div id="<?php echo esc_attr($carousel_id); ?>" 
             class="mgwpp-testimonial-carousel <?php echo esc_attr($theme_class); ?>" 
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
                                    <?php echo get_the_post_thumbnail($testimonial->ID, 'thumbnail'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mgwpp-testimonial-content">
                                <blockquote>
                                    <?php echo wpautop($testimonial->post_content); ?>
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

            <button class="mgwpp-carousel-prev" aria-label="<?php esc_attr_e('Previous', 'mini-gallery'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button class="mgwpp-carousel-next" aria-label="<?php esc_attr_e('Next', 'mini-gallery'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            
            <?php if ($settings['theme'] === 'auto') : ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const carousel = document.getElementById('<?php echo esc_js($carousel_id); ?>');
                    const savedTheme = localStorage.getItem('dashboard-theme');
                    
                    if (savedTheme === 'dark' || 
                        (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        carousel.classList.add('theme-dark');
                    }
                    
                    // Listen for theme changes from dashboard
                    window.addEventListener('storage', function(e) {
                        if (e.key === 'dashboard-theme') {
                            if (e.newValue === 'dark') {
                                carousel.classList.add('theme-dark');
                            } else {
                                carousel.classList.remove('theme-dark');
                            }
                        }
                    });
                });
            </script>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

MGWPP_Testimonial_Carousel::init();

