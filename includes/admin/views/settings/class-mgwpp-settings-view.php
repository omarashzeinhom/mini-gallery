<?php
// File: includes/admin/views/class-mgwpp-settings-view.php
if (!defined('ABSPATH')) {
    exit;
}
class MGWPP_Settings_View
{
    private $module_loader;

    public function __construct($module_loader = null)
    {
        $this->module_loader = $module_loader;
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting('mgwpp_settings_group', 'mgwpp_enabled_modules', [
            'sanitize_callback' => [$this, 'sanitize_modules']
        ]);

        add_settings_section(
            'mgwpp_modules_section',
            __('Core Modules', 'mini-gallery'),
            [$this, 'render_section_header'],
            'mgwpp-settings'
        );

        if ($this->module_loader) {
            $modules = $this->module_loader->get_modules();
            foreach ($modules as $slug => $module) {
                if ($slug !== 'galleries') {
                    add_settings_field(
                        'mgwpp_enabled_' . $slug,
                        '',
                        [$this, 'module_field_callback'],
                        'mgwpp-settings', // FIXED: Correct settings page
                        'mgwpp_modules_section',
                        ['slug' => $slug, 'module' => $module]
                    );
                }
            }
        }
    }
    public function render_section_header()
    {
        echo '<p>' . __('Enable or disable core plugin features. Galleries module is always enabled.', 'mini-gallery') . '</p>';
        echo '<div class="mgwpp-stats-grid">';
    }

    public function module_field_callback($args)
    {
        $slug = $args['slug'];
        $module = $args['module'];
        $option = get_option('mgwpp_enabled_modules', ['albums', 'testimonials', 'visual_editor', 'embed_editor']);
        $is_checked = in_array($slug, (array)$option);

?>
        <div class="mgwpp-stat-card<?php echo $is_checked ? ' active' : ''; ?>" data-module="<?php echo esc_attr($slug); ?>">
            <div class="mgwpp-stat-card-icon">
                <img src="<?php echo esc_url($this->get_module_icon($slug)); ?>"
                    alt="<?php echo esc_attr($module['name']); ?>">
            </div>
            <div class="module-info">
                <h3><?php echo esc_html($module['name']); ?></h3>
                <p><?php echo esc_html($module['description']); ?></p>
            </div>
            <div class="module-actions">
                <label class="mgwpp-switch">
                    <input type="checkbox"
                        class="mgwpp-module-toggle"
                        name="mgwpp_enabled_modules[]"
                        value="<?php echo esc_attr($slug); ?>"
                        <?php checked($is_checked, true); ?>>
                    <span class="mgwpp-switch-slider round"></span>
                </label>
            </div>
        </div>
    <?php
    }

    private function get_module_icon($module_slug)
    {
        $icons = [
            'albums' => 'albums.png',
            'testimonials' => 'testimonial.png',
            'visual_editor' => 'editor.png',
            'embed_editor' => 'build.png'
        ];

        $icon_filename = $icons[$module_slug] ?? 'default-module.png';
        return MG_PLUGIN_URL . '/includes/admin/images/modules-icons/main/' . $icon_filename;
    }

    public function render()
    {
        $this->enqueue_assets();
    ?>
        <div class="wrap">
            <h1><?php esc_html_e('Plugin Settings', 'mini-gallery'); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('mgwpp_settings_group');
                // Output settings sections - this includes the grid container and fields
                do_settings_sections('mgwpp-settings');
                ?>
        </div> <!-- Close the mgwpp-stats-grid container -->
        <?php submit_button(__('Save Core Module Settings', 'mini-gallery')); ?>
        </form>
        </div>
<?php
    }

    public function sanitize_modules($input)
    {
        $valid_modules = ['albums', 'testimonials', 'visual_editor', 'embed_editor'];
        return is_array($input) ? array_intersect($input, $valid_modules) : [];
    }

    public function enqueue_assets()
    {
        wp_enqueue_style(
            'mgwpp-settings-style',
            MG_PLUGIN_URL . "/includes/admin/views/settings/mgwpp-settings-view.css",
            [],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/settings/mgwpp-settings-view.css")
        );

        wp_enqueue_script(
            'mgwpp-settings-script',
            MG_PLUGIN_URL . "/includes/admin/views/settings/mgwpp-settings-view.js",
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . "/includes/admin/views/settings/mgwpp-settings-view.js"),
            true
        );
    }
}
