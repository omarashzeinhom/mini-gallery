<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Grid
{
    public static function render($post_id, $images)
    {
        ob_start(); ?>

        <div class="mgwpp-gallery-container">
            <!-- Layout Switch Buttons -->
            <div class="mgwpp-layout-controls">
                <button class="mgwpp-layout-btn active" data-layout="grid" aria-label="Grid layout">
                    <img
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/public/front-end/icons/layout-grid.webp'); ?>"
                        alt="Grid Layout"
                        width="24" height="24" />
                </button>
                <button class="mgwpp-layout-btn" data-layout="masonry" aria-label="Masonry layout">
                    <img
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/public/front-end/icons/layout-masonry.webp'); ?>"
                        alt="Masonry Layout"
                        width="24" height="24" />
                </button>
                <button class="mgwpp-layout-btn" data-layout="minimal" aria-label="Minimal layout">
                    <img
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/public/front-end/icons/layout-minimal.webp'); ?>"
                        alt="Minimal Layout"
                        width="24" height="24" />
                </button>
            </div>

        </div>

        <!-- Image Grid Container -->
        <div class="mgwpp-grid-container" data-layout="grid">
            <?php foreach ($images as $image) : ?>
                <div class="mgwpp-grid-item">
                    <?php
                    // Get the image HTML
                    $image_html = wp_get_attachment_image($image->ID, 'large', false, [
                        'class'     => 'mgwpp-grid-image',
                        'loading'   => 'lazy',
                        'data-full' => esc_url(wp_get_attachment_image_url($image->ID, 'full')),
                    ]);

                    echo wp_kses_post($image_html);

                    if ($caption = wp_get_attachment_caption($image->ID)) :
                        ?>
                        <div class="mgwpp-image-caption"><?php esc_html($caption); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>


        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const layoutBtns = document.querySelectorAll(".mgwpp-layout-btn");
                const gridContainer = document.querySelector(".mgwpp-grid-container");

                layoutBtns.forEach(btn => {
                    btn.addEventListener("click", () => {
                        layoutBtns.forEach(b => b.classList.remove("active"));
                        btn.classList.add("active");

                        const layout = btn.getAttribute("data-layout");
                        gridContainer.setAttribute("data-layout", layout);
                    });
                });
            });
        </script>

        <?php return ob_get_clean();
    }
}
