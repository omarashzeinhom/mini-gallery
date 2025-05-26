<?php

/**
 * Enhanced Gallery Editor View with Per-Image Customization
 * File: includes/admin/views/editor/class-mgwpp-editor-view.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Editor_View
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

    public function render()
    {
        $this->enqueue_editor_assets();
?>
        <div class="mgwpp-enhanced-editor-wrap">
            <div class="mgwpp-editor-header">
                <h1><?php _e('Enhanced Gallery Editor', 'mini-gallery'); ?></h1>
                <div class="mgwpp-toolbar">
                    <select class="mgwpp-gallery-type" id="mgwpp_gallery_type">
                        <?php foreach ($this->get_gallery_types() as $type => $label) : ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($type, $this->gallery_type); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button-secondary mgwpp-preview-gallery">
                        <?php _e('Preview', 'mini-gallery'); ?>
                    </button>
                    <button class="button button-primary mgwpp-save-gallery">
                        <?php _e('Save Gallery', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>

            <div class="mgwpp-editor-container">
                <!-- Main Stage Area -->
                <div class="mgwpp-stage-area">
                    <div class="mgwpp-stage-header">
                        <h3><?php _e('Gallery Items', 'mini-gallery'); ?></h3>
                        <button class="button mgwpp-add-new-item">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Add New Item', 'mini-gallery'); ?>
                        </button>
                    </div>

                    <div class="mgwpp-items-container" id="mgwpp-sortable-items">
                        <?php $this->render_gallery_items(); ?>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div class="mgwpp-properties-panel">
                    <div class="mgwpp-panel-header">
                        <h3><?php _e('Item Properties', 'mini-gallery'); ?></h3>
                        <span class="mgwpp-selected-item-info"><?php _e('Select an item to edit', 'mini-gallery'); ?></span>
                    </div>

                    <div class="mgwpp-properties-content">
                        <?php $this->render_properties_panel(); ?>
                    </div>
                </div>
            </div>

            <!-- Item Template (Hidden) -->
            <div class="mgwpp-item-template" style="display: none;">
                <?php $this->render_item_template(); ?>
            </div>
        </div>

        <!-- Modal for Media Selection -->
        <div class="mgwpp-modal" id="mgwpp-media-modal" style="display: none;">
            <div class="mgwpp-modal-content">
                <span class="mgwpp-modal-close">&times;</span>
                <h3><?php _e('Select Media', 'mini-gallery'); ?></h3>
                <div class="mgwpp-media-tabs">
                    <button class="mgwpp-media-tab active" data-type="image"><?php _e('Image', 'mini-gallery'); ?></button>
                    <button class="mgwpp-media-tab" data-type="video"><?php _e('Video', 'mini-gallery'); ?></button>
                </div>
                <div class="mgwpp-media-content">
                    <div class="mgwpp-media-upload-area">
                        <p><?php _e('Choose files or drag them here', 'mini-gallery'); ?></p>
                        <button class="button mgwpp-select-media"><?php _e('Select Files', 'mini-gallery'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private function render_gallery_items()
    {
        if (empty($this->gallery_data['items'])) {
            echo '<div class="mgwpp-empty-stage">';
            echo '<p>' . __('No items in this gallery yet.', 'mini-gallery') . '</p>';
            echo '<button class="button button-primary mgwpp-add-first-item">' . __('Add Your First Item', 'mini-gallery') . '</button>';
            echo '</div>';
            return;
        }

        foreach ($this->gallery_data['items'] as $index => $item) {
            $this->render_gallery_item($item, $index);
        }
    }

    private function render_gallery_item($item, $index)
    {
        $item_id = $item['id'] ?? 'item_' . $index;
    ?>
        <div class="mgwpp-gallery-item" data-item-id="<?php echo esc_attr($item_id); ?>" data-index="<?php echo esc_attr($index); ?>">
            <div class="mgwpp-item-preview">
                <?php $this->render_item_preview($item); ?>
            </div>

            <div class="mgwpp-item-controls">
                <button class="mgwpp-item-edit" title="<?php esc_attr_e('Edit Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button class="mgwpp-item-duplicate" title="<?php esc_attr_e('Duplicate Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-admin-page"></span>
                </button>
                <button class="mgwpp-item-delete" title="<?php esc_attr_e('Delete Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="mgwpp-item-drag-handle">
                    <span class="dashicons dashicons-move"></span>
                </div>
            </div>

            <div class="mgwpp-item-info">
                <span class="mgwpp-item-type"><?php echo esc_html($this->get_item_type_label($item)); ?></span>
                <span class="mgwpp-item-title"><?php echo esc_html($item['title'] ?? __('Untitled', 'mini-gallery')); ?></span>
            </div>
        </div>
    <?php
    }

    private function render_item_preview($item)
    {
        $type = $item['type'] ?? 'image';

        switch ($type) {
            case 'image':
                if (!empty($item['image_url'])) {
                    echo '<img src="' . esc_url($item['image_url']) . '" alt="' . esc_attr($item['title'] ?? '') . '">';
                } else {
                    echo '<div class="mgwpp-placeholder-image"><span class="dashicons dashicons-format-image"></span></div>';
                }
                break;

            case 'video':
                if (!empty($item['video_url'])) {
                    echo '<video src="' . esc_url($item['video_url']) . '" muted></video>';
                } else {
                    echo '<div class="mgwpp-placeholder-video"><span class="dashicons dashicons-video-alt3"></span></div>';
                }
                break;

            case 'text':
                echo '<div class="mgwpp-text-preview">' . wp_kses_post($item['content'] ?? __('Text content...', 'mini-gallery')) . '</div>';
                break;

            case 'button':
                echo '<div class="mgwpp-button-preview"><button>' . esc_html($item['button_text'] ?? __('Button', 'mini-gallery')) . '</button></div>';
                break;
        }
    }

    private function render_properties_panel()
    {
    ?>
        <div class="mgwpp-properties-tabs">
            <nav class="mgwpp-tab-nav">
                <button class="nav-tab active" data-target="content"><?php _e('Content', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="design"><?php _e('Design', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="animation"><?php _e('Animation', 'mini-gallery'); ?></button>
                <button class="nav-tab" data-target="advanced"><?php _e('Advanced', 'mini-gallery'); ?></button>
            </nav>

            <!-- Content Tab -->
            <div class="mgwpp-tab-content active" data-tab="content">
                <div class="mgwpp-control-group">
                    <label><?php _e('Item Type', 'mini-gallery'); ?></label>
                    <select class="mgwpp-item-type-selector">
                        <option value="image"><?php _e('Image', 'mini-gallery'); ?></option>
                        <option value="video"><?php _e('Video', 'mini-gallery'); ?></option>
                        <option value="text"><?php _e('Text', 'mini-gallery'); ?></option>
                        <option value="button"><?php _e('Button', 'mini-gallery'); ?></option>
                    </select>
                </div>

                <!-- Test Tab Start -->

                <div class="mgwpp-add-buttons">
                    <button class="button mgwpp-add-image">
                        <?php _e('Add Image', 'mini-gallery'); ?>
                    </button>
                    <button class="button mgwpp-add-button">
                        <?php _e('Add Button', 'mini-gallery'); ?>
                    </button>
                </div>
                <!-- Test Tab End -->

                <!-- Image Content -->
                <div class="mgwpp-content-section mgwpp-image-content">
                    <div class="mgwpp-control-group">
                        <label><?php _e('Image', 'mini-gallery'); ?></label>
                        <div class="mgwpp-image-selector">
                            <div class="mgwpp-image-preview"></div>
                            <button class="button mgwpp-select-image"><?php _e('Select Image', 'mini-gallery'); ?></button>
                            <button class="button mgwpp-remove-image" style="display: none;"><?php _e('Remove', 'mini-gallery'); ?></button>
                        </div>
                    </div>
                    <div class="mgwpp-control-group">
                        <label><?php _e('Alt Text', 'mini-gallery'); ?></label>
                        <input type="text" class="mgwpp-image-alt" placeholder="<?php esc_attr_e('Image description', 'mini-gallery'); ?>">
                    </div>
                </div>

                <!-- Video Content -->
                <div class="mgwpp-content-section mgwpp-video-content" style="display: none;">
                    <div class="mgwpp-control-group">
                        <label><?php _e('Video Source', 'mini-gallery'); ?></label>
                        <div class="mgwpp-video-source-tabs">
                            <button class="mgwpp-video-tab active" data-source="upload"><?php _e('Upload', 'mini-gallery'); ?></button>
                            <button class="mgwpp-video-tab" data-source="url"><?php _e('URL', 'mini-gallery'); ?></button>
                            <button class="mgwpp-video-tab" data-source="embed"><?php _e('Embed', 'mini-gallery'); ?></button>
                        </div>
                        <div class="mgwpp-video-input-area">
                            <div class="mgwpp-video-upload active">
                                <button class="button mgwpp-select-video"><?php _e('Select Video', 'mini-gallery'); ?></button>
                            </div>
                            <div class="mgwpp-video-url">
                                <input type="url" class="mgwpp-video-url-input" placeholder="<?php esc_attr_e('Enter video URL', 'mini-gallery'); ?>">
                            </div>
                            <div class="mgwpp-video-embed">
                                <textarea class="mgwpp-video-embed-code" placeholder="<?php esc_attr_e('Paste embed code here', 'mini-gallery'); ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mgwpp-control-group">
                        <label>
                            <input type="checkbox" class="mgwpp-video-autoplay">
                            <?php _e('Autoplay', 'mini-gallery'); ?>
                        </label>
                    </div>
                    <div class="mgwpp-control-group">
                        <label>
                            <input type="checkbox" class="mgwpp-video-muted" checked>
                            <?php _e('Muted', 'mini-gallery'); ?>
                        </label>
                    </div>
                </div>

                <!-- Text Content -->
                <div class="mgwpp-content-section mgwpp-text-content" style="display: none;">
                    <div class="mgwpp-control-group">
                        <label><?php _e('Text Content', 'mini-gallery'); ?></label>
                        <div class="mgwpp-text-editor-toolbar">
                            <button type="button" class="mgwpp-text-bold" title="<?php esc_attr_e('Bold', 'mini-gallery'); ?>"><strong>B</strong></button>
                            <button type="button" class="mgwpp-text-italic" title="<?php esc_attr_e('Italic', 'mini-gallery'); ?>"><em>I</em></button>
                            <button type="button" class="mgwpp-text-underline" title="<?php esc_attr_e('Underline', 'mini-gallery'); ?>"><u>U</u></button>
                        </div>
                        <textarea class="mgwpp-text-content-area" rows="5" placeholder="<?php esc_attr_e('Enter your text content...', 'mini-gallery'); ?>"></textarea>
                    </div>
                    <div class="mgwpp-control-group">
                        <label><?php _e('Text Alignment', 'mini-gallery'); ?></label>
                        <select class="mgwpp-text-align">
                            <option value="left"><?php _e('Left', 'mini-gallery'); ?></option>
                            <option value="center"><?php _e('Center', 'mini-gallery'); ?></option>
                            <option value="right"><?php _e('Right', 'mini-gallery'); ?></option>
                            <option value="justify"><?php _e('Justify', 'mini-gallery'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Button Content -->
                <div class="mgwpp-content-section mgwpp-button-content" style="display: none;">
                    <div class="mgwpp-control-group">
                        <label><?php _e('Button Text', 'mini-gallery'); ?></label>
                        <input type="text" class="mgwpp-button-text" placeholder="<?php esc_attr_e('Click me', 'mini-gallery'); ?>">
                    </div>
                    <div class="mgwpp-control-group">
                        <label><?php _e('Button Link', 'mini-gallery'); ?></label>
                        <input type="url" class="mgwpp-button-url" placeholder="<?php esc_attr_e('https://example.com', 'mini-gallery'); ?>">
                    </div>
                    <div class="mgwpp-control-group">
                        <label><?php _e('Link Target', 'mini-gallery'); ?></label>
                        <select class="mgwpp-button-target">
                            <option value="_self"><?php _e('Same Window', 'mini-gallery'); ?></option>
                            <option value="_blank"><?php _e('New Window', 'mini-gallery'); ?></option>
                        </select>
                    </div>
                    <div class="mgwpp-control-group">
                        <label><?php _e('Button Style', 'mini-gallery'); ?></label>
                        <select class="mgwpp-button-style">
                            <option value="primary"><?php _e('Primary', 'mini-gallery'); ?></option>
                            <option value="secondary"><?php _e('Secondary', 'mini-gallery'); ?></option>
                            <option value="outline"><?php _e('Outline', 'mini-gallery'); ?></option>
                            <option value="ghost"><?php _e('Ghost', 'mini-gallery'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Design Tab -->
            <div class="mgwpp-tab-content" data-tab="design">
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

                <div class="mgwpp-control-group">
                    <label><?php _e('Spacing', 'mini-gallery'); ?></label>
                    <div class="mgwpp-spacing-control">
                        <input type="number" class="mgwpp-margin" placeholder="<?php esc_attr_e('Margin', 'mini-gallery'); ?>" min="0">
                        <input type="number" class="mgwpp-padding" placeholder="<?php esc_attr_e('Padding', 'mini-gallery'); ?>" min="0">
                    </div>
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Background', 'mini-gallery'); ?></label>
                    <input type="color" class="mgwpp-background-color" value="#ffffff">
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Border Radius', 'mini-gallery'); ?></label>
                    <input type="range" class="mgwpp-border-radius" min="0" max="50" value="0">
                    <span class="mgwpp-range-value">0px</span>
                </div>
            </div>

            <!-- Animation Tab -->
            <div class="mgwpp-tab-content" data-tab="animation">
                <div class="mgwpp-control-group">
                    <label><?php _e('Entrance Animation', 'mini-gallery'); ?></label>
                    <select class="mgwpp-entrance-animation">
                        <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                        <option value="fadeIn"><?php _e('Fade In', 'mini-gallery'); ?></option>
                        <option value="slideUp"><?php _e('Slide Up', 'mini-gallery'); ?></option>
                        <option value="slideDown"><?php _e('Slide Down', 'mini-gallery'); ?></option>
                        <option value="slideLeft"><?php _e('Slide Left', 'mini-gallery'); ?></option>
                        <option value="slideRight"><?php _e('Slide Right', 'mini-gallery'); ?></option>
                        <option value="zoomIn"><?php _e('Zoom In', 'mini-gallery'); ?></option>
                        <option value="bounce"><?php _e('Bounce', 'mini-gallery'); ?></option>
                    </select>
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Animation Duration', 'mini-gallery'); ?></label>
                    <input type="range" class="mgwpp-animation-duration" min="0.1" max="3" step="0.1" value="0.5">
                    <span class="mgwpp-range-value">0.5s</span>
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Animation Delay', 'mini-gallery'); ?></label>
                    <input type="range" class="mgwpp-animation-delay" min="0" max="2" step="0.1" value="0">
                    <span class="mgwpp-range-value">0s</span>
                </div>
            </div>

            <!-- Advanced Tab -->
            <div class="mgwpp-tab-content" data-tab="advanced">
                <div class="mgwpp-control-group">
                    <label><?php _e('Custom CSS Class', 'mini-gallery'); ?></label>
                    <input type="text" class="mgwpp-custom-class" placeholder="<?php esc_attr_e('custom-class-name', 'mini-gallery'); ?>">
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Custom CSS', 'mini-gallery'); ?></label>
                    <textarea class="mgwpp-custom-css" rows="4" placeholder="<?php esc_attr_e('/* Custom CSS rules */', 'mini-gallery'); ?>"></textarea>
                </div>

                <div class="mgwpp-control-group">
                    <label>
                        <input type="checkbox" class="mgwpp-hide-on-mobile">
                        <?php _e('Hide on Mobile', 'mini-gallery'); ?>
                    </label>
                </div>

                <div class="mgwpp-control-group">
                    <label>
                        <input type="checkbox" class="mgwpp-hide-on-tablet">
                        <?php _e('Hide on Tablet', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>
        </div>
    <?php
    }

    private function render_item_template()
    {
    ?>
        <div class="mgwpp-gallery-item mgwpp-item-template-content" data-item-id="">
            <div class="mgwpp-item-preview">
                <div class="mgwpp-placeholder-image">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
            </div>

            <div class="mgwpp-item-controls">
                <button class="mgwpp-item-edit" title="<?php esc_attr_e('Edit Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-edit"></span>
                </button>
                <button class="mgwpp-item-duplicate" title="<?php esc_attr_e('Duplicate Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-admin-page"></span>
                </button>
                <button class="mgwpp-item-delete" title="<?php esc_attr_e('Delete Item', 'mini-gallery'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="mgwpp-item-drag-handle">
                    <span class="dashicons dashicons-move"></span>
                </div>
            </div>

            <div class="mgwpp-item-info">
                <span class="mgwpp-item-type"><?php _e('New Item', 'mini-gallery'); ?></span>
                <span class="mgwpp-item-title"><?php _e('Untitled', 'mini-gallery'); ?></span>
            </div>
        </div>
<?php
    }

    private function enqueue_editor_assets()
    {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        // Custom editor assets
        wp_enqueue_style(
            'mgwpp-enhanced-editor-css',
            MG_PLUGIN_URL . '/includes/admin/css/mgwpp-editor.css',
            [],
            MGWPP_ASSET_VERSION
        );

        wp_enqueue_script(
            'mgwpp-enhanced-editor-js',
            MG_PLUGIN_URL . '/includes/admin/js/mgwpp-editor.js',
            ['jquery', 'jquery-ui-sortable', 'wp-i18n'],
            MGWPP_ASSET_VERSION,
            true
        );

        wp_localize_script('mgwpp-enhanced-editor-js', 'mgwppEditor', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp_editor_nonce'),
            'galleryId' => $this->gallery_id,
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this item?', 'mini-gallery'),
                'unsavedChanges' => __('You have unsaved changes. Are you sure you want to leave?', 'mini-gallery'),
                'saveSuccess' => __('Gallery saved successfully!', 'mini-gallery'),
                'saveError' => __('Error saving gallery. Please try again.', 'mini-gallery'),
            ]
        ]);
    }

    private function get_current_gallery_id()
    {
        return isset($_GET['post']) ? absint($_GET['post']) : 0;
    }

    private function get_gallery_type()
    {
        if (!$this->gallery_id) return 'grid';
        return get_post_meta($this->gallery_id, '_gallery_type', true) ?: 'grid';
    }

    private function get_gallery_data()
    {
        if (!$this->gallery_id) {
            return ['items' => []];
        }

        $stored_data = get_post_meta($this->gallery_id, '_mgwpp_gallery_data', true);
        return is_array($stored_data) ? $stored_data : ['items' => []];
    }

    private function get_gallery_types()
    {
        return apply_filters('mgwpp_gallery_types', [
            'grid' => __('Image Grid', 'mini-gallery'),
            'masonry' => __('Masonry', 'mini-gallery'),
            'carousel' => __('Carousel', 'mini-gallery'),
            'slider' => __('Fullscreen Slider', 'mini-gallery'),
            'custom' => __('Custom Layout', 'mini-gallery')
        ]);
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

/**
 * AJAX Handler for Enhanced Editor
 * File: includes/admin/class-mgwpp-enhanced-editor-ajax.php
 */

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
