<?php
if (!defined('ABSPATH')) exit;

get_header();

// Debug: show current post ID and meta
$post_id = get_the_ID();

// Enqueue lightbox assets via shortcode handler if needed

if (have_posts()) :
    while (have_posts()) : the_post();
        echo '<div class="mgwpp-album">';

        // Album title and content
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';
        echo '<div class="mgwpp-album-content">';
        echo apply_filters('the_content', get_the_content());
        echo '</div>';

        // Fetch related galleries - FIXED META KEY
        $related_galleries = get_post_meta($post_id, '_mgwpp_album_galleries', true); // Note the underscore prefix

        // Debug output
        echo '<pre>Album ID: ' . $post_id . '</pre>';
        echo '<pre>Galleries Meta: ' . print_r($related_galleries, true) . '</pre>';

        if (!empty($related_galleries) && is_array($related_galleries)) {
            echo '<div class="mgwpp-album-galleries">';
            foreach ($related_galleries as $gallery_id) {
                $gallery_id = absint($gallery_id);
                $gallery = get_post($gallery_id);
                
                // Debug gallery data
                echo '<pre>Processing Gallery ID: ' . $gallery_id . '</pre>';
                echo '<pre>Gallery Status: ' . ($gallery ? $gallery->post_status : 'INVALID') . '</pre>';

                if ($gallery && $gallery->post_type === 'mgwpp_soora' && $gallery->post_status === 'publish') {
                    // Output gallery using shortcode
                    echo '<div class="mgwpp-album-gallery-item">';
                    echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
                    echo '</div>';
                } else {
                    echo '<p class="mgwpp-invalid-gallery">Invalid gallery ID: ' . $gallery_id . '</p>';
                }
            }
            echo '</div>';
        } else {
            echo '<p class="mgwpp-no-galleries">No galleries found in this album.</p>';
        }

        echo '</div>'; // .mgwpp-album
    endwhile;
endif;

get_footer();