<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Modules_View {
    private $module_loader;

    public function __construct($module_loader) {
        $this->module_loader = $module_loader;
        add_action('admin_post_save_mgwpp_modules', [$this, 'save_modules']);
        add_action('wp_ajax_toggle_module_status', [$this, 'ajax_toggle_module']);
    }

    public function render() {  // Removed static keyword
        $modules = $this->module_loader->get_modules();
        $enabled_modules = get_option('mgwpp_enabled_modules', []);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Gallery Modules', 'mini-gallery'); ?></h1>
            
            <div class="mgwpp-modules-grid">
                <?php foreach ($modules as $slug => $module) : 
                    $is_active = in_array($slug, $enabled_modules);
                    $status_class = $is_active ? 'active' : 'inactive';
                ?>
                    <div class="mgwpp-module-card <?php echo $status_class; ?>">
                        <div class="module-header">
                            <h3><?php echo esc_html($module['config']['name']); ?></h3>
                            <div class="module-actions">
                                <div class="toggle-switch">
                                    <input type="checkbox" 
                                           id="module-<?php echo esc_attr($slug); ?>" 
                                           class="module-toggle" 
                                           data-module="<?php echo esc_attr($slug); ?>"
                                           <?php checked($is_active); ?>>
                                    <label for="module-<?php echo esc_attr($slug); ?>"></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="module-meta">
                            <span class="version"><?php echo esc_html($module['config']['version']); ?></span>
                            <span class="author"><?php echo esc_html($module['config']['author']); ?></span>
                        </div>
                        
                        <div class="module-description">
                            <?php echo esc_html($module['config']['description'] ?? ''); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php $this->print_admin_scripts(); ?>
        </div>
        <?php
    }

    private function print_admin_scripts() {
        ?>
        <script>
        (function($) {
            $('.module-toggle').on('change', function() {
                const module = $(this).data('module');
                const isActive = $(this).is(':checked');
                const $card = $(this).closest('.mgwpp-module-card');

                $card.addClass('updating');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'toggle_module_status',
                        module: module,
                        status: isActive ? 1 : 0,
                        nonce: '<?php echo wp_create_nonce('module_toggle_nonce'); ?>'
                    },
                    success: function(response) {
                        $card.removeClass('updating');
                        if(response.success) {
                            $card.toggleClass('active inactive', isActive);
                        } else {
                            alert('<?php esc_html_e('Failed to update module status', 'mini-gallery'); ?>');
                            $(this).prop('checked', !isActive);
                        }
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    public function ajax_toggle_module() {
        check_ajax_referer('module_toggle_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'mini-gallery'), 403);
        }

        $module = sanitize_text_field($_POST['module'] ?? '');
        $status = (bool) ($_POST['status'] ?? false);

        $enabled_modules = get_option('mgwpp_enabled_modules', []);
        
        if ($status) {
            if (!in_array($module, $enabled_modules)) {
                $enabled_modules[] = $module;
            }
        } else {
            $enabled_modules = array_diff($enabled_modules, [$module]);
        }

        update_option('mgwpp_enabled_modules', $enabled_modules);
        wp_send_json_success();
    }

    public function save_modules() {
        // Legacy form handler (optional)
    }
}