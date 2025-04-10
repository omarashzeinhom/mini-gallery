<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Gallery_Grid {
    public static function render($post_id, $images) {
        // Get unique categories
        $categories = self::get_image_categories($images);
        
        ob_start(); ?>
        
        <div class="mgwpp-gallery-container multi-carousel" data-auto-rotate="3000" data-images-per-page="3">
            <!-- Filter Controls -->
            <div class="mgwpp-filter-controls">
                <button class="mgwpp-filter-btn active" data-category="all">All</button>
                <?php foreach ($categories as $slug => $name): ?>
                    <button class="mgwpp-filter-btn" data-category="<?= esc_attr($slug) ?>">
                        <?= esc_html($name) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Carousel Slides Wrapper -->
            <div class="mgwpp-slides-wrapper">
                <?php foreach ($images as $index => $image): 
                    $terms = wp_get_post_terms($image->ID, 'media_category', ['fields' => 'slugs']);
                    $categories = !is_wp_error($terms) ? implode(' ', $terms) : '';
                ?>
                    <div class="mgwpp-grid-item <?= esc_attr($categories) ?>" 
                         data-index="<?= esc_attr($index) ?>"
                         data-categories="<?= esc_attr($categories) ?>">
                        <div class="grid-item">
                            <?= wp_get_attachment_image($image->ID, 'large', false, [
                                'loading' => 'lazy',
                                'class' => 'mgwpp-grid-gallery--image',
                                'data-full' => wp_get_attachment_image_url($image->ID, 'full')
                            ]) ?>
                            
                            <?php if ($caption = wp_get_attachment_caption($image->ID)): ?>
                                <div class="mgwpp-image-caption"><?= esc_html($caption) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php return ob_get_clean();
    }

    private static function get_image_categories($images) {
        $categories = [];
        foreach ($images as $image) {
            $terms = wp_get_post_terms($image->ID, 'media_category');
            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $categories[$term->slug] = $term->name;
                }
            }
        }
        return array_unique($categories);
    }
}
