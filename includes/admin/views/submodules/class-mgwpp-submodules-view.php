<?php
if (!defined('ABSPATH')) exit;

class MGWPP_SubModules_View
{
    private $module_loader;
    private $sub_modules = [];

    public function __construct($module_loader)
    {
        $this->module_loader = $module_loader;
        $this->sub_modules = $module_loader->get_sub_modules();
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_toggle_module_status', [$this, 'ajax_toggle_module']);
    }

    public function register_settings()
    {
        register_setting('mgwpp_submodules_group', 'mgwpp_enabled_sub_modules', [
            'sanitize_callback' => [$this, 'sanitize_sub_modules']
        ]);

        add_settings_section(
            'mgwpp_sub_modules_section',
            '',
            [$this, 'render_section_header'],
            'mgwpp-submodules-settings'
        );

        foreach ($this->sub_modules as $slug => $module) {
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

    public function render_section_footer()
    {
        echo '</div>';
    }

    public function module_field_callback($args)
    {
        $slug = $args['slug'];
        $option = get_option('mgwpp_enabled_sub_modules', array_keys($this->sub_modules));
        $is_checked = in_array($slug, (array)$option);
        $module = $this->sub_modules[$slug];
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
                        <span class="version"><?php echo esc_html($module['config']['version']); ?></span>
                        <span class="author"><?php echo esc_html($module['config']['author']); ?></span>
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
                <?php echo esc_html($module['config']['description'] ?? ''); ?>
            </div>
        </div>
    <?php
    }

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
        $enabled_sub_modules = get_option('mgwpp_enabled_sub_modules', array_keys($this->sub_modules));
    ?>
        <div class="mgwpp-modules-view">
            <h1><?php esc_html_e('Gallery Modules', 'mini-gallery'); ?></h1>

            <div class="mgwpp-gallery-types-header">
                <h2><?php esc_html_e('Enabled Gallery Types', 'mini-gallery'); ?></h2>
                <div class="mgwpp-enabled-gallery-types">
                    <div class="mgwpp-stats-grid">
                        <?php foreach ($enabled_sub_modules as $slug) :
                            if (!isset($this->sub_modules[$slug])) continue;
                            $module = $this->sub_modules[$slug];
                        ?>
                            <div class="mgwpp-stat-card" data-module="<?php echo esc_attr($slug); ?>">
                                <img src="<?php echo esc_url($this->get_gallery_icon($slug)); ?>"
                                    alt="<?php echo esc_attr($module['config']['name']); ?>"
                                    class="mgwpp-stat-card-icon">
                                <?php echo esc_html($module['config']['name']); ?>
                                <div class="mgwpp-switch">
                                    <input type="checkbox" <?php checked(true); ?> disabled>
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
            <div class="mgwpp-performance-metrics">
                <h2><?php esc_html_e('Performance Overview', 'mini-gallery'); ?></h2>
                <?php $this->display_performance_metrics(); ?>
            </div>
        </div>
    <?php
    }

    private function display_performance_metrics()
    {
        $enabled_sub_modules = (array)get_option('mgwpp_enabled_sub_modules', array_keys($this->sub_modules));
        $disabled_sub_modules = array_diff(array_keys($this->sub_modules), $enabled_sub_modules);

        $enabled_size = 0;
        $disabled_size = 0;
        $enabled_files = [];
        $disabled_files = [];

        // Detailed module tracking
        $module_details = [];

        foreach ($this->sub_modules as $slug => $module) {
            $info = $this->get_module_asset_info($slug);
            $status = in_array($slug, $enabled_sub_modules) ? 'enabled' : 'disabled';

            $module_details[$slug] = [
                'name' => $module['config']['name'],
                'status' => $status,
                'size' => $info['size'],
                'files' => $info['files']
            ];

            if ($status === 'enabled') {
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
                <div class="metric-value"><?php echo count($enabled_sub_modules); ?></div>
                <div class="metric-size"><?php echo esc_html($this->format_size($enabled_size)); ?></div>
                <div class="metric-files"><?php echo count($enabled_files) . ' ' . esc_html(_n('file', 'files', count($enabled_files), 'mini-gallery')); ?></div>
            </div>

            <div class="metric-card">
                <h3><?php esc_html_e('Inactive Modules', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo count($disabled_sub_modules); ?></div>
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
                                    <?php echo $details['status'] === 'enabled' ? esc_html__('Enabled', 'mini-gallery') : esc_html__('Disabled', 'mini-gallery'); ?>
                                </span>
                            </td>
                            <td><?php echo count($details['files']); ?></td>
                            <td><?php echo esc_html($this->format_size($details['size'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }

    private function get_module_asset_info($module_slug)
    {
        $gallery_types_path = MG_PLUGIN_PATH . 'includes/gallery-types/';
        $public_path = MG_PLUGIN_PATH . 'public/';
        $total_size = 0;
        $files = [];

        $module_paths = [
            $gallery_types_path . 'mgwpp-' . str_replace('_', '-', $module_slug),
            $public_path . 'js/mgwpp-' . str_replace('_', '-', $module_slug) . '.js',
            $public_path . 'css/mgwpp-' . str_replace('_', '-', $module_slug) . '.css',
        ];

        foreach ($module_paths as $path) {
            if (is_dir($path)) {
                $dir_files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($dir_files as $file) {
                    if ($file->isFile()) {
                        $total_size += $file->getSize();
                        $files[] = $file->getPathname();
                    }
                }
            } elseif (file_exists($path)) {
                $total_size += filesize($path);
                $files[] = $path;
            }
        }

        return ['size' => $total_size, 'files' => $files];
    }

    private function format_size($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    public function sanitize_sub_modules($input)
    {
        $valid_sub_modules = array_keys($this->sub_modules);
        return is_array($input) ? array_intersect($input, $valid_sub_modules) : [];
    }

    public function enqueue_assets()
    {
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

        wp_localize_script('mgwpp-modules-scripts', 'MGWPPData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('module_toggle_nonce'),
            'genericError' => __('An error occurred. Please try again.', 'mini-gallery')
        ]);
    }
    public function ajax_toggle_module()
    {
        try {
            // Verify nonce and capabilities (existing code)

            // Handle bulk operations
            if (isset($_POST['is_bulk']) && $_POST['is_bulk']) {
                $modules = isset($_POST['modules']) ? (array)$_POST['modules'] : [];
                $valid_modules = array_keys($this->sub_modules);
                $enabled_sub_modules = array_intersect($modules, $valid_modules);
            }
            // Handle single toggle
            else {
                $module = sanitize_text_field($_POST['module'] ?? '');
                $status = (bool)($_POST['status'] ?? false);

                if (!array_key_exists($module, $this->sub_modules)) {
                    throw new Exception(__('Invalid module specified', 'mini-gallery'));
                }

                $enabled_sub_modules = get_option('mgwpp_enabled_sub_modules', array_keys($this->sub_modules));

                if ($status && !in_array($module, $enabled_sub_modules)) {
                    $enabled_sub_modules[] = $module;
                } elseif (!$status && ($key = array_search($module, $enabled_sub_modules)) !== false) {
                    unset($enabled_sub_modules[$key]);
                }

                $enabled_sub_modules = array_values($enabled_sub_modules);
            }

            // Save and return response
            if (update_option('mgwpp_enabled_sub_modules', $enabled_sub_modules)) {
                wp_send_json_success([
                    'metrics' => $this->get_performance_metrics_html()
                ]);
            } else {
                throw new Exception(__('No changes were made to settings', 'mini-gallery'));
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage(), 400);
        }
    }
    private function get_performance_metrics_html()
    {
        ob_start();
        $this->display_performance_metrics();
        return ob_get_clean();
    }
}
