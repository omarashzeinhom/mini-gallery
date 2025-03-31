<?php
if (!defined('ABSPATH')) exit;

class MG_Elementor_Integration
{
    public function __construct()
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($widgets_manager)
    {
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-mega-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-pro-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-neon-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-single.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-grid.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-multi.php';

        $widgets_manager->register(new MG_Elementor_Mega_Carousel());
        $widgets_manager->register(new MG_Elementor_Pro_Carousel());
        $widgets_manager->register(new MG_Elementor_Neon_Carousel());
        $widgets_manager->register(new MG_Elementor_Gallery_Single());
        $widgets_manager->register(new MG_Elementor_Gallery_Grid());
        $widgets_manager->register(new MG_Elementor_Gallery_Multi());
    }
}

new MG_Elementor_Integration();
