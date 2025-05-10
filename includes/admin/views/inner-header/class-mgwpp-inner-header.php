<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Inner_Header
{
    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('admin_body_class', [__CLASS__, 'add_admin_body_class']);
    }

    // Add dark mode class to admin body
    public static function add_admin_body_class($classes) {
        $current_theme = self::get_user_theme_preference();
        if ($current_theme === 'dark') {
            $classes .= ' mgwpp-dark-mode';
        }
        return $classes;
    }

    public static function enqueue_assets()
    {
        // Only load assets on plugin admin pages
        $screen = get_current_screen();
        if (strpos($screen->id, 'mgwpp_') === false) return;

        // Get asset paths
        $css_path = MG_PLUGIN_URL . '/includes/admin/views/inner-header/mgwpp-inner-header.css';
        $js_path = MG_PLUGIN_URL . '/includes/admin/views/inner-header/mgwpp-inner-header.js';

        // Enqueue CSS with version based on file modification time
        wp_enqueue_style(
            'mgwpp-inner-header',
            $css_path,
            [],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/inner-header/mgwpp-inner-header.css')
        );

        // Enqueue JS with version and dependencies
        wp_enqueue_script(
            'mgwpp-inner-header',
            $js_path,
            ['jquery'],
            filemtime(MG_PLUGIN_PATH . '/includes/admin/views/inner-header/mgwpp-inner-header.js'),
            true
        );

        // Localize script for AJAX
        wp_localize_script('mgwpp-inner-header', 'mgwppHeader', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgwpp-theme-nonce')
        ]);
    }

    public static function render()
    {
        $current_theme = self::get_user_theme_preference();
        $theme_class = $current_theme === 'dark' ? 'mgwpp-dark-mode' : '';
?>
<div class="mgwpp-dashboard-header <?php echo esc_attr($theme_class); ?>">
    <div class="mgwpp-branding-group">
        <img src="<?php echo esc_url(MG_PLUGIN_URL . '/includes/admin/images/logo/mgwpp-logo.png'); ?>"
            class="mgwpp-logo" width="50" height="50" alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
        <div class="mgwpp-titles">
            <h1 class="mgwpp-title">
                <?php esc_html_e('Mini Gallery Dashboard', 'mini-gallery') ?>
                <span class="mgwpp-version"><?php echo self::get_plugin_version(); ?></span>
            </h1>
            <p class="mgwpp-subtitle">
                <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
            </p>
        </div>
    </div>

    <div class="mgwpp-actions-group">
        <?php self::render_theme_toggle($current_theme); ?>
        <a class="mgwpp-admin-button" href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>">
            <?php esc_html_e('New Gallery', 'mini-gallery') ?>
            <img src="<?php echo esc_url(MG_PLUGIN_URL . '/includes/admin/images/icons/add-new.png'); ?>"
                alt="<?php esc_attr_e('New Gallery', 'mini-gallery') ?>" class="mgwpp-admin-button__icon">
        </a>
    </div>
</div>
<?php
    }

    public static function get_user_theme_preference()
    {
        $user_id = get_current_user_id();
        return get_user_meta($user_id, 'mgwpp_admin_theme', true) ?: 'light';
    }

    private static function get_plugin_version()
    {
        return defined('MGWPP_VERSION') ? MGWPP_ASSET_VERSION : '1.4.0';
    }

    private static function render_theme_toggle($current_theme)
    {
       // Add filemtime to icon URLs
$sun_icon = MG_PLUGIN_URL . '/includes/admin/images/icons/sun-icon.png?' . filemtime(MG_PLUGIN_PATH . '/includes/admin/images/icons/sun-icon.png');
$moon_icon = MG_PLUGIN_URL . '/includes/admin/images/icons/moon-icon.png?' . filemtime(MG_PLUGIN_PATH . '/includes/admin/images/icons/moon-icon.png');
    ?>
 <button id="mgwpp-theme-toggle" class="mgwpp-theme-toggle-button"
        data-current-theme="<?php echo esc_attr($current_theme); ?>">
        <img src="<?php echo esc_url($current_theme === 'dark' ? $sun_icon : $moon_icon); ?>"
            alt="<?php esc_attr_e('Theme Toggle', 'mini-gallery') ?>"
            width="35" height="35"
            data-sun="<?php echo esc_url($sun_icon); ?>"
            data-moon="<?php echo esc_url($moon_icon); ?>">
    </button>
<?php
    }

     public static function handle_theme_toggle() {
        check_ajax_referer('mgwpp-theme-nonce', 'security');
        
        $user_id = get_current_user_id();
        $current_theme = get_user_meta($user_id, 'mgwpp_admin_theme', true);
        $new_theme = $current_theme === 'dark' ? 'light' : 'dark';
        
        update_user_meta($user_id, 'mgwpp_admin_theme', $new_theme);
        
        wp_send_json_success([
            'theme' => $new_theme,
            'body_class' => $new_theme === 'dark' ? 'mgwpp-dark-mode' : ''
        ]);
    }
}

// Initialize AJAX handler
add_action('wp_ajax_mgwpp_toggle_theme', [MGWPP_Inner_Header::class, 'handle_theme_toggle']);