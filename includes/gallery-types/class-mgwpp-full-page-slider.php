
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Full_Page_Slider {
    public static function render($post_id, $images, $settings = []) {
        ob_start(); ?>
        <div class="mg-fullpage-slider">
            <div class="mg-fullpage-viewport">
                <?php foreach ($images as $index => $image) : ?>
                    <div class="mg-fullpage-slide <?= $index === 0 ? 'mg-active' : '' ?>">
                        <div class="mg-fullpage-overlay"></div>
                        <img class="mg-fullpage-image" 
                             src="<?= wp_get_attachment_url($image->ID) ?>" 
                             alt="<?= esc_attr(get_post_meta($image->ID, '_wp_attachment_image_alt', true)) ?>">
                        <div class="mg-fullpage-content">
                            <h1 class="text-8xl font-bold mb-6"><?= get_the_title($image->ID) ?></h1>
                            <p class="text-xl opacity-80 mb-8"><?= get_post_field('post_content', $image->ID) ?></p>
                            <button class="mg-fullpage-buy">Explore Collection</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="mg-fullpage-nav mg-prev">❮</button>
            <button class="mg-fullpage-nav mg-next">❯</button>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>
