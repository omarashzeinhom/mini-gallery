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
            null,
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

    public function checkbox_field_callback($args)
    {
        $option = get_option('mgwpp_enabled_modules', array_keys($this->modules));
        $slug = $args['slug'];
        echo '<input type="checkbox" name="mgwpp_enabled_modules[]" value="' . esc_attr($slug) . '" ' . checked(in_array($slug, (array)$option), true, false) . ' />';
    }

    public function render_settings_page()
    {
        echo '<div class="wrap"><h1>Gallery Settings</h1><form action="options.php" method="post">';
        settings_fields('mgwpp_settings_group');
        do_settings_sections('mgwpp-settings');
        submit_button();
        echo '</form></div>';
    }
    
    public function get_enabled_modules(){
        // empty for now
        // fetch enabled modules
    }
  
}

if (is_admin()) {
    new MGWPP_Settings();
}
