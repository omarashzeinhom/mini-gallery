<?php
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in preview mode
$is_preview = defined('MGWPP_PREVIEW_MODE') && MGWPP_PREVIEW_MODE;

if (!$is_preview) {
    get_header();
}

echo '<div class="mgwpp-single-gallery-container">';

// Gallery title
echo '<h1 class="mgwpp-gallery-title">' . esc_html(get_the_title()) . '</h1>';

// Render gallery using shortcode
echo do_shortcode('[mgwpp_gallery id="' . get_the_ID() . '"]');

// Back to album link (only in normal mode)
$album_id = get_post_meta(get_the_ID(), '_mgwpp_parent_album', true);
if ($album_id && !$is_preview) {
    echo '<a href="' . esc_url(get_permalink($album_id)) . '" class="mgwpp-back-to-album">';
    echo '&larr; ' . esc_html__('Back to Album', 'mini-gallery');
    echo '</a>';
}

echo '</div>';

if (!$is_preview) {
    get_footer();
}
