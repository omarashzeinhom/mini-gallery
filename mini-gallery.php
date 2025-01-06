<?php

/**
 * Plugin Name: Mini Gallery
 * Description: A WordPress plugin to display a simple custom gallery.
 * Version: 1.1
 * Author: Omar Ashraf Zeinhom AbdElRahman | ANDGOEDU
 * License: GPLv2
 */

if (!defined('ABSPATH')) exit;

// Unique prefix for all functions and hooks
function mgwpp_register_post_type()
{
    $args = array(
        'public' => true,
        'label' => 'Gallery Image',
        'description' => 'Manage your galleries here',
        'show_in_rest' => false,
        'show_in_menu' => false,
        'rest_base' => 'soora-api',
        'menu_icon' => 'dashicons-format-gallery',
        'has_archive' => true,
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
add_action('init', 'mgwpp_register_post_type');

// Enqueue front-end scripts and styles
function mgwpp_enqueue_assets()
{
    // Register scripts and styles
    wp_register_script('mg-carousel', plugin_dir_url(__FILE__) . 'public/js/carousel.js', array(), '1.0', true);
    wp_register_style('mg-styles', plugin_dir_url(__FILE__) . 'public/css/styles.css', array(), '1.0');

    // Enqueue for front-end only
    if (!is_admin()) {
        wp_enqueue_script('mg-carousel');
        wp_enqueue_style('mg-styles');
    }
}
add_action('wp_enqueue_scripts', 'mgwpp_enqueue_assets');

// Enqueue admin assets
function mgwpp_enqueue_admin_assets()
{
    // Register scripts and styles
    wp_register_script('mg-admin-carousel', plugin_dir_url(__FILE__) . 'public/admin/js/mg-scripts.js', array('jquery'), '1.0', true);
    wp_register_style('mg-admin-styles', plugin_dir_url(__FILE__) . 'public/admin/css/mg-styles.css', array(), '1.0');

    // Enqueue for admin pages
    wp_enqueue_script('mg-admin-carousel');
    wp_enqueue_style('mg-admin-styles');
}
add_action('admin_enqueue_scripts', 'mgwpp_enqueue_admin_assets');

// Activation & Deactivation Hooks
function mgwpp_plugin_activate()
{
    mgwpp_register_post_type();
    mgwpp_add_marketing_team_role();
    mgwpp_capabilities();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mgwpp_plugin_activate');

function mgwpp_plugin_deactivate()
{
    unregister_post_type('mgwpp_soora');
    remove_role('marketing_team');
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mgwpp_plugin_deactivate');

// Uninstall Hook
function mgwpp_plugin_uninstall()
{
    $sowar = get_posts(array(
        'post_type' => 'mgwpp_soora',
        'numberposts' => -1,
        'post_status' => 'any'
    ));
    foreach ($sowar as $gallery_image) {
        wp_delete_post(intval($gallery_image->ID), true);
    }
    remove_role('marketing_team');
}
register_uninstall_hook(__FILE__, 'mgwpp_plugin_uninstall');

// Roles
function mgwpp_add_marketing_team_role()
{
    if (get_role('marketing_team') === null) {
        add_role('marketing_team', 'Marketing Team', array(
            'read' => true,
            'upload_files' => true,
            'edit_files' => true,
            'edit_mgwpp_soora' => true,
            'read_mgwpp_soora' => true,
            'delete_mgwpp_soora' => true,
            'edit_mgwpp_sooras' => true,
            'edit_others_mgwpp_sooras' => true,
            'publish_mgwpp_sooras' => true,
            'read_private_mgwpp_sooras' => true,
            'delete_mgwpp_sooras' => true,
            'delete_private_mgwpp_sooras' => true,
            'delete_published_mgwpp_sooras' => true,
            'delete_others_mgwpp_sooras' => true,
            'edit_private_mgwpp_sooras' => true,
            'edit_published_mgwpp_sooras' => true,
            'create_mgwpp_sooras' => true,
        ));
    }
}
add_action('init', 'mgwpp_add_marketing_team_role');

// Capabilities
function mgwpp_capabilities()
{
    $roles = ['administrator', 'marketing_team'];
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->add_cap('edit_mgwpp_soora');
            $role->add_cap('read_mgwpp_soora');
            $role->add_cap('delete_mgwpp_soora');
            $role->add_cap('edit_mgwpp_sooras');
            $role->add_cap('edit_others_mgwpp_sooras');
            $role->add_cap('publish_mgwpp_sooras');
            $role->add_cap('read_private_mgwpp_sooras');
            $role->add_cap('delete_mgwpp_sooras');
            $role->add_cap('delete_private_mgwpp_sooras');
            $role->add_cap('delete_published_mgwpp_sooras');
            $role->add_cap('delete_others_mgwpp_sooras');
            $role->add_cap('edit_private_mgwpp_sooras');
            $role->add_cap('edit_published_mgwpp_sooras');
            $role->add_cap('create_mgwpp_sooras');
        }
    }
}
add_action('admin_init', 'mgwpp_capabilities');

// Admin Menu
function mgwpp_menu()
{
    if (current_user_can('edit_mgwpp_sooras')) {
        add_menu_page('Add New Mini Gallery', 'Mini Gallery', 'edit_mgwpp_sooras', 'mini-gallery', 'mgwpp_plugin_page', 'dashicons-format-gallery', 6);
    }
}
add_action('admin_menu', 'mgwpp_menu');

// Handle File Uploads
function mgwpp_upload()
{
    // Verify nonce for security
    if (!isset($_POST['mgwpp_upload_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mgwpp_upload_nonce'])), 'mgwpp_upload_nonce')) {
        wp_die(esc_html__('Security check failed', 'mini-gallery'));
    }

    // Check required fields
    if (!empty($_POST['image_title']) && !empty($_POST['gallery_type'])) {
        $title = sanitize_text_field(wp_unslash($_POST['image_title']));
        $gallery_type = sanitize_text_field(wp_unslash($_POST['gallery_type']));

        // Create a new post for the gallery
        $post_id = wp_insert_post(array(
            'post_title'  => $title,
            'post_type'   => 'mgwpp_soora',
            'post_status' => 'publish'
        ));

        if ($post_id) {
            // Save the gallery type as post meta
            update_post_meta($post_id, 'gallery_type', $gallery_type);

            // Check if files are uploaded
            if (isset($_FILES['sowar']) && !empty($_FILES['sowar']['name'][0])) {
                $file_count = count($_FILES['sowar']['name']);

                // Process multiple files
                for ($i = 0; $i < $file_count; $i++) {
                    // Validate and sanitize file data
                    if (isset($_FILES['sowar']['name'][$i], $_FILES['sowar']['type'][$i], $_FILES['sowar']['tmp_name'][$i], $_FILES['sowar']['error'][$i], $_FILES['sowar']['size'][$i])) {
                        $file_name     = sanitize_text_field($_FILES['sowar']['name'][$i]);
                        $file_type     = sanitize_text_field($_FILES['sowar']['type'][$i]);
                        $file_tmp_name = sanitize_text_field($_FILES['sowar']['tmp_name'][$i]);
                        $file_error    = intval($_FILES['sowar']['error'][$i]);
                        $file_size     = intval($_FILES['sowar']['size'][$i]);

                        // Prepare the file array
                        $file = array(
                            'name'     => $file_name,
                            'type'     => $file_type,
                            'tmp_name' => $file_tmp_name,
                            'error'    => $file_error,
                            'size'     => $file_size
                        );

                        // Handle the file upload
                        $uploaded = wp_handle_upload($file, array('test_form' => false));

                        if (isset($uploaded['file']) && !empty($uploaded['file'])) {
                            $file_path = $uploaded['file'];
                            $file_url = esc_url($uploaded['url']);
                            $file_type = wp_check_filetype($file_path);

                            // Create an attachment
                            $attachment_id = wp_insert_attachment(array(
                                'guid'           => $file_url,
                                'post_mime_type' => $file_type['type'],
                                'post_title'     => sanitize_text_field($title),
                                'post_content'   => '',
                                'post_status'    => 'inherit'
                            ), $file_path, $post_id);

                            if (!is_wp_error($attachment_id)) {
                                // Generate attachment metadata and update the database
                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                                wp_update_attachment_metadata($attachment_id, $attach_data);
                            } else {
                                return new WP_Error('attachment_creation_failed', __('Attachment creation failed:', 'mini-gallery') . $attachment_id->get_error_message());
                            }
                        } else {
                            /* translators: %d is the index of the file that failed validation. */
                            return new WP_Error(
                                'file_data_validation_failed',
                                sprintf(__('File data validation failed for index: %d', 'mini-gallery'), $index)
                            );
                        }
                    } else {
                        return new WP_Error('file_data_validation_failed', __('File data validation failed for index: ', 'mini-gallery') . $index);
                    }
                }
            }
        }
    }

    wp_redirect(esc_url_raw(admin_url('admin.php?page=mini-gallery')));
    exit;
}


