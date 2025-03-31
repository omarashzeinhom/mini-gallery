<?php
class MGWPP_Album_Display
{
    public static function render_album($post_id)
    {
        add_action('wp_footer', [__CLASS__, 'init_lightbox'], 100);

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

                    $output .= sprintf(
                        '<a href="%s" class="mgwpp-gallery-item" 
                            data-caption="%s" 
                            data-gallery="gallery-%d"
                            aria-label="%s">%s</a>',
                        esc_url($full_src[0]),
                        esc_attr($caption),
                        $gallery_id,
                        esc_attr(sprintf(__('View image %d', 'mini-gallery'), $index + 1)),
                        wp_get_attachment_image(
                            $attachment->ID,
                            'medium',
                            false,
                            [
                                'loading' => 'lazy',
                                'class' => 'mgwpp-album-thumbnail'
                            ]
                        )
                    );
                }

                $output .= '</div></div>';
            }
        }
        $output .= '</div>';

        return $output;
    }

    public static function init_lightbox()
    {
        ?>
        <div id="mgwpp-lightbox" class="mgwpp-lightbox">
            <span class="mgwpp-close">&times;</span>
            <div class="mgwpp-lightbox-content">
                <?php echo wp_get_attachment_image(0, 'full', false, ['class' => 'mgwpp-lightbox-image']); ?>
                <div class="mgwpp-lightbox-caption"></div>
            </div>
            <a class="mgwpp-prev">&#10094;</a>
            <a class="mgwpp-next">&#10095;</a>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lightbox = document.getElementById('mgwpp-lightbox');
            const body = document.body;
            const items = Array.from(document.querySelectorAll('.mgwpp-gallery-item'));
            let currentIndex = 0;
    
            function updateBodyScroll(state) {
                body.classList[state ? 'add' : 'remove']('lightbox-open');
            }
    
            function openLightbox(index) {
                currentIndex = index;
                const item = items[index];
                const img = lightbox.querySelector('.mgwpp-lightbox-image');
                img.src = item.href;
                img.alt = item.querySelector('img').alt;
                lightbox.querySelector('.mgwpp-lightbox-caption').textContent = item.dataset.caption;
                lightbox.classList.add('active');
                updateBodyScroll(true);
            }
    
            function closeLightbox() {
                lightbox.classList.remove('active');
                updateBodyScroll(false);
            }
    
            function navigate(direction) {
                currentIndex = (currentIndex + direction + items.length) % items.length;
                openLightbox(currentIndex);
            }
    
            // Event listeners
            items.forEach((item, index) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    openLightbox(index);
                });
            });
    
            lightbox.querySelector('.mgwpp-close').addEventListener('click', closeLightbox);
            lightbox.querySelector('.mgwpp-prev').addEventListener('click', () => navigate(-1));
            lightbox.querySelector('.mgwpp-next').addEventListener('click', () => navigate(1));
    
            document.addEventListener('keydown', (e) => {
                if (lightbox.classList.contains('active')) {
                    switch(e.key) {
                        case 'Escape':
                            closeLightbox();
                            break;
                        case 'ArrowLeft':
                            navigate(-1);
                            break;
                        case 'ArrowRight':
                            navigate(1);
                            break;
                    }
                }
            });
    
            // Close when clicking outside image
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });
        });
        </script>
        <?php
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


