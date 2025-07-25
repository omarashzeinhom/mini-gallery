<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Testimonials_View
{

    public static function render()
    {
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

    private static function get_testimonials()
    {
        return get_posts([
            'post_type'      => 'mgwpp_testimonial',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
    }

    private static function render_create_button()
    {
        ?>
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=mgwpp_testimonial')); ?>"
            class="page-title-action">
            <?php esc_html_e('Add New', 'mini-gallery') ?>
        </a>
        <?php
    }

    private static function render_table($testimonials)
    {
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

    private static function render_row($testimonial)
    {
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


    private static function render_actions($post_id)
    {
        ?>
        <div class="row-actions">
            <?php
            $edit_url = get_edit_post_link($post_id);
            $can_edit = current_user_can('edit_post', $post_id);

            // Debug output
            echo "<!-- Edit Capability: " . ($can_edit ? 'YES' : 'NO') . " -->";

            if ($can_edit) {
                echo sprintf(
                    '<a href="%s">%s</a> | ',
                    esc_url($edit_url), // FIX: Added esc_url()
                    esc_html__('Edit', 'mini-gallery')
                );
            }

            // Delete link with proper capability check
            $delete_url = wp_nonce_url(
                admin_url("post.php?post={$post_id}&action=delete"),
                "delete-post_{$post_id}"
            );
            $can_delete = current_user_can('delete_post', $post_id);

            // Debug output
            echo "<!-- Delete Capability: " . ($can_delete ? 'YES' : 'NO') . " -->";

            if ($can_delete) {
                echo sprintf(
                    '<a href="%s" class="submitdelete">%s</a>',
                    esc_url($delete_url), // FIX: Added esc_url()
                    esc_html__('Delete', 'mini-gallery')
                );
            }
            ?>
        </div>
        <?php
    }
}
?>