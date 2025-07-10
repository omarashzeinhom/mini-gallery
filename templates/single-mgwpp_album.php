<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        echo '<div class="mgwpp-album">';

        // Album title
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';

        // Album content - properly escaped
        echo '<div class="mgwpp-album-content">';
        echo wp_kses_post(apply_filters('the_content', get_the_content()));
        echo '</div>';

        // Fetch related galleries
        $related_galleries = get_post_meta(get_the_ID(), '_mgwpp_album_galleries', true);

        if (!empty($related_galleries) && is_array($related_galleries)) {
            echo '<div class="mgwpp-album-galleries">';
            foreach ($related_galleries as $gallery_id) {
                $gallery_id = absint($gallery_id);
                $gallery = get_post($gallery_id);
                
                if ($gallery && $gallery->post_type === 'mgwpp_soora' && $gallery->post_status === 'publish') {
                    // Output gallery using shortcode
                    echo '<div class="mgwpp-album-gallery-item">';
                    echo do_shortcode('[mgwpp_gallery id="' . absint($gallery_id) . '"]');
                    echo '</div>';
                } else {
                    echo '<p class="mgwpp-invalid-gallery">' .
                        esc_html__('Invalid gallery ID:', 'mini-gallery') . ' ' .
                        absint($gallery_id) .
                        '</p>';
                }
            }
            echo '</div>';
        } else {
            echo '<p class="mgwpp-no-galleries">' . esc_html__('No galleries found in this album.', 'mini-gallery') . '</p>';
        }

        echo '</div>'; // .mgwpp-album
    endwhile;
endif;

get_footer();
