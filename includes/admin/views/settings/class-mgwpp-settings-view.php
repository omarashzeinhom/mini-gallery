<?php
// File: includes/admin/views/class-mgwpp-settings-view.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Settings_View
{
    private $modules = [
        'testimonials_carousel' => 'Testimonials',
        'albums'                => 'Albums',
        'embed_editor'          => 'Embed Editor',
        'editor'                => 'Editor'
    ];

    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting('mgwpp_settings_group', 'mgwpp_enabled_modules', [
            'sanitize_callback' => [$this, 'sanitize_modules']
        ]);

        add_settings_section(
            'mgwpp_modules_section',
            __('', 'mini-gallery'),
            '__return_false',
            'mgwpp-settings'
        );

        foreach ($this->modules as $slug => $label) {
            add_settings_field(
                'mgwpp_enabled_' . $slug,
                '',
                [$this, 'module_field_callback'],
                'mgwpp-settings',
                'mgwpp_modules_section',
                ['slug' => $slug]
            );
        }
    }

    public function module_field_callback($args)
    {
        $option = get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $slug = $args['slug'];
        $is_active = in_array($slug, (array)$option);
        $asset_info = $this->get_module_asset_info($slug);
        $size_info = $this->format_size($asset_info['size']) . ' (' . count($asset_info['files']) . ' ' . _n('file', 'files', count($asset_info['files']), 'mini-gallery') . ')';

        echo '<div class="mgwpp-module-card ' . ($is_active ? 'active' : 'inactive') . '" data-module="' . esc_attr($slug) . '">';
        echo '<div class="mgwpp-module-header">';
        echo '<div class="mgwpp-module-icon">';
        echo '<img src="' . esc_url($this->get_module_icon($slug)) . '" alt="' . esc_attr($this->modules[$slug]) . '">';
        echo '</div>';
        echo '<div class="mgwpp-module-info">';
        echo '<h3>' . esc_html($this->modules[$slug]) . '</h3>';
        echo '<span class="mgwpp-module-size">' . esc_html($size_info) . '</span>';
        echo '</div>';
        echo '<label class="mgwpp-switch">';
        echo '<input type="checkbox" name="mgwpp_enabled_modules[]" value="' . esc_attr($slug) . '" ';
        checked($is_active);
        echo '>';
        echo '<span class="mgwpp-switch-slider round"></span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';
    }

    private function get_module_icon($module_slug)
    {
        $icons = [
            'testimonials_carousel' => 'testimonial.png',
            'albums' => 'album.png',
            'embed_editor' => 'build.png',
            'editor' => 'editor.png'
        ];
        
        $icon_filename = $icons[$module_slug] ?? 'default-module.png';
        return MG_PLUGIN_URL . '/includes/admin/images/modules-icons/' . $icon_filename;
    }

    public function render()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Plugin Optimization Settings', 'mini-gallery'); ?></h1>
            <p class="description"><?php esc_html_e('Disable unused features to optimize plugin performance', 'mini-gallery'); ?></p>
            
            <div class="mgwpp-enabled-modules">
                <div class="mgwpp-modules-grid">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('mgwpp_settings_group');
                        do_settings_sections('mgwpp-settings');
                        submit_button(__('Save Settings', 'mini-gallery'));
                        ?>
                    </form>
                </div>
            </div>
            
            <div class="mgwpp-performance-metrics">
                <h2><?php esc_html_e('Performance Impact', 'mini-gallery'); ?></h2>
                <?php $this->display_performance_metrics(); ?>
            </div>
        </div>
        <?php
        $this->enqueue_assets();
    }

    private function display_performance_metrics()
    {
        $enabled_modules = (array)get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $disabled_modules = array_diff(array_keys($this->modules), $enabled_modules);

        $enabled_size = 0;
        $disabled_size = 0;

        foreach ($this->modules as $slug => $name) {
            $info = $this->get_module_asset_info($slug);
            if (in_array($slug, $enabled_modules)) {
                $enabled_size += $info['size'];
            } else {
                $disabled_size += $info['size'];
            }
        }
        
        $total_size = $enabled_size + $disabled_size;
        $savings_percent = $total_size > 0 ? round(($disabled_size / $total_size) * 100) : 0;
        ?>
        <div class="performance-metrics-grid">
            <div class="metric-card">
                <h3><?php esc_html_e('Active Features', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo count($enabled_modules); ?></div>
                <div class="metric-size"><?php echo esc_html($this->format_size($enabled_size)); ?></div>
            </div>
            
            <div class="metric-card">
                <h3><?php esc_html_e('Disabled Features', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo count($disabled_modules); ?></div>
                <div class="metric-size"><?php echo esc_html($this->format_size($disabled_size)); ?></div>
            </div>
            
            <div class="metric-card">
                <h3><?php esc_html_e('Total Savings', 'mini-gallery'); ?></h3>
                <div class="metric-value"><?php echo esc_html($savings_percent); ?>%</div>
                <div class="metric-size"><?php echo esc_html($this->format_size($disabled_size)); ?></div>
            </div>
        </div>
        <?php
    }

    private function get_module_asset_info($module_slug)
    {
        $modules_path = MG_PLUGIN_PATH . 'includes/modules/';
        $public_path = MG_PLUGIN_PATH . 'public/';
        $total_size = 0;
        $files = [];

        $module_paths = [
            $modules_path . $module_slug,
            $public_path . 'js/mgwpp-' . $module_slug . '.js',
            $public_path . 'css/mgwpp-' . $module_slug . '.css',
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
        if ($size < 1024) return $size . ' B';
        if ($size < 1048576) return round($size / 1024) . ' KB';
        return round($size / 1048576, 1) . ' MB';
    }

    public function sanitize_modules($input)
    {
        return is_array($input) ? array_intersect($input, array_keys($this->modules)) : [];
    }

    public function enqueue_assets()
    {
        wp_enqueue_style(
            'mgwpp-settings-style',
            MG_PLUGIN_URL . "includes/admin/views/settings/mgwpp-settings-view.css",
            [],
            filemtime(MG_PLUGIN_PATH . "includes/admin/views/settings/mgwpp-settings-view.css")
        );
    }
}