add_action('admin_post_mgwpp_upload', 'mgwpp_upload');


// Handle Gallery Deletion
function mgwpp_delete_gallery()
{
    if (!isset($_GET['gallery_id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mgwpp_delete_gallery')) {
        wp_die('Security check failed');
    }

    $gallery_id = intval($_GET['gallery_id']);

    if (!current_user_can('delete_mgwpp_soora', $gallery_id)) {
        wp_die('You do not have permission to delete this gallery');
    }

    wp_delete_post($gallery_id, true);
    wp_redirect(esc_url_raw(admin_url('admin.php?page=mini-gallery')));
    exit;
}
add_action('admin_post_mgwpp_delete_gallery', 'mgwpp_delete_gallery');


function mgwpp_plugin_page()
{
?>
    <h1><?php echo esc_html__('Mini Gallery', 'mini-gallery'); ?></h1>

    <!-- Form to upload new gallery images -->
    <h2><?php echo esc_html__('Upload New Images', 'mini-gallery'); ?></h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="mgwpp_upload">
        <input type="hidden" name="mgwpp_upload_nonce"
            value="<?php echo esc_attr(wp_create_nonce('mgwpp_upload_nonce')); ?>">

        <label for="sowar"><?php echo esc_html__('Select Images:', 'mini-gallery'); ?></label>
        <input type="file" id="sowar" name="sowar[]" accept="image/*" required multiple>
        <br><br>

        <label for="image_title"><?php echo esc_html__('Gallery Title:', 'mini-gallery'); ?></label>
        <input type="text" id="image_title" name="image_title" required>
        <br><br>

        <!-- Dropdown for gallery type -->
        <label for="gallery_type"><?php echo esc_html__('Gallery Type:', 'mini-gallery'); ?></label>
        <select id="gallery_type" name="gallery_type" required>
            <option value="single_carousel"><?php echo esc_html__('Single Carousel', 'mini-gallery'); ?></option>
            <option value="multi_carousel"><?php echo esc_html__('Multi Carousel', 'mini-gallery'); ?></option>
            <option value="grid"><?php echo esc_html__('Grid Layout', 'mini-gallery'); ?></option>
        </select>
        <br><br>

        <input type="submit" class="button button-primary"
            value="<?php echo esc_attr__('Upload Images', 'mini-gallery'); ?>">
    </form>

    <!-- Display existing galleries with their IDs and shortcodes -->
    <h2><?php echo esc_html__('Existing Galleries', 'mini-gallery'); ?></h2>
    <?php
    $galleries = get_posts(array(
        'post_type' => 'mgwpp_soora',
        'numberposts' => -1
    ));
    if ($galleries) {
        foreach ($galleries as $gallery) {
    ?>
            <div>
                <h3><?php echo esc_html($gallery->post_title) . ' (ID: ' . esc_html($gallery->ID) . ')'; ?></h3>
                <p><?php echo esc_html($gallery->post_content); ?></p>

                <details style="cursor: pointer;">
                    <!-- Display the gallery type -->
                    <?php
                    $gallery_type = get_post_meta($gallery->ID, 'gallery_type', true);
                    echo '<p>' . esc_html__('Gallery Type: ', 'mini-gallery') . esc_html(ucfirst($gallery_type)) . '</p>';
                    ?>

                    <!-- Display the carousel preview using the shortcode -->
                    <?php echo do_shortcode('[mgwpp_gallery id="' . esc_attr($gallery->ID) . '"]'); ?>
                    <hr>
                    <!-- Display the shortcode dynamically with the post ID -->
                    <p><?php echo esc_html__('Shortcode to display this gallery:', 'mini-gallery'); ?></p>
                    <pre><?php echo esc_html('[mgwpp_gallery id="' . esc_attr($gallery->ID) . '"]'); ?></pre>
                    <!-- Add delete link -->
                    <?php
                    $delete_url = wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . esc_attr($gallery->ID)), 'mgwpp_delete_gallery');
                    ?>
                    <p><a href="<?php echo esc_url($delete_url); ?>"
                            class="button button-secondary"><?php echo esc_html__('Delete Gallery', 'mini-gallery'); ?></a></p>
                </details>
            </div>
            <hr>
        <?php
        }
    } else {
        ?>
        <p><?php echo esc_html__('No galleries found.', 'mini-gallery'); ?></p>
<?php
    }
}


