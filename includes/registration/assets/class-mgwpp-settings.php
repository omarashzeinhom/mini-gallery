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

    public function register_settings()
    {
        register_setting('mgwpp_settings_group', 'mgwpp_enabled_modules');

        add_settings_section(
            'mgwpp_modules_section',
            'Enabled Modules',
            array($this, 'modules_section_callback'),
            'mgwpp-settings'
        );

        foreach ($this->modules as $slug => $label) {
            add_settings_field(
                'mgwpp_enabled_' . $slug,
                $label,
                array($this, 'checkbox_field_callback'),
                'mgwpp-settings',
                'mgwpp_modules_section',
                array('slug' => $slug)
            );
        }
    }

    public function modules_section_callback() {
        echo '<p>Enable or disable gallery modules as needed. Disabling unused modules will improve performance by reducing the amount of loaded assets.</p>';
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

/**
 * Asset Manager class for handling gallery module assets
 */
class MGWPP_Assets {
    private static $load_assets = false;
    private static $shortcode_gallery_type = '';
    private $asset_registry = [];

    private function get_enabled_modules() {
        return MGWPP_Settings::get_enabled_modules();
    }

    public static function enable_assets() {
        self::$load_assets = true;
    }

    public static function set_gallery_type($type) {
        self::$shortcode_gallery_type = $type;
    }

    public function register_assets() {
        $enabled = $this->get_enabled_modules();
        $base_url = MG_PLUGIN_URL . '/public/';
        $base_path = MG_PLUGIN_PATH . 'public/';
        $gallery_types_url = MG_PLUGIN_URL . '/includes/gallery-types/';
        $gallery_types_path = MG_PLUGIN_PATH . 'includes/gallery-types/';

        // Always register universal init script
        wp_register_script(
            'mg-universal-init',
            $base_url . 'js/mg-universal-init.js',
            array('jquery'),
            '1.0',
            true
        );
        
        // Register only enabled module assets
        if (in_array('single_carousel', $enabled)) {
            $this->register_asset('mg-single-carousel-js', $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.js', 
                                 $gallery_types_path . 'mgwpp-single-gallery/mgwpp-single-gallery.js', array(), '1.0', true);
            $this->register_asset('mg-single-carousel-styles', $gallery_types_url . 'mgwpp-single-gallery/mgwpp-single-gallery.css',
                                 $gallery_types_path . 'mgwpp-single-gallery/mgwpp-single-gallery.css');
        }

        if (in_array('multi_carousel', $enabled)) {
            $this->register_asset('mg-multi-carousel-js', $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js',
                                 $gallery_types_path . 'mgwpp-multi-gallery/mgwpp-multi-gallery.js', array(), '1.0', true);
            $this->register_asset('mg-multi-carousel-styles', $gallery_types_url . 'mgwpp-multi-gallery/mgwpp-multi-gallery.css',
                                 $gallery_types_path . 'mgwpp-multi-gallery/mgwpp-multi-gallery.css');
        }

        if (in_array('grid', $enabled)) {
            $this->register_asset('mg-grid-styles', $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.css',
                                 $gallery_types_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.css');
            $this->register_asset('mg-grid-gallery-js', $gallery_types_url . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js',
                                 $gallery_types_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js', array(), 
                                 file_exists($gallery_types_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-grid-gallery/mgwpp-grid-gallery.js') : '1.0', true);
        }

        if (in_array('mega_slider', $enabled)) {
            $this->register_asset('mg-mega-carousel-styles', $gallery_types_url . 'mgwpp-mega-slider/mgwpp-mega-slider.css',
                                 $gallery_types_path . 'mgwpp-mega-slider/mgwpp-mega-slider.css');
            $this->register_asset('mg-mega-carousel-js', $gallery_types_url . 'mgwpp-mega-slider/mgwpp-mega-slider.js',
                                 $gallery_types_path . 'mgwpp-mega-slider/mgwpp-mega-slider.js', array(), 
                                 file_exists($gallery_types_path . 'mgwpp-mega-slider/mgwpp-mega-slider.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-mega-slider/mgwpp-mega-slider.js') : '1.0', true);
        }

        if (in_array('pro_carousel', $enabled)) {
            $this->register_asset('mgwpp-pro-carousel-styles', $gallery_types_url . 'mgwpp-pro-carousel/mgwpp-pro-carousel.css',
                                 $gallery_types_path . 'mgwpp-pro-carousel/mgwpp-pro-carousel.css');
            $this->register_asset('mgwpp-pro-carousel-js', $gallery_types_url . 'mgwpp-pro-carousel/mgwpp-pro-carousel.js',
                                 $gallery_types_path . 'mgwpp-pro-carousel/mgwpp-pro-carousel.js', array(), '1.0', true);
        }

        if (in_array('neon_carousel', $enabled)) {
            $this->register_asset('mgwpp-neon-carousel-styles', $gallery_types_url . 'mgwpp-neon-carousel/mgwpp-neon-carousel.css',
                                 $gallery_types_path . 'mgwpp-neon-carousel/mgwpp-neon-carousel.css');
            $this->register_asset('mgwpp-neon-carousel-js', $gallery_types_url . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js',
                                 $gallery_types_path . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js', array('jquery'), 
                                 file_exists($gallery_types_path . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-neon-carousel/mgwpp-neon-carousel.js') : '1.0', true);
        }

        if (in_array('threed_carousel', $enabled)) {
            $this->register_asset('mgwpp-threed-carousel-styles', $gallery_types_url . 'mgwpp-threed-carousel/mgwpp-threed-carousel.css',
                                 $gallery_types_path . 'mgwpp-threed-carousel/mgwpp-threed-carousel.css');
            $this->register_asset('mgwpp-threed-carousel-js', $gallery_types_url . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js',
                                 $gallery_types_path . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js', array('jquery'), 
                                 file_exists($gallery_types_path . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-threed-carousel/mgwpp-threed-carousel.js') : '1.0', true);
        }

        if (in_array('testimonials_carousel', $enabled)) {
            $this->register_asset('mgwpp-testimonial-carousel-styles', $base_url . 'css/mgwpp-testimonial-carousel.css',
                                 $base_path . 'css/mgwpp-testimonial-carousel.css');
            $this->register_asset('mgwpp-testimonial-carousel-js', $base_url . 'js/mgwpp-testimonial-carousel.js',
                                 $base_path . 'js/mgwpp-testimonial-carousel.js', array('jquery'), 
                                 file_exists($base_path . 'js/mgwpp-testimonial-carousel.js') ? 
                                 filemtime($base_path . 'js/mgwpp-testimonial-carousel.js') : '1.0', true);
        }

        if (in_array('lightbox', $enabled)) {
            $this->register_asset('mgwpp-lightbox-js', $base_url . 'js/mg-lightbox.js',
                                 $base_path . 'js/mg-lightbox.js', array('jquery'), 
                                 file_exists($base_path . 'js/mg-lightbox.js') ? 
                                 filemtime($base_path . 'js/mg-lightbox.js') : '1.0', true);
        }

        if (in_array('fullpage_slider', $enabled)) {
            $this->register_asset('mg-fullpage-slider-styles', $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css',
                                 $gallery_types_path . 'mgwpp-full-page-slider/mgwpp-full-page-slider.css');
            $this->register_asset('mg-fullpage-slider-js', $gallery_types_url . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js',
                                 $gallery_types_path . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js', array('jquery'), 
                                 file_exists($gallery_types_path . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-full-page-slider/mgwpp-full-page-slider.js') : '1.0', true);
        }

        if (in_array('spotlight_slider', $enabled)) {
            $this->register_asset('mg-spotlight-slider-styles', $gallery_types_url . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.css',
                                 $gallery_types_path . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.css');
            $this->register_asset('mg-spotlight-slider-js', $gallery_types_url . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js',
                                 $gallery_types_path . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js', array(), 
                                 file_exists($gallery_types_path . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js') ? 
                                 filemtime($gallery_types_path . 'mgwpp-spotlight-carousel/mgwpp-spotlight-carousel.js') : '1.0', true);
        }

        if (in_array('albums', $enabled)) {
            $this->register_asset('mg-album-styles', $base_url . 'css/mg-album-styles.css',
                                 $base_path . 'css/mg-album-styles.css', array(), 
                                 file_exists($base_path . 'css/mg-album-styles.css') ? 
                                 filemtime($base_path . 'css/mg-album-styles.css') : '1.0');
            $this->register_asset('mg-albums-script', $base_url . 'js/mg-albums.js',
                                 $base_path . 'js/mg-albums.js', array(), 
                                 file_exists($base_path . 'js/mg-albums.js') ? 
                                 filemtime($base_path . 'js/mg-albums.js') : '1.0', true);
        }
    }
    
    /**
     * Register an asset and track its file size
     */
    private function register_asset($handle, $url, $path, $deps = array(), $ver = false, $in_footer = false) {
        // For script
        if ($in_footer !== null) {
            wp_register_script($handle, $url, $deps, $ver, $in_footer);
            
            // Track asset info for debugging
            $this->asset_registry[$handle] = [
                'type' => 'script',
                'path' => $path,
                'size' => file_exists($path) ? filesize($path) : 0
            ];
        } 
        // For style
        else {
            wp_register_style($handle, $url, $deps, $ver);
            
            // Track asset info for debugging  
            $this->asset_registry[$handle] = [
                'type' => 'style',
                'path' => $path,
                'size' => file_exists($path) ? filesize($path) : 0
            ];
        }
    }

    public function enqueue_assets() {
        $enabled = $this->get_enabled_modules();
        
        // Always enqueue universal script
        wp_enqueue_script('mg-universal-init');
        
        // Albums module assets are always loaded if enabled
        if (in_array('albums', $enabled)) {
            wp_enqueue_style('mg-album-styles');
            wp_enqueue_script('mg-albums-script');
        }

        // Only load gallery-specific assets when needed
        if (is_singular('mgwpp_gallery') || self::$load_assets) {
            $gallery_type = self::$shortcode_gallery_type ?: get_post_meta(get_the_ID(), 'gallery_type', true) ?: 'single_carousel';

            // Only enqueue if the module is enabled
            if (in_array($gallery_type, $enabled)) {
                switch ($gallery_type) {
                    case 'single_carousel':
                        wp_enqueue_script('mg-single-carousel-js');
                        wp_enqueue_style('mg-single-carousel-styles');
                        break;
                    case 'multi_carousel':
                        wp_enqueue_script('mg-multi-carousel-js');
                        wp_enqueue_style('mg-multi-carousel-styles');
                        break;
                    case 'grid':
                        wp_enqueue_style('mg-grid-styles');
                        wp_enqueue_script('mg-grid-gallery-js');
                        break;
                    case 'mega_slider':
                        wp_enqueue_script('mg-mega-carousel-js');
                        wp_enqueue_style('mg-mega-carousel-styles');
                        break;
                    case 'pro_carousel':
                        wp_enqueue_style('mgwpp-pro-carousel-styles');
                        wp_enqueue_script('mgwpp-pro-carousel-js');
                        break;
                    case 'neon_carousel':
                        wp_enqueue_script('mgwpp-neon-carousel-js');
                        wp_enqueue_style('mgwpp-neon-carousel-styles');
                        break;
                    case 'threed_carousel':
                        wp_enqueue_script('mgwpp-threed-carousel-js');
                        wp_enqueue_style('mgwpp-threed-carousel-styles');
                        break;
                    case 'testimonials_carousel':
                        wp_enqueue_script('mgwpp-testimonial-carousel-js');
                        wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                        break;
                    // Add other gallery types with appropriate break
                }
            } else {
                // Log that gallery type is disabled but was requested
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("MG Gallery: Attempted to use disabled gallery type: {$gallery_type}");
                }
            }
        }
        
        // Add debug information when in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG && is_admin()) {
            add_action('admin_footer', array($this, 'print_asset_debug_info'));
        }
    }
    
    /**
     * Print debug information about loaded assets
     */
    public function print_asset_debug_info() {
        global $wp_scripts, $wp_styles;
        $mg_scripts = [];
        $mg_styles = [];
        $total_size = 0;
        
        // Track our scripts
        foreach ($wp_scripts->registered as $handle => $script) {
            if (strpos($handle, 'mg') === 0 || strpos($handle, 'mgwpp') === 0) {
                $size = isset($this->asset_registry[$handle]) ? $this->asset_registry[$handle]['size'] : 0;
                $mg_scripts[$handle] = [
                    'url' => $script->src,
                    'size' => $size
                ];
                $total_size += $size;
            }
        }
        
        // Track our styles
        foreach ($wp_styles->registered as $handle => $style) {
            if (strpos($handle, 'mg') === 0 || strpos($handle, 'mgwpp') === 0) {
                $size = isset($this->asset_registry[$handle]) ? $this->asset_registry[$handle]['size'] : 0;
                $mg_styles[$handle] = [
                    'url' => $style->src,
                    'size' => $size
                ];
                $total_size += $size;
            }
        }
        
        if (count($mg_scripts) > 0 || count($mg_styles) > 0) {
            echo '<div class="mgwpp-debug-info" style="background:#f1f1f1;padding:20px;margin:20px 0;border:1px solid #ddd;">';
            echo '<h3>Mini Gallery Debug Info</h3>';
            echo '<p>Total asset size: ' . $this->format_file_size($total_size) . '</p>';
            
            if (count($mg_scripts) > 0) {
                echo '<h4>Scripts:</h4><ul>';
                foreach ($mg_scripts as $handle => $data) {
                    echo '<li>' . esc_html($handle) . ' - ' . $this->format_file_size($data['size']) . '</li>';
                }
                echo '</ul>';
            }
            
            if (count($mg_styles) > 0) {
                echo '<h4>Styles:</h4><ul>';
                foreach ($mg_styles as $handle => $data) {
                    echo '<li>' . esc_html($handle) . ' - ' . $this->format_file_size($data['size']) . '</li>';
                }
                echo '</ul>';
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Format file size for display
     */
    private function format_file_size($bytes) {
        if ($bytes < 1024) {
            return $bytes . ' bytes';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    public function enqueue_admin_assets($hook_suffix) {
        if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
            $screen = get_current_screen();
            if ($screen && ('post' === $screen->base || 'page' === $screen->base)) {
                wp_enqueue_style('mgwpp-admin-styles');
            }
        }
    }

    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
}