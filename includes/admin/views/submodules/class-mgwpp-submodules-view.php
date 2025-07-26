<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_SubModules_View
{
    private $module_loader;
    private $all_sub_modules = [];
    private $enabled_sub_modules = [];

    public function __construct($module_loader)
    {
        $this->module_loader = $module_loader;
        $this->all_sub_modules = MGWPP_Module_Manager::get_all_sub_modules();
        $this->refresh_enabled_modules();
        
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_toggle_module_status', [$this, 'ajax_toggle_module']);
    }

    /**
     * Refresh enabled modules from database
     */
    private function refresh_enabled_modules()
    {
        $default_modules = array_keys($this->all_sub_modules);
        $this->enabled_sub_modules = (array) get_option('mgwpp_enabled_sub_modules', $default_modules);
        
        // Ensure only valid modules are enabled
        $this->enabled_sub_modules = array_intersect($this->enabled_sub_modules, $default_modules);
    }

    public function register_settings()
    {
        register_setting('mgwpp_submodules_group', 'mgwpp_enabled_sub_modules', [
            'sanitize_callback' => [$this, 'sanitize_sub_modules'],
            'default' => array_keys($this->all_sub_modules)
        ]);

        add_settings_section(
            'mgwpp_sub_modules_section',
            '',
            [$this, 'render_section_header'],
            'mgwpp-submodules-settings'
        );

        // Register fields for all available modules
        foreach ($this->all_sub_modules as $slug => $module) {
            add_settings_field(
                'mgwpp_enabled_' . $slug,
                '',
                [$this, 'module_field_callback'],
                'mgwpp-submodules-settings',
                'mgwpp_sub_modules_section',
                ['slug' => $slug, 'module' => $module]
            );
        }
    }

    public function render_section_header()
    {
        echo '<div class="mgwpp-stats-grid">';
    }

    public function module_field_callback($args)
    {
        $slug = $args['slug'];
        $module = $args['module'];
        $is_checked = in_array($slug, $this->enabled_sub_modules, true);
        ?>
        <div class="mgwpp-module-card<?php echo $is_checked ? ' active' : ''; ?>" data-module="<?php echo esc_attr($slug); ?>">
            <div class="module-header">
                <div class="module-icon">
                    <img src="<?php echo esc_url($this->get_gallery_icon($slug)); ?>"
                        alt="<?php echo esc_attr($module['config']['name']); ?>">
                </div>

                <div class="module-info">
                    <h3><?php echo esc_html($module['config']['name']); ?></h3>
                    <div class="module-meta">
                        <span class="version"><?php echo esc_html($module['config']['version'] ?? '1.0'); ?></span>
                        <span class="author"><?php echo esc_html($module['config']['author'] ?? 'MiniGallery'); ?></span>
                    </div>
                </div>

                <div class="module-actions">
                    <label class="mgwpp-switch">
                        <input type="checkbox"
                            class="mgwpp-module-toggle"
                            name="mgwpp_enabled_sub_modules[]"
                            value="<?php echo esc_attr($slug); ?>"
                            <?php checked($is_checked, true); ?>>
                        <span class="mgwpp-switch-slider round"></span>
                    </label>
                </div>
            </div>

            <div class="module-description">
                <?php echo esc_html($module['config']['description'] ?? 'No description available.'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get gallery icon URL with fallback
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

        $icon_filename = $icons[$gallery_type] ?? 'default-gallery.png';
        return MG_PLUGIN_URL . '/includes/admin/images/modules-icons/sub-modules/galleries/' . $icon_filename;
    }

    public function render()
    {
        ?>
        <div class="mgwpp-modules-view">
            <div id="mgwpp-notice-area"></div>

            <h1><?php esc_html_e('Gallery Modules', 'mini-gallery'); ?></h1>

            <?php $this->render_enabled_gallery_types(); ?>
            <?php $this->render_module_settings(); ?>
            <?php $this->render_performance_metrics(); ?>
        </div>
        <?php
    }

    /**
     * Render enabled gallery types section
     */
    private function render_enabled_gallery_types()
    {
        ?>
        <div class="mgwpp-gallery-types-header">
            <h2><?php esc_html_e('Enabled Gallery Types', 'mini-gallery'); ?></h2>
            <div class="mgwpp-enabled-gallery-types">
                <div class="mgwpp-stats-grid">
                    <?php foreach ($this->enabled_sub_modules as $slug) : 
                        if (!isset($this->all_sub_modules[$slug])) continue;
                        $module = $this->all_sub_modules[$slug];
                    ?>
                        <div class="mgwpp-stat-card" data-module="<?php echo esc_attr($slug); ?>">
                            <img src="<?php echo esc_url($this->get_gallery_icon($slug)); ?>"
                                alt="<?php echo esc_attr($module['config']['name']); ?>"
                                class="mgwpp-stat-card-icon">
                            <?php echo esc_html($module['config']['name']); ?>
                            <div class="mgwpp-switch">
                                <input type="checkbox" checked disabled>
                                <span class="mgwpp-switch-slider round"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mgwpp-save-wrapper">
                <button id="mgwpp-save-settings" class="button button-primary">
                    <?php esc_html_e('Save All Changes', 'mini-gallery'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render module settings section
     */
    private function render_module_settings()
    {
        ?>
        <div class="mgwpp-submodules-settings">
            <h2><?php esc_html_e('Module Settings', 'mini-gallery'); ?></h2>
            <form method="post" action="options.php" id="mgwpp-submodules-form">
                <?php
                settings_fields('mgwpp_submodules_group');
                do_settings_sections('mgwpp-submodules-settings');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render performance metrics section
     */
    private function render_performance_metrics()
    {
        ?>
        <div class="mgwpp-performance-metrics">
            <h2><?php esc_html_e('Performance Overview', 'mini-gallery'); ?></h2>
            <?php $this->display_performance_metrics(); ?>
        </div>
        <?php
    }

    /**
     * Display performance metrics with optimized calculations
     */
    private function display_performance_metrics()
    {
        $enabled_size = 0;
        $disabled_size = 0;
        $enabled_files = [];
        $disabled_files = [];
        $module_details = [];

        // Calculate metrics for all modules
        foreach ($this->all_sub_modules as $slug => $module) {
            $info = $this->get_module_asset_info($slug);
            $is_enabled = in_array($slug, $this->enabled_sub_modules, true);

            $module_details[$slug] = [
                'name' => $module['config']['name'],
                'status' => $is_enabled ? 'enabled' : 'disabled',
                'size' => $info['size'],
                'files' => $info['files']
            ];

            if ($is_enabled) {
                $enabled_size += $info['size'];
                $enabled_files = array_merge($enabled_files, $info['files']);
            } else {
                $disabled_size += $info['size'];
                $disabled_files = array_merge($disabled_files, $info['files']);
            }
        }

        $total_size = $enabled_size + $disabled_size;
        $savings_percent = $total_size > 0 ? round(($disabled_size / $total_size) * 100, 2) : 0;

        ?>
        <div class="performance-metrics-grid">
            <div class="metric-card">
                <h3><?php esc_html_e('Active Modules', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo count($this->enabled_sub_modules); ?></div>
                <div class="metric-size"><?php echo esc_html($this->format_size($enabled_size)); ?></div>
                <div class="metric-files"><?php echo count($enabled_files) . ' ' . esc_html(_n('file', 'files', count($enabled_files), 'mini-gallery')); ?></div>
            </div>

            <div class="metric-card">
                <h3><?php esc_html_e('Inactive Modules', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo count($this->all_sub_modules) - count($this->enabled_sub_modules); ?></div>
                <div class="metric-size"><?php echo esc_html($this->format_size($disabled_size)); ?></div>
                <div class="metric-files"><?php echo count($disabled_files) . ' ' . esc_html(_n('file', 'files', count($disabled_files), 'mini-gallery')); ?></div>
            </div>

            <div class="metric-card">
                <h3><?php esc_html_e('Performance Savings', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo esc_html($savings_percent); ?>%</div>
                <div class="metric-size"><?php echo esc_html($this->format_size($disabled_size)); ?></div>
                <div class="metric-description"><?php esc_html_e('of total assets', 'mini-gallery'); ?></div>
            </div>
        </div>

        <div class="module-asset-details">
            <h3><?php esc_html_e('Module Details', 'mini-gallery'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Module', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Status', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Files', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Size', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($module_details as $slug => $details) : ?>
                        <tr>
                            <td><?php echo esc_html($details['name']); ?></td>
                            <td>
                                <span class="module-status <?php echo esc_attr($details['status']); ?>">
                                    <?php echo $details['status'] === 'enabled' ? 
                                        esc_html__('Enabled', 'mini-gallery') : 
                                        esc_html__('Disabled', 'mini-gallery'); ?>
                                </span>
                            </td>
                            <td data-files="<?php echo esc_attr(wp_json_encode($details['files'])); ?>">
                                <?php echo count($details['files']); ?>
                            </td>
                            <td><?php echo esc_html($this->format_size($details['size'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Get module asset information with optimized file scanning
     */
    private function get_module_asset_info($module_slug)
    {
        static $cache = [];
        
        if (isset($cache[$module_slug])) {
            return $cache[$module_slug];
        }

        $total_size = 0;
        $files = [];

        // Define possible module paths
        $normalized_slug = str_replace('_', '-', $module_slug);
        $module_paths = [
            MG_PLUGIN_PATH . 'includes/gallery-types/mgwpp-' . $normalized_slug,
            MG_PLUGIN_PATH . 'public/js/mgwpp-' . $normalized_slug . '.js',
            MG_PLUGIN_PATH . 'public/css/mgwpp-' . $normalized_slug . '.css',
        ];

        foreach ($module_paths as $path) {
            if (is_dir($path)) {
                try {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile() && $file->isReadable()) {
                            $total_size += $file->getSize();
                            $files[] = $file->getPathname();
                        }
                    }
                } catch (Exception $e) {
                    error_log('Error scanning module directory: ' . $e->getMessage());
                }
            } elseif (file_exists($path) && is_readable($path)) {
                $total_size += filesize($path);
                $files[] = $path;
            }
        }

        $result = ['size' => $total_size, 'files' => array_unique($files)];
        $cache[$module_slug] = $result;
        
        return $result;
    }

    /**
     * Format file size for display
     */
    private function format_size($size)
    {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min(floor(log($size, 1024)), count($units) - 1);
        
        return number_format($size / pow(1024, $power), $power > 0 ? 2 : 0) . ' ' . $units[$power];
    }

    /**
     * Sanitize sub modules input
     */
    public function sanitize_sub_modules($input)
    {
        if (!is_array($input)) {
            return [];
        }

        $valid_modules = array_keys($this->all_sub_modules);
        $sanitized = array_map('sanitize_key', $input);
        
        return array_values(array_intersect($sanitized, $valid_modules));
    }
    public function ajax_toggle_module()
    {
        try {
            // 1. Verify nonce exists
            if (!isset($_POST['nonce'])) {
                wp_send_json_error([
                    'message' => 'Missing security nonce',
                    'received_data' => $_POST
                ], 400);
            }

            // 2. Verify nonce validity - FIXED SYNTAX
            $nonce_value = sanitize_key($_POST['nonce']);
            if (!wp_verify_nonce($nonce_value, 'module_toggle_nonce')) {
                wp_send_json_error([
                    'message' => 'Invalid security nonce',
                    'expected_action' => 'module_toggle_nonce'
                ], 403);
            }

            // 3. Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Insufficient permissions', 403);
            }

            // 4. Validate request type
            $is_bulk = isset($_POST['is_bulk']) && $_POST['is_bulk'] === '1';
            $enabled_modules = get_option('mgwpp_enabled_sub_modules', array_keys($this->all_sub_modules));

            if ($is_bulk) {
                // 5A. Bulk update validation
                $modules = isset($_POST['modules']) ? array_map('sanitize_text_field', wp_unslash((array)$_POST['modules'])) : [];

                if (empty($modules)) {
                    wp_send_json_error('Missing modules array for bulk update', 400);
                }

                // 6A. Validate modules
                $valid_modules = array_keys($this->all_sub_modules);
                $modules = array_intersect($modules, $valid_modules);
                update_option('mgwpp_enabled_sub_modules', $modules);
                $enabled_modules = $modules;
            } else {
                // 5B. Single toggle validation
                if (!isset($_POST['module'])) {
                    wp_send_json_error('Missing module parameter', 400);
                }

                if (!isset($_POST['status'])) {
                    wp_send_json_error('Missing status parameter', 400);
                }

                // 6B. Sanitize inputs
                $module = sanitize_key($_POST['module']);
                $status = filter_var($_POST['status'], FILTER_VALIDATE_BOOLEAN);

                // 7. Validate module exists
                if (!isset($this->all_sub_modules[$module])) {
                    wp_send_json_error([
                        'message' => 'Invalid module requested',
                        'valid_modules' => array_keys($this->all_sub_modules)
                    ], 400);
                }

                // 8. Update status
                if ($status) {
                    if (!in_array($module, $enabled_modules)) {
                        $enabled_modules[] = $module;
                    }
                } else {
                    $enabled_modules = array_diff($enabled_modules, [$module]);
                }
                update_option('mgwpp_enabled_sub_modules', $enabled_modules);
            }

            // 9. Generate updated performance metrics HTML
            ob_start();
            $this->display_performance_metrics();
            $metrics_html = ob_get_clean();

            // 10. Return success with diagnostics
            wp_send_json_success([
                'enabled_modules' => $enabled_modules,
                'metrics' => $metrics_html,
                'total_modules' => count($this->all_sub_modules),
                'is_bulk' => $is_bulk
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Server error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function enqueue_assets()
    {
        // Only enqueue on the submodules page
        $screen = get_current_screen();
        if ($screen->id !== 'mgwpp_dashboard_page_mgwpp-submodules') {
            return;
        }

        wp_enqueue_style(
            'mgwpp-modules-styles',
            MG_PLUGIN_URL . "/includes/admin/views/submodules/mgwpp-submodules-view.css",
            [],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/submodules/mgwpp-submodules-view.css")
        );

        wp_enqueue_script(
            'mgwpp-modules-scripts',
            MG_PLUGIN_URL . "/includes/admin/views/submodules/mgwpp-submodules-view.js",
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/submodules/mgwpp-submodules-view.js"),
            true
        );

        // Get current enabled modules for JS
        $enabled_modules = get_option('mgwpp_enabled_sub_modules', array_keys($this->all_sub_modules));

        wp_localize_script('mgwpp-modules-scripts', 'MGWPPData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('module_toggle_nonce'),
            'genericError' => __('An error occurred. Please try again.', 'mini-gallery'),
            'enabledModules' => $enabled_modules,
            'saveText' => __('Save All Changes', 'mini-gallery')
        ]);
    }
}