<?php

/**
 * Enhanced Gallery Editor Sidebar
 * File: includes/admin/views/editor/class-mgwpp-editor-sidebar.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Editor_Sidebar
{
    private $gallery_data;
    private $media_items;

    public function __construct($gallery_data = [], $media_items = [])
    {
        $this->gallery_data = $gallery_data;
        $this->media_items = $media_items;
    }

    public function render()
    {
        ?>
        <div class="mgwpp-editor-sidebar">
            <?php $this->render_media_library_panel(); ?>
            <?php $this->render_properties_panel(); ?>
        </div>
        <?php
    }

    private function render_media_library_panel()
    {
        ?>
        <div class="mgwpp-media-panel">
            <div class="mgwpp-panel-header">
                <h3><?php _e('Media Library', 'mini-gallery'); ?></h3>
                <div class="mgwpp-media-search">
                    <input type="search" placeholder="<?php esc_attr_e('Search media...', 'mini-gallery'); ?>">
                </div>
            </div>
            <div class="mgwpp-media-grid">
                <?php $this->render_media_items(); ?>
            </div>
        </div>
        <?php
    }

    private function render_properties_panel()
    {
        ?>
        <div class="mgwpp-properties-panel">
            <div class="mgwpp-panel-header">
                <h3><?php _e('Item Properties', 'mini-gallery'); ?></h3>
                <span class="mgwpp-selected-item-info"></span>
            </div>
            <div class="mgwpp-properties-content">
                <?php $this->render_properties_content(); ?>
            </div>
        </div>
        <?php
    }

    private function render_media_items()
    {
        if (empty($this->media_items)) {
            $this->render_empty_media_library();
            return;
        }

        foreach ($this->media_items as $index => $item) {
            echo $this->get_media_item_html($item, $index);
        }
    }

    private function render_empty_media_library()
    {
        ?>
        <div class="mgwpp-empty-library">
            <p><?php _e('No media items found.', 'mini-gallery'); ?></p>
            <button class="button button-primary mgwpp-open-media-library">
                <?php _e('Open Media Library', 'mini-gallery'); ?>
            </button>
        </div>
        <?php
    }

    private function get_media_item_html($item, $index)
    {
        $item_id = $item['id'] ?? 'item_' . $index;
        $type = $item['type'] ?? 'image';

        ob_start();
        ?>
        <div class="mgwpp-media-item" data-item-id="<?php echo esc_attr($item_id); ?>">
            <div class="mgwpp-media-thumbnail">
                <?php switch ($type):
                    case 'image': ?>
                        <img src="<?php echo esc_url($item['thumbnail'] ?? $item['image_url']); ?>"
                            alt="<?php echo esc_attr($item['alt_text'] ?? ''); ?>">
                        <div class="mgwpp-media-actions">
                            <button class="button-link mgwpp-edit-item"
                                title="<?php esc_attr_e('Edit', 'mini-gallery'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button class="button-link mgwpp-delete-item"
                                title="<?php esc_attr_e('Delete', 'mini-gallery'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                        <?php break;

                    case 'video': ?>
                        <div class="mgwpp-video-thumbnail">
                            <span class="dashicons dashicons-format-video"></span>
                        </div>
                        <?php break;

                    default: ?>
                        <div class="mgwpp-file-thumbnail">
                            <span class="dashicons dashicons-media-default"></span>
                        </div>
                <?php endswitch; ?>
            </div>
            <div class="mgwpp-media-info">
                <span class="mgwpp-media-title"><?php echo esc_html($item['title'] ?? __('Untitled', 'mini-gallery')); ?></span>
                <span class="mgwpp-media-type"><?php echo esc_html($this->get_item_type_label($item)); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_properties_content()
    {
        ?>
        <div class="mgwpp-properties-tabs">
            <nav class="mgwpp-tab-nav">
                <button class="nav-tab active" data-target="content"><?php _e('Content', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="design"><?php _e('Design', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="animation"><?php _e('Animation', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="advanced"><?php _e('Advanced', 'mini-gallery'); ?></button>
            </nav>

            <div class="mgwpp-tab-content active" data-tab="content">
                <?php $this->render_content_tab(); ?>
            </div>

            <div class="mgwpp-tab-content" data-tab="design">
                <?php $this->render_design_tab(); ?>
            </div>

            <div class="mgwpp-tab-content" data-tab="animation">
                <?php $this->render_animation_tab(); ?>
            </div>

            <div class="mgwpp-tab-content" data-tab="advanced">
                <?php $this->render_advanced_tab(); ?>
            </div>
        </div>
        <?php
    }

    private function render_content_tab()
    {
        ?>
        <div class="mgwpp-control-group">
            <label><?php _e('Item Type', 'mini-gallery'); ?></label>
            <select class="mgwpp-item-type-selector">
                <option value="image"><?php _e('Image', 'mini-gallery'); ?></option>
                <option value="video"><?php _e('Video', 'mini-gallery'); ?></option>
                <option value="text"><?php _e('Text', 'mini-gallery'); ?></option>
                <option value="button"><?php _e('Button', 'mini-gallery'); ?></option>
            </select>
        </div>

        <div class="mgwpp-content-section mgwpp-image-content">
            <!-- Image content controls -->
        </div>

        <div class="mgwpp-content-section mgwpp-video-content" style="display: none;">
            <!-- Video content controls -->
        </div>

        <div class="mgwpp-content-section mgwpp-text-content" style="display: none;">
            <!-- Text content controls -->
        </div>

        <div class="mgwpp-content-section mgwpp-button-content" style="display: none;">
            <!-- Button content controls -->
        </div>
        <?php
    }

    private function render_design_tab()
    {
        ?>
        <div class="mgwpp-control-group">
            <label><?php _e('Width', 'mini-gallery'); ?></label>
            <div class="mgwpp-dimension-control">
                <input type="number" class="mgwpp-width-value" min="0" max="100" value="100">
                <select class="mgwpp-width-unit">
                    <option value="%">%</option>
                    <option value="px">px</option>
                    <option value="auto">Auto</option>
                </select>
            </div>
        </div>
        <?php
    }

    private function render_animation_tab()
    {
        ?>
        <div class="mgwpp-control-group">
            <label><?php _e('Entrance Animation', 'mini-gallery'); ?></label>
            <select class="mgwpp-entrance-animation">
                <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                <option value="fadeIn"><?php _e('Fade In', 'mini-gallery'); ?></option>
                <option value="slideUp"><?php _e('Slide Up', 'mini-gallery'); ?></option>
            </select>
        </div>
        <?php
    }

    private function render_advanced_tab()
    {
        ?>
        <div class="mgwpp-control-group">
            <label><?php _e('Custom CSS Class', 'mini-gallery'); ?></label>
            <input type="text" class="mgwpp-custom-class" 
                placeholder="<?php esc_attr_e('custom-class-name', 'mini-gallery'); ?>">
        </div>
        <?php
    }

    private function get_item_type_label($item)
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
}