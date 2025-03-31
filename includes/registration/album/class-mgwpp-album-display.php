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
            if (!$gallery || $gallery->post_type !== 'mgwpp_soora') continue;

            $attachments = get_posts([
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $gallery_id,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ]);

            if (!empty($attachments)) {
                $output .= sprintf(
                    '<div class="mgwpp-gallery-container">
                        <h3 class="mgwpp-gallery-title">%s</h3>
                        <div class="mgwpp-gallery-grid">',
                    esc_html($gallery->post_title)
                );

                foreach ($attachments as $index => $attachment) {
                    $full_src = wp_get_attachment_image_src($attachment->ID, 'full');
                    $caption = wp_get_attachment_caption($attachment->ID);

                    // Generate image HTML with wp_get_attachment_image()
                    $image_html = wp_get_attachment_image(
                        $attachment->ID,
                        'medium', // You can change the size here as needed
                        false,
                        [
                            'loading' => 'lazy',
                            'class' => 'mgwpp-album-thumbnail'
                        ]
                    );

                    // Add the gallery item and image
                    $output .= sprintf(
                        '<a href="%s" class="mgwpp-gallery-item" 
                            data-caption="%s" 
                            data-gallery="gallery-%d"
                            data-image-id="%d" 
                            aria-label="%s">%s</a>',
                        esc_url($full_src[0]), // Use the full image URL for the lightbox
                        esc_attr($caption),
                        $gallery_id,
                        $attachment->ID,
                        esc_attr(sprintf(__('View image %d', 'mini-gallery'), $index + 1)),
                        $image_html // Use the generated image HTML here
                    );
                }

                $output .= '</div></div>';
            }
        }

        // Add lightbox HTML at the bottom
        $output .= self::get_lightbox_html();

        $output .= '</div>';

        return $output;
    }

    // Lightbox HTML structure (note no src in the image tag initially)
    public static function get_lightbox_html()
    {
        ob_start();
?>
        <div id="mgwpp-lightbox" class="mgwpp-lightbox">
            <span class="mgwpp-close">&times;</span>
            <div class="mgwpp-lightbox-overlay"></div> <!-- Overlay for album title -->
            <div class="mgwpp-lightbox-content">
                <!-- Image container where the image will be added dynamically -->
                <div class="mgwpp-lightbox-image-container"></div>
                <div class="mgwpp-lightbox-caption"></div>
            </div>
            <a class="mgwpp-prev">&#10094;</a>
            <a class="mgwpp-next">&#10095;</a>
        </div>
<?php
        return ob_get_clean();
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

add_shortcode('mgwpp_album', array('MGWPP_Album_Display', 'album_shortcode'));

// Enqueue the lightbox JavaScript file
function mgwpp_enqueue_lightbox_script()
{
    wp_enqueue_script('mgwpp-lightbox', get_template_directory_uri() . '/js/mg-lightbox.js', array(), 1.0, true);
}

add_action('wp_enqueue_scripts', 'mgwpp_enqueue_lightbox_script');
