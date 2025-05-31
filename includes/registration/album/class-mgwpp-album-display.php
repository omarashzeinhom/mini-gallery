<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Album_Display
{



    public static function render_album($post_id)
    {
        $galleries = get_post_meta($post_id, '_mgwpp_album_galleries', true);


        if (! is_array($galleries) || empty($galleries)) {
            return '<p class="mgwpp-no-galleries">' . esc_html__('No galleries in this album.', 'mini-gallery') . '</p>';
        }

        // Verify nonce before using the gallery_id from the URL
        $current_gallery_id = 0;
        if (isset($_GET['gallery_id'])) {
            $gallery_id = sanitize_key(wp_unslash($_GET['gallery_id']));
        }

        if (isset($_GET['_mg_nonce'])) {
            $nonce = sanitize_key(wp_unslash($_GET['_mg_nonce']));
        }


        if (isset($gallery_id, $nonce) && wp_verify_nonce($nonce, 'mgwpp_view_gallery')) {
            $current_gallery_id = absint($gallery_id);
        }
        // Album Styles
        wp_enqueue_style(
            'mg-album-styles',
            MG_PLUGIN_URL . '/public/css/mgwpp-albums.css',
            [],
            filemtime(MG_PLUGIN_PATH . '/public/css/mgwpp-albums.css')
        );

        // Album Scripts
        wp_enqueue_script(
            'mg-albums-scripts',
            MG_PLUGIN_URL . '/public/js/mgwpp-albums.js',
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . '/public/js/mgwpp-albums.js'),
            true
        );

        // Localize script
        wp_localize_script('mg-albums-scripts', 'mgwpp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_nonce')
        ]);





        $album_url = get_permalink($post_id);
        $output    = '<div class="mgwpp-album-container">';

        if ($current_gallery_id && in_array($current_gallery_id, $galleries)) {
            // Single Gallery View
            $output .= self::render_single_gallery($current_gallery_id, $album_url);
        } else {
            // Album Gallery List View with Preview Images
            $output .= '<div class="mgwpp-album-gallery-list">';
            foreach ($galleries as $gallery_id) {
                $gallery = get_post($gallery_id);
                if (! $gallery || $gallery->post_type !== 'mgwpp_soora') {
                    continue;
                }

                // Get the first image from the gallery for preview
                $preview_image = self::get_gallery_preview_image($gallery_id);

                // Build the URL with gallery_id and nonce for security
                $gallery_url = add_query_arg([
                    'gallery_id' => $gallery_id,
                    '_mg_nonce'  => wp_create_nonce('mgwpp_view_gallery'),
                ], $album_url);

                // In render_album() method:
                $output .= sprintf(
                    '<div class="mgwpp-album-gallery-item">
        <a href="%s" class="mgwpp-album-gallery-link">
            %s
            <h3 class="mgwpp-album-gallery-title">%s</h3>
        </a>
    </div>',
                    esc_url(get_permalink($gallery_id)), // Direct link to single gallery
                    $preview_image,
                    esc_html($gallery->post_title)
                );
            }
            $output .= '</div>';
        }

        // Add lightbox HTML at the bottom
        $output .= self::get_lightbox_html();
        $output .= '</div>';

        return $output;
    }

    private static function get_gallery_preview_image($gallery_id)
    {
        $attachments = get_posts([
            'post_type'      => 'attachment',
            'posts_per_page' => 1,
            'post_parent'    => $gallery_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (! empty($attachments)) {
            return wp_get_attachment_image(
                $attachments[0]->ID,
                'large',
                false,
                [
                    'loading' => 'lazy',
                    'class'   => 'mgwpp-album-thumbnail',
                ]
            );
        }

        return '<div class="mgwpp-no-preview">' . esc_html__('No images', 'mini-gallery') . '</div>';
    }

    private static function render_single_gallery($gallery_id, $album_url)
    {
        $gallery = get_post($gallery_id);
        if (! $gallery || $gallery->post_type !== 'mgwpp_soora') {
            return '<p class="mgwpp-no-gallery">' . esc_html__('Gallery not found.', 'mini-gallery') . '</p>';
        }

        $attachments = get_posts([
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_parent'    => $gallery_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (empty($attachments)) {
            return '<p class="mgwpp-no-images">' . esc_html__('This gallery contains no images.', 'mini-gallery') . '</p>';
        }

        $output = sprintf(
            '<div class="mgwpp-gallery-container">
                <a href="%s" class="mgwpp-back-to-album">&larr; %s</a>
                <h3 class="mgwpp-gallery-title">%s</h3>
                <div class="mgwpp-gallery-grid">',
            esc_url($album_url),
            esc_html__('Back to Album', 'mini-gallery'),
            esc_html($gallery->post_title)
        );

        foreach ($attachments as $index => $attachment) {
            $full_src = wp_get_attachment_image_src($attachment->ID, 'full');
            $caption  = wp_get_attachment_caption($attachment->ID);

            $output .= sprintf(
                '<a href="%s" class="mgwpp-gallery-item" 
                    data-caption="%s" 
                    data-gallery="gallery-%d"
                    data-image-id="%d" 
                    aria-label="%s">%s</a>',
                esc_url($full_src[0]),
                esc_attr($caption),
                $gallery_id,
                $attachment->ID,
                esc_attr(sprintf('View image %d', $index + 1)),
                wp_get_attachment_image(
                    $attachment->ID,
                    'medium',
                    false,
                    [
                        'loading' => 'lazy',
                        'class'   => 'mgwpp-album-thumbnail',
                    ]
                )
            );
        }

        $output .= '</div></div>';
        return $output;
    }

    public static function get_lightbox_html()
    {
        ob_start();
?>
        <div id="mgwpp-lightbox" class="mgwpp-lightbox">
            <span class="mgwpp-close">&times;</span>
            <div class="mgwpp-lightbox-overlay"></div>
            <div class="mgwpp-lightbox-content">
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
        $atts = shortcode_atts(['id' => 0], $atts, 'mgwpp_album');
        return empty($atts['id']) ? '' : self::render_album($atts['id']);
    }
}

add_shortcode('mgwpp_album', ['MGWPP_Album_Display', 'album_shortcode']);

