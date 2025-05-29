<?php
/* Main Editor View File: includes/admin/views/editor/class-mgwpp-editor-view.php */
if (!defined('ABSPATH')) exit;

require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/class-mgwpp-editor-data.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/side-bar/class-mgwpp-editor-sidebar.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/media-panel/class-mgwpp-media-panel.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/properties-panel/class-mgwpp-properties-panel.php';
require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/class-mgwpp-editor-assets.php';

class MGWPP_Editor_View
{
    private $gallery_data;
    private $assets;


    public function __construct()
    {
        $this->gallery_data = new MGWPP_Editor_Data();
        $this->assets = new MGWPP_Editor_Assets();
       $this->assets->register();  
    }

    public function render()
    {
       $this->assets->enqueue_editor_assets();
?>
        <div class="mgwpp-enhanced-editor-wrap">
            <?php  $this->render_header(); ?>
            <div class="mgwpp-editor-container">
                <?php $this->render_canvas_area(); ?>
                <?php $this->render_sidebar(); ?>
            </div>
        </div>
    <?php
    }

    private function render_header()
    { ?>
        <div class="mgwpp-editor-header">
            <h1><?php _e('Enhanced Gallery Editor', 'mini-gallery'); ?></h1>
            <div class="mgwpp-toolbar">
                <select class="mgwpp-gallery-type" id="mgwpp_gallery_type">
                    <?php foreach ($this->gallery_data->get_gallery_types() as $type => $label) : ?>
                        <option value="<?= esc_attr($type); ?>" <?php selected($type, $this->gallery_data->get_type()); ?>>
                            <?= esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button button-primary mgwpp-save-gallery">
                    <?php _e('Save Gallery', 'mini-gallery'); ?>
                </button>
            </div>
        </div>
    <?php }

    private function render_canvas_area()
    { ?>
        <div class="mgwpp-canvas-area">
            <div class="mgwpp-canvas-header">
                <div class="mgwpp-canvas-tools">
                    <button class="button button-primary mgwpp-add-image-to-canvas">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Add Media', 'mini-gallery'); ?>
                    </button>
                    <button class="button mgwpp-toggle-grid">
                        <span class="dashicons dashicons-grid-view"></span>
                        <?php _e('Toggle Grid', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>
            <div class="mgwpp-canvas-container">
                <div class="mgwpp-canvas-grid"></div>
                <div class="mgwpp-canvas" id="mgwpp-main-canvas">
                    <?php $this->gallery_data->render_canvas_items(); ?>

                </div>
            </div>
        </div>
<?php }

    private function render_sidebar()
    {
        new MGWPP_Editor_Sidebar(
            new MGWPP_Properties_Panel($this->gallery_data->get_data()['items'] ?? [])
        );
    }
}
