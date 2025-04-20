<?php
if (!defined('ABSPATH')) exit;

get_header();

// Get current gallery ID
$gallery_id = get_the_ID();

// Enqueue lightbox assets
wp_enqueue_style('mgwpp-lightbox', MG_PLUGIN_URL . '/public/css/lightbox.css');
wp_enqueue_script('mgwpp-lightbox', MG_PLUGIN_URL . '/public/js/lightbox.js', ['jquery'], null, true);

echo '<div class="mgwpp-single-gallery-container">';

// Gallery title
echo '<h1 class="mgwpp-gallery-title">' . esc_html(get_the_title()) . '</h1>';

// Render gallery using shortcode
echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');

// Back to album link
$album_id = get_post_meta($gallery_id, '_mgwpp_parent_album', true);
if ($album_id) {
    echo '<a href="' . esc_url(get_permalink($album_id)) . '" class="mgwpp-back-to-album">';
    echo '&larr; ' . esc_html__('Back to Album', 'mini-gallery');
    echo '</a>';
}

echo '</div>';

get_footer();