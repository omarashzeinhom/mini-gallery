<?php

if (!defined('ABSPATH')) {
    exit();
}

class MGWPP_Settings
{
    private $modules = [
        'single_carousel' => 'Single Carousel',
        'multi_carousel' => 'Multi Carousel',
        'grid' => 'Grid Gallery',
        'mega_slider' => 'Mega Slider',
        'pro_carousel' => 'Pro Carousel',
        'neon_carousel' => 'Neon Carousel',
        'threed_carousel' => '3D Carousel',
        'testimonials_carousel' => 'Testimonials Carousel',
        'lightbox' => 'Lightbox',
        'fullpage_slider' => 'FullPage Slider',
        'spotlight_slider' => 'Spotlight Slider',
        'albums' => 'Albums'
    ];

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=mgwpp_gallery',
            'Gallery Settings',
            'Settings',
            'manage_options',
            'mgwpp-settings',
            array($this, 'render_settings_page')
        );
    }

public function register_settings() {
        register_setting('mgwpp_settings_group', 'mgwpp_enabled_modules');

        // Add section with custom callback
        add_settings_section(
            'mgwpp_modules_section',
            '',
            [$this, 'section_callback'],
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

    public function checkbox_field_callback($args)
    {
        $option = get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $slug = $args['slug'];
        $is_checked = in_array($slug, (array)$option);
        
        // Calculate file size when checked
        $asset_info = $this->get_module_asset_info($slug);
        
        echo '<div class="module-toggle-wrapper">';
        echo '<input type="checkbox" id="module-' . esc_attr($slug) . '" name="mgwpp_enabled_modules[]" value="' . esc_attr($slug) . '" ' . checked($is_checked, true, false) . ' />';
        echo '<label for="module-' . esc_attr($slug) . '">' . esc_html($this->modules[$slug]) . '</label>';
        
        if ($asset_info['size'] > 0) {
            echo '<span class="module-size-info">' . esc_html($this->format_size($asset_info['size'])) . ' (' . 
                  count($asset_info['files']) . ' ' . _n('file', 'files', count($asset_info['files']), 'mini-gallery') . ')</span>';
        }
        
        echo '</div>';
    }
    
    private function get_module_asset_info($module_slug) {
        $gallery_types_path = MG_PLUGIN_PATH . 'includes/gallery-types/';
        $public_path = MG_PLUGIN_PATH . 'public/';
        $total_size = 0;
        $files = [];
        
        // Module specific paths based on slug
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
                        $size = $file->getSize();
                        $total_size += $size;
                        $files[] = $file->getPathname();
                    }
                }
            } elseif (file_exists($path)) {
                $size = filesize($path);
                $total_size += $size;
                $files[] = $path;
            }
        }
        
        return [
            'size' => $total_size,
            'files' => $files
        ];
    }
    
    private function format_size($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Gets the list of enabled modules
     * @return array Array of enabled module slugs
     */
    public static function get_enabled_modules() {
        $default_modules = array(
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
        );
        
        return get_option('mgwpp_enabled_modules', $default_modules);
    }
}





if (is_admin()) {
    new MGWPP_Settings();
}

