<?php
if (! defined('ABSPATH')) {
    exit;
}
/**
 * Mini Gallery Preview Template
 * Displays gallery preview in isolation
 */

// Get and validate parameters
$gallery_id = isset($_GET['gallery_id']) ? absint($_GET['gallery_id']) : 0;

if (
    !$gallery_id ||
    !isset($_GET['_wpnonce']) ||
    !wp_verify_nonce($_GET['_wpnonce'], 'mgwpp_preview')
) {
    status_header(403);
    wp_die(esc_html__('Invalid gallery preview request', 'mini-gallery'));
}

// Set preview mode constant
define('MGWPP_PREVIEW_MODE', true);

// Setup minimal environment
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php /* translators: %s: Gallery Title   */;?>
    <title><?php printf(esc_html__('Preview: %s', 'mini-gallery'), esc_html(get_the_title($gallery_id))); ?></title>

    <?php
    // Output necessary scripts and styles
    wp_head();
    ?>
</head>

<body>
    <div class="mgwpp-preview-wrapper">
        <?php
        // Render the gallery using the shortcode
        echo do_shortcode('[mgwpp_gallery id="' . $gallery_id . '"]');
        ?>
    </div>

    <?php wp_footer(); ?>
</body>

</html>