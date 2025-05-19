<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Modules_View
{
    private $module_loader;

    public function __construct($module_loader)
    {
        $this->module_loader = $module_loader;
        add_action('admin_post_save_mgwpp_modules', [$this, 'save_modules']);
        add_action('wp_ajax_toggle_module_status', [$this, 'ajax_toggle_module']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Get gallery type icon
     * 
     * @param string $gallery_type The gallery type slug
     * @return string The icon URL or default icon if not found
     */
    private function get_gallery_icon($gallery_type)
    {
        $icons = [
            'single_carousel' => 'single-gallery.png',
            'multi_carousel' => 'multi-gallery.png',
            'grid' => 'grid.png',
            'mega_slider' => 'mega-carousel.png',
            'pro_carousel' => 'pro-carousel.png',
            'neon_carousel' => 'neon-carousel.png',
            'threed_carousel' => '3d-carousel.png',
            'testimonials_carousel' => 'testimonial.png',
            'lightbox' => 'lightbox.png',
            'fullpage_slider' => 'fullpage-slider.png',
            'spotlight_slider' => 'spotlight-carousel.png',
            'albums' => 'albums.png',
        ];

        $icon_filename = isset($icons[$gallery_type]) ? $icons[$gallery_type] : 'default-gallery.png';
        return MG_PLUGIN_URL . '/includes/admin/images/modules-icons/galleries/' . $icon_filename;
    }

    /**
     * Get enabled modules from database
     * If none are found, return default set
     */
    private function get_enabled_modules()
    {
        $enabled_modules = get_option('mgwpp_enabled_modules', []);

        // If empty, set default modules
        if (empty($enabled_modules)) {
            $enabled_modules = [
                'single_carousel',
                'multi_carousel',
                'grid',
                'mega_slider',
                'pro_carousel',
                'neon_carousel',
                'threed_carousel',
                'testimonials_carousel',
                'lightbox',
                'fullpage_slider',
                'spotlight_slider',
                'albums'
            ];
            update_option('mgwpp_enabled_modules', $enabled_modules);
        }

        return $enabled_modules;
    }

    public function render()
    {
        $modules = $this->module_loader->get_modules();
        $enabled_modules = $this->get_enabled_modules();
        // Debug output
        echo '<pre style="background:#fff;padding:20px;margin:20px;border:2px solid red">';
        echo 'Enabled Modules: ';
        print_r($enabled_modules);
        echo 'All Modules: ';
        print_r(array_keys($modules));
        echo '</pre>';

?>
        <div class="wrap">

            <h1><?php esc_html_e('Gallery Modules', 'mini-gallery'); ?></h1>

            <div class="mgwpp-gallery-types-header">
                <h2><?php esc_html_e('Enabled Gallery Types', 'mini-gallery'); ?></h2>
                <div class="mgwpp-enabled-gallery-types">
                    <?php foreach ($enabled_modules as $module_slug) : ?>
                        <div class="mgwpp-gallery-type-badge" data-module="<?php echo esc_attr($module_slug); ?>">
                            <img src="<?php echo esc_url($this->get_gallery_icon($module_slug)); ?>"
                                alt="<?php echo esc_attr(str_replace('_', ' ', ucfirst($module_slug))); ?>"
                                class="mgwpp-gallery-type-icon" />
                            <?php echo esc_html(str_replace('_', ' ', ucfirst($module_slug))); ?>
                            <label class="mgwpp-switch">
                                <input type="checkbox" <?php checked(true); ?>>
                                <span class="mgwpp-switch-slider round"></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mgwpp-modules-grid">
                <?php foreach ($modules as $slug => $module) :
                    $is_active = in_array($slug, $enabled_modules);
                    $status_class = $is_active ? 'active' : 'inactive';
                ?>
                    <div class="mgwpp-module-card <?php echo esc_attr($status_class); ?>" data-module="<?php echo esc_attr($slug); ?>">
                        <div class="module-header">
                            <div class="module-icon">
                                <img src="<?php echo esc_url($this->get_gallery_icon($slug)); ?>"
                                    alt="<?php echo esc_attr($module['config']['name']); ?>" />
                            </div>
                            <h3><?php echo esc_html($module['config']['name']); ?></h3>
                            <div class="module-actions">
                                <div class="mgwpp-switch">
                                    <input type="checkbox"
                                        id="module-toggle-<?php echo esc_attr($slug); ?>"
                                        class="mgwpp-module-toggle"
                                        <?php checked($is_active); ?>>
                                    <label for="module-toggle-<?php echo esc_attr($slug); ?>" class="slider round"></label>
                                </div>
                            </div>
                        </div>

                        <div class="module-meta">
                            <span class="version"><?php echo esc_html($module['config']['version']); ?></span>
                            <span class="author"><?php echo esc_html($module['config']['author']); ?></span>
                        </div>

                        <div class="module-description">
                            <?php echo esc_html($module['config']['description'] ?? ''); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
<?php
    }

    public function enqueue_assets($hook)
    {
        wp_enqueue_style(
            'mgwpp-modules-view-style',
            MG_PLUGIN_URL . "/includes/admin/views/modules/mgwpp-modules-view.css",
            [],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/modules/mgwpp-modules-view.css")
        );

        wp_enqueue_script(
            'mgwpp-modules-view-script',
            MG_PLUGIN_URL . "/includes/admin/views/modules/mgwpp-modules-view.js",
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/modules/mgwpp-modules-view.js"),
            true
        );

        // Localize script for AJAX
        wp_localize_script('mgwpp-modules-view-script', 'MGWPPData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('module_toggle_nonce')
        ]);
    }

    public function ajax_toggle_module()
    {
        check_ajax_referer('module_toggle_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'mini-gallery'), 403);
        }

        $module = sanitize_text_field($_POST['module'] ?? '');
        $status = (bool) ($_POST['status'] ?? false);

        $enabled_modules = get_option('mgwpp_enabled_modules', []);

        if ($status) {
            if (!in_array($module, $enabled_modules)) {
                $enabled_modules[] = $module;
            }
        } else {
            $enabled_modules = array_diff($enabled_modules, [$module]);
        }

        update_option('mgwpp_enabled_modules', $enabled_modules);
        wp_send_json_success();
    }

    public function save_modules()
    {
        // Legacy form handler (optional)
    }
}
