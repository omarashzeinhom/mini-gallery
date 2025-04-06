<?php

class MGWPP_Testimonials {

    // Renders the Testimonials page for the admin dashboard
    public static function mgwpp_render_testimonials_page() {
        ?>
        <div id="mgwpp_testimonials_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Manage Testimonials', 'mini-gallery'); ?></h2>

            <div class="mgwpp-testimonial-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=testimonial')); ?>" class="button button-primary">
                    <?php echo esc_html__('Add New Testimonial', 'mini-gallery'); ?>
                </a>
            </div>

            <h3><?php echo esc_html__('Existing Testimonials', 'mini-gallery'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Author', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Position/Company', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Testimonial', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Actions', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all published testimonials
                    $testimonials = get_posts([
                        'post_type' => 'testimonial',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    ]);

                    // If testimonials exist, display them in a table
                    if ($testimonials) {
                        foreach ($testimonials as $testimonial) {
                            $author = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_author', true));
                            $position = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_position', true));
                            $content = wp_trim_words($testimonial->post_content, 20);
                    ?>
                            <tr>
                                <td><?php echo esc_html($author); ?></td>
                                <td><?php echo esc_html($position); ?></td>
                                <td><?php echo wp_kses_post($content); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($testimonial->ID)); ?>" class="button button-primary">
                                        <?php echo esc_html__('Edit', 'mini-gallery'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(
                                                    admin_url('post.php?post=' . $testimonial->ID . '&action=delete'),
                                                    'delete-post_' . $testimonial->ID
                                                )); ?>" class="button button-danger">
                                        <?php echo esc_html__('Delete', 'mini-gallery'); ?>
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="4">' . esc_html__('No testimonials found.', 'mini-gallery') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
?>
