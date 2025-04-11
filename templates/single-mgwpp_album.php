<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $post;

// Optional: display album title
echo '<h1>' . get_the_title($post->ID) . '</h1>';

// Optional: display album content (if you’re putting the shortcode there)
echo apply_filters('the_content', $post->post_content);

// If you’re storing galleries via meta or relationship, get them here:
// Example: get a gallery shortcode from a custom field called 'mgwpp_gallery_shortcode'
$shortcode = get_post_meta($post->ID, 'mgwpp_gallery_shortcode', true);

if (!empty($shortcode)) {
    echo do_shortcode($shortcode);
}

// OR if you want to load images directly from a custom field
$images = get_post_meta($post->ID, 'mgwpp_album_images', true); // You’d need to set this in the post meta

if (!empty($images) && is_array($images)) {
    echo '<div class="mgwpp-album-gallery">';
    foreach ($images as $img_id) {
        $src = wp_get_attachment_image_src($img_id, 'large');
        echo '<img src="' . esc_url($src[0]) . '" alt="" />';
    }
    echo '</div>';
}

get_footer();
