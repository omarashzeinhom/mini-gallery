<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Gallery_Grid {
    public static function render($post_id, $images) {
        ob_start(); ?>

        <div class="mgwpp-gallery-container">
            <!-- Layout Switch Buttons -->
            <div class="mgwpp-layout-controls">
                <button class="mgwpp-layout-btn active" data-layout="grid">Grid</button>
                <button class="mgwpp-layout-btn" data-layout="masonry">Masonry</button>
                <button class="mgwpp-layout-btn" data-layout="minimal">Minimal</button>
            </div>

            <!-- Image Grid Container -->
            <div class="mgwpp-grid-container" data-layout="grid">
                <?php foreach ($images as $image): ?>
                    <div class="mgwpp-grid-item">
                        <?= wp_get_attachment_image($image->ID, 'large', false, [
                            'class' => 'mgwpp-grid-image',
                            'loading' => 'lazy',
                            'data-full' => wp_get_attachment_image_url($image->ID, 'full')
                        ]) ?>
                        
                        <?php if ($caption = wp_get_attachment_caption($image->ID)): ?>
                            <div class="mgwpp-image-caption"><?= esc_html($caption) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function () {
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
