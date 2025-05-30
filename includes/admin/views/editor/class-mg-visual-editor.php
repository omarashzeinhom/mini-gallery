<?php

/**
 * Main Visual Editor Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Visual_Editor_View
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_mg_save_gallery', array($this, 'ajax_save_gallery'));
        add_action('wp_ajax_mg_load_gallery', array($this, 'ajax_load_gallery'));
        add_action('wp_ajax_mg_upload_media', array($this, 'ajax_upload_media'));
        add_shortcode('mg_gallery', array($this, 'render_gallery_shortcode'));
    }

    public function init()
    {
        $this->create_gallery_post_type();
        $this->create_database_tables();
    }


    public function enqueue_admin_scripts($hook)
    {
        if (strpos($hook, 'mg-visual-editor') === false) {
            return;
        }

        // Enqueue scripts in correct order
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-util');

        // Enqueue the editor script - make sure the path is correct
        wp_enqueue_script(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/js/visual-editor.js',
            array('jquery', 'wp-util'),  // Explicit dependencies
            MGWPP_ASSET_VERSION,
            true  // Load in footer - CRITICAL for timing
        );

        // Localize script data
        wp_localize_script('mg-visual-editor', 'mgAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'pluginUrl' => MG_PLUGIN_URL
        ));

        // Enqueue styles
        wp_enqueue_style(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/css/visual-editor.css',
            array(),
            MGWPP_ASSET_VERSION
        );
    }
    public function enqueue_frontend_scripts()
    {
        wp_enqueue_script(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/js/gallery-frontend.js',
            array('jquery'),
            MGWPP_ASSET_VERSION,
            true
        );

        wp_enqueue_style(
            'mg-gallery-frontend',
            MG_PLUGIN_URL . 'editor/assets/css/gallery-frontend.css',
            array(),
            MGWPP_ASSET_VERSION
        );
    }

    // Update the admin_page method to use the render function
    public function admin_page()
    {
        $gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;
        $this->render($gallery_id);
    }

    public function all_galleries_page()
    {
        include MG_PLUGIN_PATH . 'editor/templates/all-galleries.php';
    }

    public function ajax_save_gallery()
    {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $gallery_data = json_decode(stripslashes($_POST['gallery_data']), true);
        $gallery_id = intval($_POST['gallery_id']);

        $gallery_manager = new MG_Gallery_Manager();
        $result = $gallery_manager->save_gallery($gallery_id, $gallery_data);

        wp_send_json_success($result);
    }

    public function ajax_load_gallery()
    {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');

        $gallery_id = intval($_POST['gallery_id']);
        $gallery_manager = new MG_Gallery_Manager();
        $gallery_data = $gallery_manager->load_gallery($gallery_id);

        wp_send_json_success($gallery_data);
    }

    public function ajax_upload_media()
    {
        check_ajax_referer('mg_visual_editor_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_die('Unauthorized');
        }

        $media_handler = new MG_Media_Handler();
        $result = $media_handler->handle_upload();

        wp_send_json_success($result);
    }

    public function render_gallery_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
            'type' => 'grid'
        ), $atts);

        $gallery_renderer = new MG_Gallery_Renderer();
        return $gallery_renderer->render_gallery($atts['id'], $atts['type']);
    }

    private function create_gallery_post_type()
    {
        register_post_type('mg_gallery', array(
            'labels' => array(
                'name' => 'Galleries',
                'singular_name' => 'Gallery'
            ),
            'public' => false,
            'show_ui' => false,
            'supports' => array('title', 'editor')
        ));
    }
    /**
     * Renders the visual editor interface
     * 
     * @param int $gallery_id The ID of the gallery to edit (0 for new gallery)
     */
    public function render($gallery_id = 0)
    {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Load required dependencies
        require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/class-mg-media-handler.php';
        require_once MG_PLUGIN_PATH . 'includes/admin/views/editor/class-mg-gallery-manager.php';

        // Initialize handlers
        $media_handler = new MG_Media_Handler();
        $gallery_manager = new MG_Gallery_Manager();

        // Get media library and gallery data
        $media_items = $media_handler->get_media_library();
        $gallery_data = $gallery_id ? $gallery_manager->load_gallery($gallery_id) : array();

        // Prepare editor data for JavaScript
        $editor_data = array(
            'galleryId' => $gallery_id,
            'mediaItems' => $media_items,
            'galleryData' => $gallery_data,
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => MG_PLUGIN_URL
        );

        // Output the editor HTML
?>
        <div class="wrap">
            <h1><?php echo $gallery_id ? __('Edit Gallery', 'mini-gallery') : __('Create New Gallery', 'mini-gallery'); ?></h1>
            <?php
            // Prepare media items HTML
            $media_grid_html = '';
            foreach ($media_items as $item) {
                $thumbnail = $item['type'] === 'image'
                    ? '<img src="' . esc_url($item['thumbnail'] ?: $item['url']) . '" alt="' . esc_attr($item['title']) . '">'
                    : '<div class="video-thumbnail"><span class="video-icon">🎥</span></div>';

                $media_grid_html .= '
            <div class="media-item" data-media-id="' . esc_attr($item['id']) . '">
                <div class="media-thumbnail">' . $thumbnail . '</div>
                <div class="media-info">
                    <span class="media-title">' . esc_html($item['title']) . '</span>
                    <span class="media-type">' . esc_html($item['type']) . '</span>
                </div>
            </div>';
            }

            // Output the editor HTML
            ?>
            <div class="wrap">
                <h1><?php echo $gallery_id ? __('Edit Gallery', 'mini-gallery') : __('Create New Gallery', 'mini-gallery'); ?></h1>

                <div class="mg-editor-container">
                    <div class="mg-editor-header">
                        <div class="header-content">
                            <h2>Visual Gallery Editor</h2>
                            <div class="header-actions">
                                <select id="gallery-type" class="gallery-type-select">
                                    <option value="grid">Image Grid</option>
                                    <option value="masonry">Masonry</option>
                                    <option value="carousel">Carousel</option>
                                    <option value="slider">Fullscreen Slider</option>
                                    <option value="custom">Custom Layout</option>
                                </select>
                                <button id="save-gallery" class="button button-primary">Save Gallery</button>
                            </div>
                        </div>
                    </div>

                    <div class="mg-editor-main">
                        <div class="canvas-area">
                            <div class="canvas-toolbar">
                                <button class="button add-media">📷 Add Media</button>
                                <button class="button toggle-grid">⊞ Toggle Grid</button>
                                <button class="button add-text">📝 Add Text</button>
                                <button class="button add-button">🔘 Add Button</button>
                            </div>

                            <div class="canvas-container">
                                <div id="mg-canvas" class="canvas show-grid">
                                    <div class="empty-canvas">
                                        <h3>Your canvas is empty</h3>
                                        <p>Drag items from the media library or use the toolbar to add content</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="editor-sidebar">
                            <div class="sidebar-panel media-panel">
                                <div class="panel-header">
                                    <h3>Media Library</h3>
                                    <input type="search" id="media-search" placeholder="Search media..." class="search-input">
                                </div>
                                <div class="media-grid" id="media-grid">
                                    <?php echo $media_grid_html; ?>
                                </div>
                            </div>

                            <div class="sidebar-panel properties-panel">
                                <div class="panel-header">
                                    <h3>Properties</h3>
                                    <span class="selected-info" id="selected-info"></span>
                                </div>
                                <div class="properties-content" id="properties-content">
                                    <div class="no-selection">
                                        <p>Select an item on the canvas to edit its properties</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                    window.mgEditorData = <?php echo wp_json_encode($editor_data); ?>;

                    // Minimal JavaScript for interactive functionality
                    document.addEventListener('DOMContentLoaded', function() {
                        // Toggle grid visibility
                        document.querySelector('.toggle-grid').addEventListener('click', function() {
                            const canvas = document.getElementById('mg-canvas');
                            canvas.classList.toggle('show-grid');
                        });

                        // Media search
                        document.getElementById('media-search').addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase();
                            document.querySelectorAll('.media-item').forEach(function(item) {
                                const title = item.querySelector('.media-title').textContent.toLowerCase();
                                item.style.display = title.includes(searchTerm) ? '' : 'none';
                            });
                        });

                        // Save gallery button
                        document.getElementById('save-gallery').addEventListener('click', function() {
                            const galleryData = {
                                title: "Gallery " + (window.mgEditorData.galleryId || Date.now()),
                                type: document.getElementById('gallery-type').value,
                                items: [], // Will be filled with canvas items
                                settings: {},
                            };

                            // Collect canvas items
                            document.querySelectorAll('.canvas-item').forEach(function(item) {
                                galleryData.items.push({
                                    id: item.dataset.itemId,
                                    type: item.dataset.itemType,
                                    title: item.dataset.itemTitle,
                                    content: item.dataset.itemContent || '',
                                    url: item.dataset.itemUrl || '',
                                    position: {
                                        x: parseInt(item.style.left),
                                        y: parseInt(item.style.top)
                                    },
                                    dimensions: {
                                        width: parseInt(item.style.width),
                                        height: parseInt(item.style.height)
                                    },
                                    rotation: parseInt(item.dataset.rotation) || 0,
                                    zIndex: parseInt(item.style.zIndex) || 1
                                });
                            });

                            // Send data to server
                            const formData = new FormData();
                            formData.append('action', 'mg_save_gallery');
                            formData.append('gallery_id', window.mgEditorData.galleryId);
                            formData.append('gallery_data', JSON.stringify(galleryData));
                            formData.append('nonce', window.mgEditorData.nonce);

                            fetch(window.mgEditorData.ajaxUrl, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert("Gallery saved successfully!");
                                        if (window.mgEditorData.galleryId === 0) {
                                            window.mgEditorData.galleryId = data.data.gallery_id;
                                            window.history.replaceState({}, "", window.location.href + "&gallery_id=" + window.mgEditorData.galleryId);
                                        }
                                    } else {
                                        alert("Error saving gallery");
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert("Error saving gallery");
                                });
                        });

                        // Add media items to canvas
                        document.querySelectorAll('.media-item').forEach(function(item) {
                            item.addEventListener('click', function() {
                                const mediaId = this.dataset.mediaId;
                                const mediaItem = window.mgEditorData.mediaItems.find(item => item.id == mediaId);
                                if (mediaItem) {
                                    addToCanvas(mediaItem);
                                }
                            });
                        });

                        // Add text/button
                        document.querySelector('.add-text').addEventListener('click', () => addNewItem('text'));
                        document.querySelector('.add-button').addEventListener('click', () => addNewItem('button'));

                        // Canvas item functions
                        function addToCanvas(mediaItem) {
                            const canvas = document.getElementById('mg-canvas');
                            const canvasItems = canvas.querySelectorAll('.canvas-item');

                            // Create new item
                            const newItem = document.createElement('div');
                            newItem.className = 'canvas-item';
                            newItem.dataset.itemId = `canvas_${Date.now()}`;
                            newItem.dataset.itemType = mediaItem.type;
                            newItem.dataset.itemTitle = mediaItem.title;
                            newItem.dataset.itemUrl = mediaItem.url;
                            newItem.dataset.rotation = '0';

                            newItem.style.left = '100px';
                            newItem.style.top = '100px';
                            newItem.style.width = '200px';
                            newItem.style.height = '150px';
                            newItem.style.zIndex = (canvasItems.length + 1).toString();

                            if (mediaItem.type === 'image') {
                                newItem.innerHTML = `
                            <div class="item-content">
                                <img src="${mediaItem.url}" alt="${mediaItem.title}">
                            </div>
                            <div class="item-controls" style="display: none;">
                                <button class="delete-handle">×</button>
                            </div>
                        `;
                            } else if (mediaItem.type === 'video') {
                                newItem.innerHTML = `
                            <div class="item-content">
                                <video src="${mediaItem.url}" controls></video>
                            </div>
                            <div class="item-controls" style="display: none;">
                                <button class="delete-handle">×</button>
                            </div>
                        `;
                            }

                            canvas.appendChild(newItem);
                            updateEmptyState();

                            // Add delete functionality
                            newItem.querySelector('.delete-handle').addEventListener('click', function(e) {
                                e.stopPropagation();
                                newItem.remove();
                                updateEmptyState();
                            });

                            // Add selection functionality
                            newItem.addEventListener('click', function(e) {
                                e.stopPropagation();
                                selectItem(this);
                            });
                        }

                        function addNewItem(type) {
                            const canvas = document.getElementById('mg-canvas');
                            const canvasItems = canvas.querySelectorAll('.canvas-item');

                            const newItem = document.createElement('div');
                            newItem.className = 'canvas-item';
                            newItem.dataset.itemId = `new_${Date.now()}`;
                            newItem.dataset.itemType = type;
                            newItem.dataset.itemTitle = `New ${type}`;

                            if (type === 'text') {
                                newItem.dataset.itemContent = "Sample text content";
                            } else if (type === 'button') {
                                newItem.dataset.itemContent = "Click me";
                            }

                            newItem.style.left = '150px';
                            newItem.style.top = '150px';
                            newItem.style.width = type === 'text' ? '300px' : '200px';
                            newItem.style.height = type === 'text' ? '100px' : '150px';
                            newItem.style.zIndex = (canvasItems.length + 1).toString();

                            if (type === 'text') {
                                newItem.innerHTML = `
                            <div class="item-content">
                                <div class="text-content">Sample text content</div>
                            </div>
                            <div class="item-controls" style="display: none;">
                                <button class="delete-handle">×</button>
                            </div>
                        `;
                            } else if (type === 'button') {
                                newItem.innerHTML = `
                            <div class="item-content">
                                <button class="button-content">Click me</button>
                            </div>
                            <div class="item-controls" style="display: none;">
                                <button class="delete-handle">×</button>
                            </div>
                        `;
                            }

                            canvas.appendChild(newItem);
                            updateEmptyState();
                            selectItem(newItem);

                            // Add delete functionality
                            newItem.querySelector('.delete-handle').addEventListener('click', function(e) {
                                e.stopPropagation();
                                newItem.remove();
                                updateEmptyState();
                                document.getElementById('properties-content').innerHTML = `
                            <div class="no-selection">
                                <p>Select an item on the canvas to edit its properties</p>
                            </div>
                        `;
                            });

                            // Add selection functionality
                            newItem.addEventListener('click', function(e) {
                                e.stopPropagation();
                                selectItem(this);
                            });
                        }

                        function selectItem(item) {
                            // Remove previous selection
                            document.querySelectorAll('.canvas-item').forEach(el => {
                                el.classList.remove('selected');
                                el.querySelector('.item-controls').style.display = 'none';
                            });

                            // Select new item
                            item.classList.add('selected');
                            item.querySelector('.item-controls').style.display = 'block';

                            // Update properties panel
                            const selectedInfo = document.getElementById('selected-info');
                            selectedInfo.textContent = item.dataset.itemTitle;

                            const propertiesContent = document.getElementById('properties-content');
                            propertiesContent.innerHTML = `
                        <div class="tab-navigation">
                            <button class="tab-button active" data-tab="content">Content</button>
                            <button class="tab-button" data-tab="design">Design</button>
                        </div>
                        
                        <div class="tab-content">
                            ${renderContentTab(item)}
                        </div>
                    `;

                            // Add tab switching
                            propertiesContent.querySelectorAll('.tab-button').forEach(button => {
                                button.addEventListener('click', function() {
                                    propertiesContent.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                                    this.classList.add('active');

                                    const tab = this.dataset.tab;
                                    propertiesContent.querySelector('.tab-content').innerHTML =
                                        tab === 'content' ? renderContentTab(item) : renderDesignTab(item);
                                });
                            });
                        }

                        function renderContentTab(item) {
                            let contentFields = '';
                            if (item.dataset.itemType === 'text') {
                                contentFields = `
                            <div class="control-group">
                                <label>Text Content</label>
                                <textarea id="item-content" rows="4">${item.dataset.itemContent || ''}</textarea>
                            </div>
                        `;
                            } else if (item.dataset.itemType === 'button') {
                                contentFields = `
                            <div class="control-group">
                                <label>Button Text</label>
                                <input type="text" id="item-content" value="${item.dataset.itemContent || ''}">
                            </div>
                        `;
                            }

                            return `
                        <div class="content-tab">
                            <div class="control-group">
                                <label>Position</label>
                                <div class="position-controls">
                                    <input type="number" id="item-x" value="${parseInt(item.style.left)}">
                                    <input type="number" id="item-y" value="${parseInt(item.style.top)}">
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label>Size</label>
                                <div class="size-controls">
                                    <input type="number" id="item-width" value="${parseInt(item.style.width)}">
                                    <input type="number" id="item-height" value="${parseInt(item.style.height)}">
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label>Rotation: ${item.dataset.rotation || 0}°</label>
                                <input type="range" id="item-rotation" min="0" max="360" value="${item.dataset.rotation || 0}">
                            </div>
                            
                            ${contentFields}
                            
                            <div class="control-group">
                                <button class="button update-item">Update Item</button>
                            </div>
                        </div>
                    `;
                        }

                        function renderDesignTab(item) {
                            return `
                        <div class="design-tab">
                            <div class="control-group">
                                <label>Background Color</label>
                                <input type="color" id="item-bg-color" value="#ffffff">
                            </div>
                            
                            <div class="control-group">
                                <label>Border Radius</label>
                                <input type="range" id="item-border-radius" min="0" max="50" value="0">
                            </div>
                            
                            <div class="control-group">
                                <label>Opacity</label>
                                <input type="range" id="item-opacity" min="0" max="100" value="100">
                            </div>
                            
                            <div class="control-group">
                                <button class="button update-item">Update Item</button>
                            </div>
                        </div>
                    `;
                        }

                        function updateEmptyState() {
                            const emptyCanvas = document.querySelector('.empty-canvas');
                            const canvasItems = document.querySelectorAll('.canvas-item');
                            emptyCanvas.style.display = canvasItems.length === 0 ? 'block' : 'none';
                        }
                    });
                </script>
            </div>
        </div>
<?php
    }

    private function create_database_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mg_galleries';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            gallery_data longtext NOT NULL,
            gallery_type varchar(50) DEFAULT 'grid',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
