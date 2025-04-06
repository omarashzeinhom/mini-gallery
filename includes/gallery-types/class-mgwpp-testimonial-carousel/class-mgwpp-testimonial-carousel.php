<?php
if (! defined('ABSPATH')) exit;
class MGWPP_Testimonial_Carousel
{

    public static function init()
    {
        add_action('init', [__CLASS__, 'register_image_sizes']);
        add_shortcode('mgwpp_testimonials', [__CLASS__, 'shortcode_handler']);
    }
    public static function register_image_sizes()
    {
        add_image_size('mgwpp-testimonial', 400, 400, true); // 400x400 cropped


    }

    public static function shortcode_handler($atts)
    {
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

    public static function render($post_id, $testimonials, $settings = [])
    {
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
<div id="<?php echo esc_attr($carousel_id); ?>" class="mgwpp-carousel-testimonials" data-autoplay="<?php echo esc_attr($settings['autoplay']); ?>" data-interval="<?php echo esc_attr($settings['interval']); ?>">
  <div class="mgwpp-carousel-testimonials-track">
    <?php foreach ($testimonials as $testimonial) :
      $author = get_post_meta($testimonial->ID, '_mgwpp_author', true);
      $position = get_post_meta($testimonial->ID, '_mgwpp_position', true);
    ?>
      <div class="mgwpp-carousel-testimonials-slide">
        <div class="mgwpp-carousel-testimonials-card">
          <?php if (has_post_thumbnail($testimonial->ID)) : ?>
            <div class="mgwpp-carousel-testimonials-image">
              <?php echo get_the_post_thumbnail(
                $testimonial->ID,
                'mgwpp-testimonial',
                [
                  'class'   => 'mgwpp-carousel-testimonials-img',
                  'loading' => 'lazy',
                  'alt'     => esc_attr(get_the_title($testimonial->ID))
                ]
              ); ?>
            </div>
          <?php endif; ?>
          <div class="mgwpp-carousel-testimonials-content">
            <blockquote><?php echo wpautop($testimonial->post_content); ?></blockquote>
            <div class="mgwpp-carousel-testimonials-meta">
              <?php if ($author) : ?>
                <span class="mgwpp-carousel-testimonials-author"><?php echo esc_html($author); ?></span>
              <?php endif; ?>
              <?php if ($position) : ?>
                <span class="mgwpp-carousel-testimonials-position"><?php echo esc_html($position); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <!-- Navigation Buttons -->
  <button class="mgwpp-carousel-testimonials-nav mgwpp-carousel-testimonials-prev" aria-label="Previous">
    <svg width="24" height="24" fill="none" stroke="currentColor">
      <path d="m15 18-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </button>
  <button class="mgwpp-carousel-testimonials-nav mgwpp-carousel-testimonials-next" aria-label="Next">
    <svg width="24" height="24" fill="none" stroke="currentColor">
      <path d="m9 18 6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </button>
</div>


    <?php
        return ob_get_clean();
    }


    public function init_carousel()
    {
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new TestimonialCarousel('.mgwpp-testimonial-carousel');
            });
        </script>
<?php
    }
}

MGWPP_Testimonial_Carousel::init();
