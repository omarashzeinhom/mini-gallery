<?php
/* File: includes/admin/views/editor/class-mgwpp-editor-data.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Editor_Data
{
    private $gallery_id;
    private $gallery_type;
    private $gallery_data;

    public function __construct()
    {
        $this->gallery_id = $this->get_current_gallery_id();
        $this->gallery_type = $this->get_gallery_type();
        $this->gallery_data = $this->get_gallery_data();
    }

    public function get_current_gallery_id()
    {
        return isset($_GET['post']) ? absint($_GET['post']) : 0;
    }

    public function get_gallery_type()
    {
        if (!$this->gallery_id) return 'grid';
        return get_post_meta($this->gallery_id, '_gallery_type', true) ?: 'grid';
    }

    public function get_gallery_data()
    {
        if (!$this->gallery_id) {
            return ['items' => []];
        }

        $stored_data = get_post_meta($this->gallery_id, '_mgwpp_gallery_data', true);
        return is_array($stored_data) ? $stored_data : ['items' => []];
    }

    // Getters for the properties
    public function get_id()
    {
        return $this->gallery_id;
    }

    public function get_type()
    {
        return $this->gallery_type;
    }

    public function get_data()
    {
        return $this->gallery_data;
    }

    public function get_gallery_types()
    {
        return apply_filters('mgwpp_gallery_types', [
            'grid' => __('Image Grid', 'mini-gallery'),
            'masonry' => __('Masonry', 'mini-gallery'),
            'carousel' => __('Carousel', 'mini-gallery'),
            'slider' => __('Fullscreen Slider', 'mini-gallery'),
            'custom' => __('Custom Layout', 'mini-gallery')
        ]);
    }

    public function get_item_type_label($item)
    {
        $type = $item['type'] ?? 'image';
        $labels = [
            'image' => __('Image', 'mini-gallery'),
            'video' => __('Video', 'mini-gallery'),
            'text' => __('Text', 'mini-gallery'),
            'button' => __('Button', 'mini-gallery')
        ];
        return $labels[$type] ?? __('Unknown', 'mini-gallery');
    }

    public function render_canvas_items()
    {
        if (empty($this->gallery_data['canvas_items'])) {
            echo '<div class="mgwpp-empty-canvas">';
            echo '<p>' . __('No items on canvas yet.', 'mini-gallery') . '</p>';
            echo '</div>';
            return;
        }

        foreach ($this->gallery_data['canvas_items'] as $index => $item) {
            $this->render_canvas_item($item, $index);
        }
    }

    public function render_canvas_item($item, $index)
    {
        $style = sprintf(
            'left:%dpx;top:%dpx;width:%dpx;height:%dpx;z-index:%d;',
            $item['position']['x'] ?? 0,
            $item['position']['y'] ?? 0,
            $item['dimensions']['width'] ?? 200,
            $item['dimensions']['height'] ?? 200,
            $item['z_index'] ?? 1
        );
    }
}
