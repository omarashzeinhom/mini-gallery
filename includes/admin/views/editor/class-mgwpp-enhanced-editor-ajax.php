<?php
class MGWPP_Enhanced_Editor_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_mgwpp_save_gallery_data', [$this, 'save_gallery_data']);
        add_action('wp_ajax_mgwpp_get_gallery_data', [$this, 'get_gallery_data']);
        add_action('wp_ajax_mgwpp_duplicate_gallery_item', [$this, 'duplicate_gallery_item']);
    }

    public function save_gallery_data()
    {
        check_ajax_referer('mgwpp_editor_nonce', 'nonce');

        if (!current_user_can('edit_mgwpp_galleries')) {
            wp_die(__('You do not have permission to perform this action.', 'mini-gallery'));
        }

        $gallery_id = absint($_POST['gallery_id']);
        $gallery_data = json_decode(stripslashes($_POST['gallery_data']), true);

        if (!$gallery_id || !is_array($gallery_data)) {
            wp_send_json_error(__('Invalid data provided.', 'mini-gallery'));
        }

        // Sanitize gallery data
        $sanitized_data = $this->sanitize_gallery_data($gallery_data);

        // Save to database
        $result = update_post_meta($gallery_id, '_mgwpp_gallery_data', $sanitized_data);

        if ($result !== false) {
            wp_send_json_success([
                'message' => __('Gallery saved successfully!', 'mini-gallery'),
                'data' => $sanitized_data
            ]);
        } else {
            wp_send_json_error(__('Failed to save gallery data.', 'mini-gallery'));
        }
    }

    public function get_gallery_data()
    {
        check_ajax_referer('mgwpp_editor_nonce', 'nonce');

        $gallery_id = absint($_POST['gallery_id']);
        $gallery_data = get_post_meta($gallery_id, '_mgwpp_gallery_data', true);

        wp_send_json_success($gallery_data ?: ['items' => []]);
    }

    public function duplicate_gallery_item()
    {
        check_ajax_referer('mgwpp_editor_nonce', 'nonce');

        if (!current_user_can('edit_mgwpp_galleries')) {
            wp_die(__('You do not have permission to perform this action.', 'mini-gallery'));
        }

        $gallery_id = absint($_POST['gallery_id']);
        $item_index = absint($_POST['item_index']);

        $gallery_data = get_post_meta($gallery_id, '_mgwpp_gallery_data', true);

        if (!is_array($gallery_data) || !isset($gallery_data['items'][$item_index])) {
            wp_send_json_error(__('Item not found.', 'mini-gallery'));
        }

        $item_to_duplicate = $gallery_data['items'][$item_index];
        $item_to_duplicate['id'] = uniqid('item_');
        $item_to_duplicate['title'] = $item_to_duplicate['title'] . ' ' . __('(Copy)', 'mini-gallery');

        array_splice($gallery_data['items'], $item_index + 1, 0, [$item_to_duplicate]);

        update_post_meta($gallery_id, '_mgwpp_gallery_data', $gallery_data);

        wp_send_json_success([
            'message' => __('Item duplicated successfully!', 'mini-gallery'),
            'item' => $item_to_duplicate,
            'new_index' => $item_index + 1
        ]);
    }

    private function sanitize_gallery_data($data)
    {
        $sanitized = ['items' => []];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $sanitized_item = [
                    'id' => sanitize_text_field($item['id'] ?? uniqid('item_')),
                    'type' => sanitize_text_field($item['type'] ?? 'image'),
                    'title' => sanitize_text_field($item['title'] ?? ''),
                ];

                switch ($sanitized_item['type']) {
                    case 'image':
                        $sanitized_item['image_url'] = esc_url_raw($item['image_url'] ?? '');
                        $sanitized_item['image_id'] = absint($item['image_id'] ?? 0);
                        $sanitized_item['alt_text'] = sanitize_text_field($item['alt_text'] ?? '');
                        break;

                    case 'video':
                        $sanitized_item['video_url'] = esc_url_raw($item['video_url'] ?? '');
                        $sanitized_item['video_id'] = absint($item['video_id'] ?? 0);
                        $sanitized_item['autoplay'] = (bool)($item['autoplay'] ?? false);
                        $sanitized_item['muted'] = (bool)($item['muted'] ?? true);
                        $sanitized_item['embed_code'] = wp_kses($item['embed_code'] ?? '', [
                            'iframe' => [
                                'src' => [],
                                'width' => [],
                                'height' => [],
                                'frameborder' => [],
                                'allowfullscreen' => []
                            ]
                        ]);
                        break;

                    case 'text':
                        $sanitized_item['content'] = wp_kses_post($item['content'] ?? '');
                        $sanitized_item['text_align'] = sanitize_text_field($item['text_align'] ?? 'left');
                        break;

                    case 'button':
                        $sanitized_item['button_text'] = sanitize_text_field($item['button_text'] ?? '');
                        $sanitized_item['button_url'] = esc_url_raw($item['button_url'] ?? '');
                        $sanitized_item['button_target'] = sanitize_text_field($item['button_target'] ?? '_self');
                        $sanitized_item['button_style'] = sanitize_text_field($item['button_style'] ?? 'primary');
                        break;
                }

                // Common design properties
                $sanitized_item['width_value'] = absint($item['width_value'] ?? 100);
                $sanitized_item['width_unit'] = sanitize_text_field($item['width_unit'] ?? '%');
                $sanitized_item['margin'] = absint($item['margin'] ?? 0);
                $sanitized_item['padding'] = absint($item['padding'] ?? 0);
                $sanitized_item['background_color'] = sanitize_hex_color($item['background_color'] ?? '#ffffff');
                $sanitized_item['border_radius'] = absint($item['border_radius'] ?? 0);
                $sanitized_item['entrance_animation'] = sanitize_text_field($item['entrance_animation'] ?? 'none');
                $sanitized_item['animation_duration'] = floatval($item['animation_duration'] ?? 0.5);
                $sanitized_item['animation_delay'] = floatval($item['animation_delay'] ?? 0);
                $sanitized_item['custom_class'] = sanitize_text_field($item['custom_class'] ?? '');
                $sanitized_item['custom_css'] = sanitize_textarea_field($item['custom_css'] ?? '');
                $sanitized_item['hide_on_mobile'] = (bool)($item['hide_on_mobile'] ?? false);
                $sanitized_item['hide_on_tablet'] = (bool)($item['hide_on_tablet'] ?? false);

                $sanitized['items'][] = $sanitized_item;
            }
        }

        return $sanitized;
    }
}
