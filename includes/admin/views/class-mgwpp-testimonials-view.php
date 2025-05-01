<?php
if (!defined('ABSPATH')) {
    exit;
}
// File: includes/admin/views/class-mgwpp-testimonials-view.php
class MGWPP_Testimonials_View {

    public static function render() {
        $testimonials = self::get_testimonials();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Testimonials Management', 'mini-gallery') ?></h1>
            
            <?php self::render_create_button(); ?>
            
            <div class="mgwpp-table-container">
                <?php self::render_table($testimonials); ?>
            </div>
        </div>
        <?php
    }

    private static function get_testimonials() {
        return get_posts([
            'post_type'      => 'testimonial',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
    }

    private static function render_create_button() {
        ?>
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=testimonial')); ?>" 
           class="page-title-action">
            <?php esc_html_e('Add New', 'mini-gallery') ?>
        </a>
        <?php
    }

    private static function render_table($testimonials) {
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Author', 'mini-gallery') ?></th>
                    <th><?php esc_html_e('Position', 'mini-gallery') ?></th>
                    <th><?php esc_html_e('Content', 'mini-gallery') ?></th>
                    <th><?php esc_html_e('Actions', 'mini-gallery') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $testimonial) : ?>
                    <tr>
                        <?php self::render_row($testimonial); ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private static function render_row($testimonial) {
        $author = sanitize_text_field(
            get_post_meta($testimonial->ID, '_mgwpp_author', true)
        );
        
        $position = sanitize_text_field(
            get_post_meta($testimonial->ID, '_mgwpp_position', true)
        );
        ?>
        <td><?php echo esc_html($author) ?></td>
        <td><?php echo esc_html($position) ?></td>
        <td><?php echo wp_kses_post(wp_trim_words($testimonial->post_content, 20)) ?></td>
        <td><?php self::render_actions($testimonial->ID) ?></td>
        <?php
    }

    private static function render_actions($post_id) {
        ?>
        <div class="row-actions">
            <?php
            echo sprintf(
                '<a href="%s">%s</a> | ',
                esc_url(get_edit_post_link($post_id)),
                esc_html__('Edit', 'mini-gallery')
            );
            
            echo sprintf(
                '<a href="%s" class="delete">%s</a>',
                esc_url(wp_nonce_url(
                    admin_url("post.php?post={$post_id}&action=delete"),
                    "delete-post_{$post_id}"
                )),
                esc_html__('Delete', 'mini-gallery')
            );
            ?>
        </div>
        <?php
    }
}