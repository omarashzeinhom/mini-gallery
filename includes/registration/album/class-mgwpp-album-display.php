<?php
class MGWPP_Album_Display
{
    public static function render_album($post_id)
    {
        $galleries = get_post_meta($post_id, '_mgwpp_album_galleries', true);
        if (!is_array($galleries) || empty($galleries)) {
            return '<p class="mgwpp-no-galleries">' . esc_html__('No galleries in this album.', 'mini-gallery') . '</p>';
        }

        $output = '<div class="mgwpp-album-container">';
        foreach ($galleries as $gallery_id) {
            $gallery = get_post($gallery_id);
            if (!$gallery || $gallery->post_type !== 'mgwpp_soora') {
                continue;
            }

            // Get gallery images
            $attachments = get_posts(array(
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $gallery_id,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ));

            if (!empty($attachments)) {
                $output .= sprintf(
                    '<div class="mgwpp-gallery-container">
                        <h3 class="mgwpp-gallery-title">%s</h3>
                        <div class="mgwpp-gallery-grid">',
                    esc_html($gallery->post_title)
                );

                foreach ($attachments as $attachment) {
                    $image_src = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
                    $full_src = wp_get_attachment_image_src($attachment->ID, 'full');

                    if ($image_src) {
                        $output .= sprintf(
                            '<a href="%s" class="mgwpp-gallery-item" data-fancybox="gallery-%d">
                                <img src="%s" alt="%s">
                            </a>',
                            esc_url($full_src[0]),
                            $gallery_id,
                            esc_url($image_src[0]),
                            esc_attr(get_post_meta($attachment->ID, '_wp_attachment_image_alt', true))
                        );
                    }
                }

                $output .= '</div></div>';
            }
        }
        $output .= '</div>';

        return $output;
    }

    public static function album_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'mgwpp_album');

        if (empty($atts['id'])) {
            return '';
        }

        return self::render_album($atts['id']);
    }
}

// Register shortcode
add_shortcode('mgwpp_album', array('MGWPP_Album_Display', 'album_shortcode'));

// Add display styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'mgwpp-album-style',
        plugins_url('../../../../public/css/mg-album-styles.css', __FILE__)
    );
});
