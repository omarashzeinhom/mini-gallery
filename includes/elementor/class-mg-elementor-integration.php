<?php
if (!defined('ABSPATH')) exit;

class MG_Elementor_Integration
{
    private $show_pro_elements_notice = false;
    private $plugin_file;
    private $elementor_pro_conflict = false;

    public function __construct()
    {
        $this->plugin_file = plugin_basename(__FILE__);

        // Only admin_init is needed for both checks + deactivation
        add_action('admin_init', [$this, 'handle_admin_init']);

        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('wp_ajax_mg_dismiss_pro_elements_notice', [$this, 'dismiss_notice_ajax_handler']);
    }

    public function handle_admin_init()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

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
        if (!current_user_can('manage_options')) return;

        $user_id = get_current_user_id();
        $dismissed = get_user_meta($user_id, 'mg_dismiss_pro_elements_notice', true);

        if ($this->elementor_pro_conflict) {
            echo '<div class="notice notice-error"><p>' .
                esc_html__('⚠️ Mini Gallery has been deactivated because Elementor Pro is not compatible. Please deactivate Elementor Pro and use Pro Elements instead:', 'mini-gallery') .
                ' <a href="https://proelements.org" target="_blank" rel="noopener noreferrer">proelements.org</a></p></div>';
            return;
        }

        // ✅ Thank you/review notice
        if ($this->show_pro_elements_notice && !$dismissed) {
            echo '<div class="notice notice-success is-dismissible mg-pro-elements-notice">
                <p><strong style="font-size: 16px;">🎉 Thank you for installing Mini Gallery!</strong></p>
                <p style="font-size: 15px;">If you love the plugin, please consider leaving us a 
                <a href="https://wordpress.org/plugins/mini-gallery/#reviews" target="_blank" rel="noopener noreferrer" style="text-decoration: underline; font-weight: 500;">🌟🌟🌟🌟🌟 review</a> — it really helps!</p>
            </div>';
        }

        // ✅ Recommend Pro Elements if Elementor is missing
        if (
            !is_plugin_active('elementor/elementor.php') &&
            !is_plugin_active('elementor-pro/elementor-pro.php') &&
            !is_plugin_active('pro-elements/pro-elements.php')
        ) {
            echo '<div class="notice notice-warning">
                <p><strong>Heads up!</strong> It looks like Elementor is not active. If you plan to use Elementor with Mini Gallery, we recommend 
                <a href="https://proelements.org" target="_blank" rel="noopener noreferrer"><strong>Pro Elements</strong></a> — the free, open-source alternative to Elementor Pro that works perfectly with Mini Gallery. 🚀</p>
            </div>';
        }

        // CSS & dismiss JS
        if (($this->show_pro_elements_notice && !$dismissed) || !is_plugin_active('elementor/elementor.php')) {
            add_action('admin_footer', function () {
                ?>
                <style>
                    .mg-pro-elements-notice, .notice-warning {
                        padding: 20px 20px 20px 25px;
                        font-size: 15px;
                    }
                    .mg-pro-elements-notice p, .notice-warning p {
                        margin: 0 0 5px;
                        line-height: 1.6;
                    }
                    .mg-pro-elements-notice a, .notice-warning a {
                        color: #0073aa;
                    }
                    .mg-pro-elements-notice a:hover, .notice-warning a:hover {
                        color: #00a0d2;
                    }
                </style>
                <script>
                    (function($){
                        $(document).on('click', '.mg-pro-elements-notice .notice-dismiss', function(){
                            $.post(ajaxurl, {
                                action: 'mg_dismiss_pro_elements_notice',
                                user_id: <?php echo esc_js( get_current_user_id() ); ?>
                            });
                        });
                    })(jQuery);
                </script>
                <?php
            });
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

    public function register_widgets($widgets_manager)
    {
        if ($this->elementor_pro_conflict) return;

        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-testimonial-carousel.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-threed-carousel.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-mega-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-pro-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-neon-carousel-widget.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-single.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-grid.php';
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/class-mg-elementor-gallery-multi.php';

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
