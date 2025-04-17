<?php
if (!defined(ABSPATH)) {
    exit();
}


class MGWPP_Elementor_Assets
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }

        if (\Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode()) {
            //wp_enqueue_style('mg-mega-carousel-styles');
            // wp_enqueue_script('mg-mega-carousel-js');
        }
    }
}

// Initialize only if Elementor is active
if (did_action('elementor/loaded')) {
    new MGWPP_Elementor_Assets();
}

function mgwpp_enqueue_elementor_assets()
{
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }

    if (\Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode()) {
        wp_enqueue_style('mg-neon-carousel-styles');
        wp_enqueue_script('mg-neon-carousel-js');
    }
}
add_action('wp_enqueue_scripts', 'mgwpp_enqueue_elementor_assets');



function mgwpp_add_elementor_category($elements_manager)
{
    $elements_manager->add_category(
        'minigallery',
        [
            'title' => __('Mini Gallery', 'mini-gallery'),
            'icon' => 'fa fa-images',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'mgwpp_add_elementor_category');
