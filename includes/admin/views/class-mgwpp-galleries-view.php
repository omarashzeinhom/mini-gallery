<?php
if (!defined('ABSPATH')) exit;

class Galleries_View {
    public static function render() {
        // Galleries page rendering logic
    }
}
?>

<div class="wrap mgwpp-admin">
    <h1><?php esc_html_e('Gallery Management', 'mini-gallery'); ?></h1>
    
    <?php MGWPP_Admin_Notices::display(); ?>
    
    <div class="mgwpp-columns">
        <div class="mgwpp-main-col">
            <!-- Gallery Creation Form -->
            <form method="post">
                <?php wp_nonce_field('mgwpp_gallery_creation'); ?>
                
                <div class="mgwpp-form-section">
                    <label for="gallery_title">
                        <?php esc_html_e('Gallery Title:', 'mini-gallery'); ?>
                    </label>
                    <input type="text" id="gallery_title" name="gallery_title" required>
                </div>

                <!-- Image Uploader Component -->
                <?php MGWPP_Media_Uploader::render(); ?>

                <!-- Gallery Type Selector -->
                <?php MGWPP_Gallery_Type_Selector::render(); ?>

                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Create Gallery', 'mini-gallery'); ?>
                </button>
            </form>
        </div>

        <div class="mgwpp-side-col">
            <!-- Live Preview Area -->
            <div id="mgwpp-gallery-preview"></div>
        </div>
    </div>

    <!-- Existing Galleries Table -->
    <?php MGWPP_Table_Builder::render_galleries_table($galleries); ?>
</div>