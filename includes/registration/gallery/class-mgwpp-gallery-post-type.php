<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Gallery_Post_Type
{

    public static function mgwpp_register_gallery_post_type()
    {
        $args = array(
            'public' => true,
            'label' => 'Gallery Image',
            'description' => 'Manage your galleries here',
            'show_in_rest' => true,
            'show_in_menu' => false,
            'rest_base' => 'soora-api',
            'menu_icon' => 'dashicons-format-gallery',
            'has_archive' => true,
            'rewrite' => ['slug' => 'mgwpp-gallery'],
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'capability_type' => 'mgwpp_soora',
            'map_meta_cap' => true,
            'capabilities' => array(
                'edit_post' => 'edit_mgwpp_soora',
                'read_post' => 'read_mgwpp_soora',
                'delete_post' => 'delete_mgwpp_soora',
                'edit_posts' => 'edit_mgwpp_sooras',
                'edit_others_posts' => 'edit_others_mgwpp_sooras',
                'publish_posts' => 'publish_mgwpp_sooras',
                'read_private_posts' => 'read_private_mgwpp_sooras',
                'delete_posts' => 'delete_mgwpp_sooras',
                'delete_private_posts' => 'delete_private_mgwpp_sooras',
                'delete_published_posts' => 'delete_published_mgwpp_sooras',
                'delete_others_posts' => 'delete_others_mgwpp_sooras',
                'edit_private_posts' => 'edit_private_mgwpp_sooras',
                'edit_published_posts' => 'edit_published_mgwpp_sooras',
                'create_posts' => 'create_mgwpp_sooras',
            )
        );
        register_post_type('mgwpp_soora', $args);
    }
    public static function mgwpp_add_gallery_meta_boxes()
    {
        add_meta_box(
            'mgwpp_gallery_links',
            'Gallery Links',
            [self::class, 'render_gallery_links_meta_box'],
            'mgwpp_soora',
            'normal',
            'high'
        );

        // Only add text meta box for specific gallery types
        $screen = get_current_screen();
        if ($screen && $post_id = get_the_ID()) {
            $gallery_type = get_post_meta($post_id, 'gallery_type', true);
            if (in_array($gallery_type, ['type1', 'type2', 'type3'])) {
                add_meta_box(
                    'mgwpp_gallery_text',
                    'Gallery Text',
                    [self::class, 'render_gallery_text_meta_box'],
                    'mgwpp_soora',
                    'normal',
                    'high'
                );
            }
        }
    }

    public static function render_gallery_links_meta_box($post)
    {
        // Get existing image links
        $image_links = get_post_meta($post->ID, '_mgwpp_image_links', true) ?: [];
        $cta_links = get_post_meta($post->ID, '_mgwpp_cta_links', true) ?: [];

        // Nonce field for security
        wp_nonce_field('mgwpp_save_gallery_links', 'mgwpp_gallery_links_nonce');

        // Get all images uploaded to this gallery
        $attachments = get_attached_media('image', $post->ID);

        // Enqueue media scripts
        wp_enqueue_media();
        wp_enqueue_script('mgwpp-admin-gallery');

        echo '<div class="mgwpp-gallery-links-container">';

        // Existing images section
        echo '<h3>Gallery Image Links</h3>';
        echo '<div class="mgwpp-image-links-list">';

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $image_url = wp_get_attachment_url($attachment->ID);
                $current_link = $image_links[$attachment->ID] ?? '';
                ?>
                <div class="mgwpp-image-link-item" data-attachment-id="<?php echo $attachment->ID; ?>">
                    <div class="mgwpp-image-preview">
                        <img src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; height: auto;">
                        <div class="mgwpp-image-info">
                            <span><?php echo esc_html($attachment->post_title); ?></span>
                        </div>
                    </div>
                    <div class="mgwpp-link-fields">
                        <div class="mgwpp-link-field">
                            <label>Link URL:</label>
                            <input type="url"
                                name="mgwpp_image_links[<?php echo $attachment->ID; ?>]"
                                value="<?php echo esc_attr($current_link); ?>"
                                placeholder="https://example.com">
                        </div>
                        <div class="mgwpp-link-options">
                            <label>
                                <input type="checkbox"
                                    name="mgwpp_image_link_new_tab[<?php echo $attachment->ID; ?>]"
                                    <?php checked(isset($image_links[$attachment->ID . '_new_tab']) && $image_links[$attachment->ID . '_new_tab']); ?>>
                                Open in new tab
                            </label>
                            <label>
                                <input type="checkbox"
                                    name="mgwpp_image_link_nofollow[<?php echo $attachment->ID; ?>]"
                                    <?php checked(isset($image_links[$attachment->ID . '_nofollow']) && $image_links[$attachment->ID . '_nofollow']); ?>>
                                Nofollow
                            </label>
                        </div>
                    </div>
                    <button type="button" class="mgwpp-remove-image-link button-link">Remove</button>
                </div>
                <?php
            }
        } else {
            echo '<p class="mgwpp-no-images-notice">No images found in this gallery. Upload images first.</p>';
        }

        echo '</div>'; // .mgwpp-image-links-list

        //  new image button
        echo '<button type="button" class="button mgwpp-add-gallery-image" data-post-id="' . $post->ID . '">Add Gallery Image</button>';

        // CTA Links section
        echo '<h3>Call-to-Action Links</h3>';
        echo '<div class="mgwpp-cta-links">';
        echo '<p><label>Primary CTA: <input type="url" name="mgwpp_cta_links[primary]" value="' . esc_attr($cta_links['primary'] ?? '') . '"></label></p>';
        echo '<p><label>Secondary CTA: <input type="url" name="mgwpp_cta_links[secondary]" value="' . esc_attr($cta_links['secondary'] ?? '') . '"></label></p>';
        echo '</div>';

        echo '</div>'; // .mgwpp-gallery-links-container
    }
    public static function render_gallery_text_meta_box($post)
    {
        $custom_text = get_post_meta($post->ID, '_mgwpp_custom_text', true);
        wp_editor(
            $custom_text,
            'mgwpp_custom_text',
            ['textarea_name' => 'mgwpp_custom_text']
        );
    }

    public static function mgwpp_save_gallery_meta($post_id)
    {
        // Verify nonce and permissions
        if (!isset($_POST['mgwpp_gallery_links_nonce']) ||
            !wp_verify_nonce($_POST['mgwpp_gallery_links_nonce'], 'mgwpp_save_gallery_links')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_mgwpp_soora', $post_id)) {
            return;
        }

        // Save image links and their attributes
        $image_links_data = [];

        if (isset($_POST['mgwpp_image_links'])) {
            foreach ($_POST['mgwpp_image_links'] as $attachment_id => $link) {
                if (!empty($link)) {
                    $image_links_data[$attachment_id] = esc_url_raw($link);

                    // Save link attributes
                    $image_links_data[$attachment_id . '_new_tab'] = isset($_POST['mgwpp_image_link_new_tab'][$attachment_id]);
                    $image_links_data[$attachment_id . '_nofollow'] = isset($_POST['mgwpp_image_link_nofollow'][$attachment_id]);
                }
            }
            update_post_meta($post_id, '_mgwpp_image_links', $image_links_data);
        } else {
            delete_post_meta($post_id, '_mgwpp_image_links');
        }

        // Save CTA links
        if (isset($_POST['mgwpp_cta_links'])) {
            update_post_meta($post_id, '_mgwpp_cta_links', array_map('esc_url_raw', $_POST['mgwpp_cta_links']));
        } else {
            delete_post_meta($post_id, '_mgwpp_cta_links');
        }
    }
}

// Register the custom post type during the 'init' hook
add_action('init', array('MGWPP_Gallery_Post_Type', 'mgwpp_register_gallery_post_type'));
//  the hooks
add_action('add_meta_boxes', [MGWPP_Gallery_Post_Type::class, 'mgwpp_add_gallery_meta_boxes']);
add_action('save_post_mgwpp_soora', [MGWPP_Gallery_Post_Type::class, 'mgwpp_save_gallery_meta']);
