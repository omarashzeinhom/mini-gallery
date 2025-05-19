<?php
// File: includes/admin/views/class-mgwpp-settings-view.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Settings_View
{
    private $modules = [
        'single_carousel'  => 'Single Carousel',
        'multi_carousel'   => 'Multi Carousel',
        'grid'             => 'Grid Gallery',
        'mega_slider'      => 'Mega Slider',
        'pro_carousel'     => 'Pro Carousel',
        'neon_carousel'    => 'Neon Carousel',
        'threed_carousel'  => '3D Carousel',
        'testimonials_carousel' => 'Testimonials Carousel',
        'lightbox'         => 'Lightbox',
        'fullpage_slider'  => 'FullPage Slider',
        'spotlight_slider' => 'Spotlight Slider',
        'albums'          => 'Albums'
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
            '__return_false', // No callback
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
        echo '<img src="' . esc_url($this->get_gallery_icon($slug)) . '" alt="' . esc_attr($this->modules[$slug]) . '">';
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

    public function render()
    {
?>
        <div class="wrap">
                            <h1><?php esc_html_e('Gallery Settings', 'mini-gallery'); ?></h1>

            <div class="mgwpp-enabled-gallery-types">
                <div class="mgwpp-modules-grid">
                        <form method="post" action="options.php">
                            <?php
                            settings_fields('mgwpp_settings_group');
                            do_settings_sections('mgwpp-settings');
                            submit_button();
                            ?>
                        </form>
                </div>
            </div>
            
            <div class="mgwpp-performance-metrics">
                <h2><?php esc_html_e('Performance Overview', 'mini-gallery'); ?></h2>
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

    public function sanitize_modules($input)
    {
        return is_array($input) ? array_intersect($input, array_keys($this->modules)) : [];
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
