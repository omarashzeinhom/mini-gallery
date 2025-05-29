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
        add_action('admin_menu', array($this, 'add_admin_menu'));
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

    public function add_admin_menu()
    {
        add_menu_page(
            'Visual Gallery Editor',
            'Gallery Editor',
            'manage_options',
            'mg-visual-editor',
            array($this, 'admin_page'),
            'dashicons-format-gallery',
            30
        );

        add_submenu_page(
            'mg-visual-editor',
            'All Galleries',
            'All Galleries',
            'manage_options',
            'mg-all-galleries',
            array($this, 'all_galleries_page')
        );
    }

    public function enqueue_admin_scripts($hook)
    {
        if (strpos($hook, 'mg-visual-editor') === false) {
            return;
        }

        wp_enqueue_script(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/js/visual-editor.js',
            array('jquery', 'wp-util'),
            MGWPP_ASSET_VERSION,
            true
        );

        wp_enqueue_style(
            'mg-visual-editor',
            MG_PLUGIN_URL . 'editor/assets/css/visual-editor.css',
            array(),
            MGWPP_ASSET_VERSION
        );

        wp_localize_script('mg-visual-editor', 'mgAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mg_visual_editor_nonce'),
            'pluginUrl' => MG_PLUGIN_URL
        ));
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

            <div id="mg-visual-editor-app">
                <div class="editor-loading">
                    <p><?php _e('Loading Visual Editor...', 'mini-gallery'); ?></p>
                </div>
            </div>

            <script type="text/javascript">
                window.mgEditorData = <?php echo wp_json_encode($editor_data); ?>;
            </script>
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
