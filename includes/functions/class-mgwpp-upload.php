<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Upload
{
    public static function mgwpp_upload()
    {
        // Verify nonce for security
        if (!isset($_POST['mgwpp_upload_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mgwpp_upload_nonce'])), 'mgwpp_upload_nonce')) {
            wp_die(esc_html__('Security check failed for Uploading Gallery', 'mini-gallery'));
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
                                    return new WP_Error('attachment_creation_failed', __('Attachment creation failed: ', 'mini-gallery') . $attachment_id->get_error_message());
                                }
                            } else {
                            // translators: %d is the index of the file that failed validation.
                                return new WP_Error(
                                    'file_upload_failed',
                                    __('File upload failed. Please try again.', 'mini-gallery')
                                );
                            }
                        } else {
                        // translators: %s is the error message from WP_Error.
                            return new WP_Error(
                                'file_data_validation_failed',
                                sprintf(__('File data validation failed for index: %d', 'mini-gallery'), $index)
                            );
                        }
                    }
                }
            }
        }

        wp_redirect(esc_url_raw(admin_url('admin.php?page=mini-gallery')));
        exit;
    }
}

add_action('admin_post_mgwpp_upload', array('MGWPP_Upload', 'mgwpp_upload'));
