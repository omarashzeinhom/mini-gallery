<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Inner_Header
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('admin_body_class', [__CLASS__, 'add_admin_body_class']);
    }

    // Add dark mode class to admin body
    public static function add_admin_body_class($classes)
    {
        $current_theme = self::get_user_theme_preference();
        return $classes . ($current_theme === 'dark' ? ' mgwpp-dark-mode' : '');
    }

    public static function render()
    {
        $current_theme = self::get_user_theme_preference();
        $theme_class = $current_theme === 'dark' ? 'mgwpp-dark-mode' : '';
?>
        <div class="mgwpp-dashboard-header <?php echo esc_attr($theme_class); ?>">
            <div class="mgwpp-branding-group">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_dashboard')); ?>" class="mgwpp-link-no-decoration">
                    <img src="<?php echo esc_url(MG_PLUGIN_URL . '/includes/admin/images/logo/mgwpp-logo.png'); ?>"
                        class="mgwpp-logo"
                        width="50"
                        height="50"
                        alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
                </a>
                <div class="mgwpp-titles">
                    <h1 class="mgwpp-title">
                        <?php esc_html_e('Mini Gallery', 'mini-gallery') ?>
                        <span class="mgwpp-version"><?php echo self::get_plugin_version(); ?></span>
                    </h1>
                    <p class="mgwpp-subtitle">
                        <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
                    </p>
                </div>
            </div>

            <div class="mgwpp-actions-group">
                <?php self::render_theme_toggle($current_theme); ?>
                <a class="mgwpp-admin-button mgwpp-link-no-decoration " href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>">
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
        // Add cache-busting version parameter
        $version = MGWPP_ASSET_VERSION;

        $sun_icon = MG_PLUGIN_URL . '/includes/admin/images/icons/sun-icon.png?v=' . $version;
        $moon_icon = MG_PLUGIN_URL . '/includes/admin/images/icons/moon-icon.png?v=' . $version;
    ?>
        <div class="mgwpp-theme-toggle-wrapper">
            <button id="mgwpp-theme-toggle"
                data-current-theme="<?php echo esc_attr($current_theme); ?>"
                data-sun="<?php echo esc_url($sun_icon); ?>"
                data-moon="<?php echo esc_url($moon_icon); ?>"
                data-sun-fallback="☀️"
                data-moon-fallback="🌙">
                <img src="<?php echo $current_theme === 'dark' ? esc_url($sun_icon) : esc_url($moon_icon); ?>"
                    alt="<?php esc_attr_e('Theme Toggle', 'mini-gallery') ?>"
                    width="35" height="35">
            </button>
            </button>
        </div>
<?php
    }

    public static function handle_theme_toggle()
    {
        try {
            // Verify nonce first
            if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mgwpp-theme-nonce')) {
                throw new Exception(__('Security verification failed', 'mini-gallery'), 403);
            }

            // Validate theme parameter
            if (!isset($_POST['theme']) || !in_array($_POST['theme'], ['light', 'dark'])) {
                throw new Exception(__('Invalid theme parameter', 'mini-gallery'), 400);
            }

            $user_id = get_current_user_id();
            $new_theme = sanitize_key($_POST['theme']);

            // Update user meta
            if (!update_user_meta($user_id, 'mgwpp_admin_theme', $new_theme)) {
                throw new Exception(__('Failed to save theme preference', 'mini-gallery'), 500);
            }

            wp_send_json_success([
                'theme' => $new_theme,
                'body_class' => 'mgwpp-dark-mode'
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], $e->getCode());
        }
    }
}

// Initialize AJAX handler
add_action('wp_ajax_mgwpp_toggle_theme', [MGWPP_Inner_Header::class, 'handle_theme_toggle']);
