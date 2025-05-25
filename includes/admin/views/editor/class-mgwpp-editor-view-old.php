<?php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Editor_View
{
    private $gallery_id;
    private $gallery_type;

    public function __construct()
    {
        $this->gallery_id = $this->get_current_gallery_id();
        $this->gallery_type = $this->get_gallery_type();
    }

    public function render()
    {
        $this->enqueue_editor_assets();
        ?>
        <div class="mgwpp-editor-wrap">
            <div class="mgwpp-editor-header">
                <h1><?php _e('Gallery Editor', 'mini-gallery'); ?></h1>
                <div class="mgwpp-toolbar">
                    <select class="mgwpp-gallery-type" id="mgwpp_gallery_type">
                        <?php foreach ($this->get_gallery_types() as $type => $label) : ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($type, $this->gallery_type); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button-primary mgwpp-save-gallery">
                        <?php _e('Save Gallery', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>

            <div class="mgwpp-editor-container">
                <div class="mgwpp-stage-preview">
                    <div class="mgwpp-preview-wrapper">
                        <?php $this->render_preview_area(); ?>
                    </div>
                </div>
                
                <div class="mgwpp-control-panel">
                    <?php include plugin_dir_path(__FILE__) . 'tabs/class-mgwpp-editor-tabs.php'; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_preview_area()
    {
        if ($this->gallery_id) {
            echo do_shortcode('[mgwpp_gallery id="' . $this->gallery_id . '" editor_preview="true"]');
        } else {
            echo '<div class="mgwpp-empty-preview">' . __('Add images to start building your gallery', 'mini-gallery') . '</div>';
        }
    }

    private function enqueue_editor_assets()
    {
        wp_enqueue_media();
        wp_enqueue_script('mgwpp-editor');
        wp_enqueue_style('mgwpp-editor-styles');
    }

    private function get_current_gallery_id()
    {
        return isset($_GET['post']) ? absint($_GET['post']) : 0;
    }

    private function get_gallery_type()
    {
        return get_post_meta($this->gallery_id, '_gallery_type', true) ?: 'grid';
    }

    private function get_gallery_types()
    {
        return apply_filters('mgwpp_gallery_types', [
            'grid' => __('Image Grid', 'mini-gallery'),
            'carousel' => __('Carousel', 'mini-gallery'),
            'masonry' => __('Masonry', 'mini-gallery'),
            'slider' => __('Fullscreen Slider', 'mini-gallery')
        ]);
    }
}