<?php
if (!defined('ABSPATH')) {
    exit;
}

class MG_Elementor_Integration
{
    private $show_pro_elements_notice = false;
    private $plugin_file;
    private $elementor_pro_conflict = false;

    public function __construct()
    {
        $this->plugin_file = plugin_basename(__FILE__);

        // Admin actions
        add_action('admin_init', [$this, 'handle_admin_init']);
        add_action('admin_notices', [$this, 'admin_notices']);
        
        // Register Elementor widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Ajax handling for dismissing notices
        add_action('wp_ajax_mg_dismiss_pro_elements_notice', [$this, 'dismiss_notice_ajax_handler']);

        // Enqueue assets for widgets both frontend and editor
        add_action('elementor/widget/render_content', [$this, 'enqueue_widget_assets'], 10, 2);
        //add_action('elementor/widget/render_content', [$this, 'enqueue_editor_widget_assets'], 10, 2);
        
        // Enqueue editor specific assets
        //add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_assets']);
    }

    // Enqueue widget-specific assets (both frontend and editor)
    public function enqueue_widget_assets($content, $widget)
    {
        // Check widget class to load specific assets
        $widget_class = get_class($widget);

        switch ($widget_class) {
            case 'MG_Elementor_Gallery_Single':
                wp_enqueue_script('mg-single-carousel-js');
                wp_enqueue_style('mg-single-carousel-styles');
                break;
            case 'MG_Elementor_Gallery_Grid':
                wp_enqueue_script('mg-grid-gallery');
                wp_enqueue_style('mg-grid-styles');
                break;
            case 'MG_Elementor_Gallery_Multi':
                wp_enqueue_script('mg-multi-carousel-js');
                wp_enqueue_style('mg-multi-carousel-styles');
                break;
            case 'MG_Elementor_Testimonial_Carousel':
                wp_enqueue_script('mgwpp-testimonial-carousel-js');
                wp_enqueue_style('mgwpp-testimonial-carousel-styles');
                break;
            case 'MG_Elementor_3D_Carousel':
                wp_enqueue_script('mgwpp-threed-carousel-js');
                wp_enqueue_style('mgwpp-threed-carousel-styles');
                break;
            case 'MG_Elementor_Mega_Carousel':
                wp_enqueue_script('mg-mega-carousel-js');
                wp_enqueue_style('mg-mega-carousel-styles');
                break;
            case 'MG_Elementor_Pro_Carousel':
                wp_enqueue_script('mgwpp-pro-carousel-js');
                wp_enqueue_style('mgwpp-pro-carousel-styles');
                break;
            case 'MG_Elementor_Neon_Carousel':
                wp_enqueue_script('mgwpp-neon-carousel-js');
                wp_enqueue_style('mgwpp-neon-carousel-styles');
                break;
            default:
                // No assets enqueued for unknown widgets
                break;
        }

        return $content;
    }

    public function handle_admin_init()
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (is_plugin_active('elementor-pro/elementor-pro.php')) {
            $this->elementor_pro_conflict = true;
            deactivate_plugins($this->plugin_file);
            return;
        }

        if (is_plugin_active('pro-elements/pro-elements.php') || is_plugin_active('elementor/elementor.php')) {
            $this->show_pro_elements_notice = true;
        }
    }

    public function admin_notices()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $user_id = get_current_user_id();
        $dismissed = get_user_meta($user_id, 'mg_dismiss_pro_elements_notice', true);

        if ($this->elementor_pro_conflict) {
            echo '<div class="notice notice-error"><p>' . esc_html__('⚠️ Mini Gallery has been deactivated because Elementor Pro is not compatible. Please deactivate Elementor Pro and use Pro Elements instead:', 'mini-gallery') . ' <a href="https://proelements.org" target="_blank" rel="noopener noreferrer">proelements.org</a></p></div>';
            return;
        }

        // Show Pro Elements notice if necessary
        if ($this->show_pro_elements_notice && !$dismissed) {
            echo '<div class="notice notice-success is-dismissible mg-pro-elements-notice">
                    <p><strong style="font-size: 16px;">🎉 Thank you for installing Mini Gallery!</strong></p>
                    <p style="font-size: 15px;">If you love the plugin, please consider leaving us a 
                    <a href="https://wordpress.org/plugins/mini-gallery/#reviews" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; font-weight: 500;">🌟🌟🌟🌟🌟 review</a> — it really helps!</p>
                </div>';
        }
    }

    public function dismiss_notice_ajax_handler()
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            update_user_meta($user_id, 'mg_dismiss_pro_elements_notice', 1);
        }
        wp_die();
    }

    // Register Elementor widgets
    public function register_widgets($widgets_manager)
    {
        if ($this->elementor_pro_conflict) {
            return;
        }

        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-testimonial-carousel.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-threed-carousel.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-mega-carousel-widget.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-pro-carousel-widget.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-neon-carousel-widget.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-single.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-grid.php';
        include_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-multi.php';

        $widgets_manager->register(new MG_Elementor_Testimonial_Carousel());
        $widgets_manager->register(new MG_Elementor_3D_Carousel());
        $widgets_manager->register(new MG_Elementor_Mega_Carousel());
        $widgets_manager->register(new MG_Elementor_Pro_Carousel());
        $widgets_manager->register(new MG_Elementor_Neon_Carousel());
        $widgets_manager->register(new MG_Elementor_Gallery_Single());
        $widgets_manager->register(new MG_Elementor_Gallery_Grid());
        $widgets_manager->register(new MG_Elementor_Gallery_Multi());
    }
}

new MG_Elementor_Integration();
