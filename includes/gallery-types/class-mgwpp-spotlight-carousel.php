<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Spotlight_Carousel {
    public static function render($post_id, $images, $settings = []) {
        if (empty($images) || !is_array($images)) {
            return '';
        }

        ob_start(); ?>
        <div class="spotlight-carousel">
            <div class="light-overlay"></div>
            
            <div class="carousel-viewport">
                <?php foreach ($images as $index => $image) : 
                    $image_id = is_object($image) ? $image->ID : $image;
                    $image_url = esc_url(wp_get_attachment_url($image_id));
                    $image_alt = esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true));
                    $title = esc_html(get_the_title($image_id));
                    $content = wp_kses_post(get_post_field('post_content', $image_id));
                ?>
                    <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="slide-content">
                            <div class="text-content">
                                <h1 class="slide-title"><?php echo $title; ?></h1>
                                <p class="slide-subtitle"><?php echo $content; ?></p>
                                <button class="cta-button">Discover More</button>
                            </div>
                            <div class="image-container">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="carousel-nav">
                <?php foreach ($images as $index => $image) : ?>
                    <button class="nav-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                            data-index="<?php echo $index; ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}
?>