// Shortcode to display gallery
function mgwpp_gallery_shortcode($atts)
{
    $atts = shortcode_atts(['id' => '', 'paged' => 1], $atts);
    $post_id = max(0, intval($atts['id']));
    $paged = max(1, intval($atts['paged']));
    $output = '';

    if ($post_id) {
        // Retrieve the gallery type from post meta
        $gallery_type = get_post_meta($post_id, 'gallery_type', true);
        if (!$gallery_type) {
            $gallery_type = 'single_carousel'; // Fallback to default if not set
        }

        $images_per_page = 6; // Number of images per page for multi-carousel
        $offset = ($paged - 1) * $images_per_page;

        // Retrieve all images for the gallery
        $all_images = get_attached_media('image', $post_id);

        if ($all_images) {
            if ($gallery_type === 'single_carousel') {
                $output .= '<div id="mg-carousel" class="mg-gallery-single-carousel">';
                foreach ($all_images as $image) {
                    $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                    $output .= '<div class="carousel-slide"><img src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'multi_carousel') {
                $output .= '<div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="' . esc_attr($paged) . '">';

                // Slice images for current page
                $images = array_slice($all_images, $offset, $images_per_page);
                foreach ($images as $image) {
                    $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                    $output .= '<div class="mg-multi-carousel-slide"><img class="mg-multi-carousel-slide" src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                }
                $output .= '</div>';
            } elseif ($gallery_type === 'grid') {
                $output .= '<div class="grid-layout">';
                foreach ($all_images as $image) {
                    $imgwpp_url = wp_get_attachment_image_src($image->ID, 'medium');
                    $output .= '<div class="grid-item"><img src="' . esc_url($imgwpp_url[0]) . '" alt="' . esc_attr($image->post_title) . '" loading="lazy"></div>';
                }
                $output .= '</div>';
            }
        } else {
            $output .= '<p>No images found for this gallery.</p>';
        }
    } else {
        $output .= '<p>Invalid gallery ID.</p>';
    }
    return $output;
}
add_shortcode('mgwpp_gallery', 'mgwpp_gallery_shortcode');
