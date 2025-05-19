<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Modules_View
{
    private $module_loader;
    private $modules = [];

    public function __construct($module_loader)
    {
        $this->module_loader = $module_loader;
        $this->modules = $module_loader->get_modules();
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }


    public function register_settings()
    {
        register_setting('mgwpp_settings_group', 'mgwpp_enabled_modules', [
            'sanitize_callback' => [$this, 'sanitize_modules']
        ]);

        // Modified settings section registration
        add_settings_section(
            'mgwpp_modules_section',
            '', // Empty title
            '__return_false', // No callback
            'mgwpp-settings'
        );


        foreach ($this->modules as $slug => $module) {
            add_settings_field(
                'mgwpp_enabled_' . $slug,
                '',
                [$this, 'module_field_callback'],
                'mgwpp-settings',
                'mgwpp_modules_section',
                ['slug' => $slug, 'module' => $module]
            );
        }
    }


    public function section_callback()
    {
        // Empty callback to prevent default table output
        echo '<div class="mgwpp-modules-grid">';
    }

    public function module_field_callback($args)
    {
        $slug = $args['slug'];
        $option = get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $is_checked = in_array($slug, (array)$option);
        $asset_info = $this->get_module_asset_info($slug);

        // In module_field_callback() function
        echo '<div class="mgwpp-module-card ' . '" data-module="' . esc_attr($slug) . '">';
        echo '<div class="module-header">';
        echo '<div class="module-icon">';
        echo '<img src="' . esc_url($this->get_gallery_icon($slug)) . '" alt="' . esc_attr($this->modules[$slug]['config']['name']) . '">';
        echo '</div>';
        echo '<div class="module-info">';
        echo '<h3>' . esc_html($this->modules[$slug]['config']['name']) . '</h3>';
        echo '<div class="module-meta">';
        echo '<span class="version">' . esc_html($this->modules[$slug]['config']['version']) . '</span>';
        echo '<span class="author">' . esc_html($this->modules[$slug]['config']['author']) . '</span>';
        echo '</div>'; // Close module-meta
        echo '</div>'; // Close module-info
        echo '<div class="module-actions">';
        echo '<label class="mgwpp-switch">';
        echo '<input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_modules[]" value="' . esc_attr($slug) . '" ' . checked($is_checked, true, false) . '>';
        echo '<span class="mgwpp-switch-slider round"></span>';
        echo '</label>';
        echo '</div>'; // Close module-actions
        echo '</div>'; // Close module-header
        echo '<div class="module-description">';
        echo esc_html($this->modules[$slug]['config']['description'] ?? '');
        echo '</div>';
        echo '</div>'; // Close mgwpp-module-card


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
        return MG_PLUGIN_URL . '/includes/admin/images/modules-icons/galleries/' . $icon_filename;
    }

    public function render()
    {
        $enabled_modules = get_option('mgwpp_enabled_modules', array_keys($this->modules));
?>
        <div class="wrap">
            <h1><?php esc_html_e('Gallery Modules', 'mini-gallery'); ?></h1>

            <!-- Enabled Modules Header -->
            <div class="mgwpp-gallery-types-header">
                <h2><?php esc_html_e('Enabled Gallery Types', 'mini-gallery'); ?></h2>
                <?php foreach ($enabled_modules as $module_slug) :
                    $module = $this->modules[$module_slug] ?? null;
                    if (!$module) continue;
                ?>
                    <div class="mgwpp-gallery-type-badge" data-module="<?php echo esc_attr($module_slug); ?>">
                        <img src="<?php echo esc_url($this->get_gallery_icon($module_slug)); ?>"
                            alt="<?php echo esc_attr($module['config']['name']); ?>"
                            class="mgwpp-gallery-type-icon" />
                        <?php echo esc_html($module['config']['name']); ?>
                        <div class="mgwpp-switch">
                            <input type="checkbox"
                                <?php checked(true); ?>
                                disabled="disabled">
                            <span class="mgwpp-switch-slider round"></span>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <!-- Main Modules Grid -->
                    <form method="post" action="options.php" class="mgwpp-modules-grid">
                        <div class="mgwpp-modules-grid">
                            <?php settings_fields('mgwpp_settings_group'); ?>

                            <?php do_settings_sections('mgwpp-settings'); ?>
                        </div>


                        <?php submit_button(__('Save Module Settings', 'mini-gallery'), 'primary', 'submit', true, [
                            'style' => 'margin-top: 20px; margin-left: 20px;'
                        ]); ?>
                    </form>

                    <!-- Performance Metrics -->
                    <div class="mgwpp-performance-metrics">
                        <h2><?php esc_html_e('Performance Overview', 'mini-gallery'); ?></h2>
                        <?php $this->display_performance_metrics(); ?>
                    </div>
            </div>

        </div>

    <?php
    }

    private function display_performance_metrics()
    {
        $enabled_modules = (array)get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $disabled_modules = array_diff(array_keys($this->modules), $enabled_modules);

        $enabled_size = 0;
        $disabled_size = 0;
        $enabled_files = [];
        $disabled_files = [];

        foreach (array_keys($this->modules) as $module) {
            $info = $this->get_module_asset_info($module);

            if (in_array($module, $enabled_modules)) {
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
        <div class="performance-metric">
            <div class="performance-metric-value">
                <?php echo count($enabled_modules) . ' ' . esc_html(_n('module', 'modules', count($enabled_modules), 'mini-gallery')); ?>
                (<?php echo esc_html($this->format_size($enabled_size)); ?> / <?php echo count($enabled_files) . ' ' . esc_html(_n('file', 'files', count($enabled_files), 'mini-gallery')); ?>)
            </div>
        </div>
        <div class="performance-metric">
            <div class="performance-metric-label"><?php esc_html_e('Disabled Modules:', 'mini-gallery'); ?></div>
            <div class="performance-metric-value">
                <?php echo count($disabled_modules) . ' ' . esc_html(_n('module', 'modules', count($disabled_modules), 'mini-gallery')); ?>
                (<?php echo esc_html($this->format_size($disabled_size)); ?> / <?php echo count($disabled_files) . ' ' . esc_html(_n('file', 'files', count($disabled_files), 'mini-gallery')); ?>)
            </div>
        </div>
        <div class="performance-metric">
            <div class="performance-metric-label"><?php esc_html_e('Performance Savings:', 'mini-gallery'); ?></div>
            <div class="performance-metric-value">
                <?php echo esc_html($this->format_size($disabled_size)); ?> (<?php echo esc_html($savings_percent); ?>% of total assets)
            </div>
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

    private function sanitize_modules($input)
    {
        $valid_modules = array_keys($this->modules);
        return is_array($input) ? array_intersect($input, $valid_modules) : [];
    }

    public function enqueue_assets()
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
}
