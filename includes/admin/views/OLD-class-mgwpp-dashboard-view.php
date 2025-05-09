<?php
// File: includes/admin/views/class-mgwpp-dashboard-view.php
if (!defined('ABSPATH')) exit;


class MGWPP_Dashboard_View
{
    private $asset_manager;

    public function __construct($asset_manager)
    {
        $this->asset_manager = $asset_manager;

        // Hook into admin_menu instead (standard WordPress hook)
        add_action('admin_menu', [$this, 'init']);
    }

    public function init()
    {
        // Register the assets when admin menu is being built
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    private static function is_dark_mode_enabled()
    {
        $user_id = get_current_user_id();
        $user_dark_mode = get_user_meta($user_id, 'mgwpp_dark_mode', true);

        // Check user meta first
        if ($user_dark_mode !== '') {
            return (bool) $user_dark_mode;
        }

        // Fallback to cookie
        return isset($_COOKIE['mgwpp_dark_mode']) && $_COOKIE['mgwpp_dark_mode'] === 'true';
    }


    public function enqueue_assets($hook)
    {
        if ($hook === 'toplevel_page_mgwpp_dashboard') {
            // Enqueue main styles
            wp_enqueue_style(
                'mgwpp-admin-styles',
                MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
                [],
                filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
            );

            // Enqueue theme toggle script
            wp_enqueue_script(
                'mgwpp-admin-scripts',
                MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
                ['jquery'],
                filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
                true
            );

            // Localize script data
            wp_localize_script('mgwpp-admin-scripts', 'mgwppData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mgwpp_dark_mode_nonce'),
                'darkMode' => self::is_dark_mode_enabled()
            ]);
        }
    }
    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $dark_mode = self::is_dark_mode_enabled();
        $stats = [
            'galleries' => MGWPP_Data_Handler::get_post_count('mgwpp_soora'),
            'albums' => MGWPP_Data_Handler::get_post_count('mgwpp_album'),
            'testimonials' => MGWPP_Data_Handler::get_post_count('mgwpp_testimonial')
        ];

?>
        <div class="mgwpp-dashboard-container <?php echo $dark_mode ? 'mgwpp-dark' : ''; ?>">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php
                    self::render_header($dark_mode);
                    self::render_stats_grid($stats, $dark_mode);
                    self::render_storage_section(MGWPP_Data_Handler::get_storage_data());
                    self::render_file_type_table(MGWPP_Data_Handler::get_storage_data());
                    self::render_modules_section(MGWPP_Data_Handler::get_installed_gallery_modules(), $dark_mode);
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_header($dark_mode)
    {
        $logo_url = $dark_mode
            ? MG_PLUGIN_URL . '/admin/images/icons/logo-light.png'
            : MG_PLUGIN_URL . '/admin/images/icons/logo-dark.png';
    ?>
        <header class="mgwpp-dashboard-header">
            <div class="mgwpp-branding">
                <img src="<?php echo esc_url($logo_url); ?>"
                    class="mgwpp-logo"
                    width="50"
                    height="50"
                    alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
                <div class="mgwpp-titles">
                    <h1 class="mgwpp-title">
                        <?php esc_html_e('Gallery Dashboard', 'mini-gallery') ?>
                        <span class="mgwpp-version">v1.2</span>
                    </h1>
                    <p class="mgwpp-subtitle">
                        <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
                    </p>
                </div>
            </div>
            <div class="mgwpp-header-actions">
                <button id="mgwpp-theme-toggle" class="mgwpp-theme-toggle-button">
                    <?php if ($dark_mode): ?>
                        <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/sun-icon.png'); ?>"
                            alt="<?php esc_attr_e('Light Mode', 'mini-gallery') ?>" height="35" width="35">
                    <?php else: ?>
                        <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/moon-icon.png'); ?>"
                            alt="<?php esc_attr_e('Dark Mode', 'mini-gallery') ?>" height="35" width="35">
                    <?php endif; ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>"
                    class="mgwpp-new-gallery-button">
                    <?php esc_html_e('New Gallery', 'mini-gallery') ?>
                </a>
            </div>
        </header>
    <?php
    }

    private static function render_stats_grid($stats, $dark_mode)
    {
    ?>
        <div class="mgwpp-stats-grid">
            <?php
            self::render_stat_card(
                __('Galleries', 'mini-gallery'),
                $stats['galleries'],
                'gallery',
                $dark_mode
            );
            self::render_stat_card(
                __('Albums', 'mini-gallery'),
                $stats['albums'],
                'album',
                $dark_mode
            );
            self::render_stat_card(
                __('Testimonials', 'mini-gallery'),
                $stats['testimonials'],
                'testimonial',
                $dark_mode
            );
            ?>
        </div>
    <?php
    }

    private static function render_stat_card($title, $count, $icon, $dark_mode)
    {
        // Remove the extension and dark/light suffix if present
        $icon_base = preg_replace('/-(dark|light)\.(png|webp)$/i', '', $icon);

        $base_path = '/admin/images/icons/';
        $variant = $dark_mode ? 'light' : 'dark';
        $icon_file = "{$icon}-{$variant}.png";

        // Full icon path
        $icon_url = MG_PLUGIN_URL . $base_path . $icon_file;
        $icon_path = MG_PLUGIN_PATH . $base_path . $icon_file;

        // Fallback system
        if (!file_exists($icon_path)) {
            $icon_url = MG_PLUGIN_URL . $base_path . "default-{$variant}.png";
        }
    ?>
        <div class="mgwpp-stat-card">
            <div class="mgwpp-stat-content">
                <div class="mgwpp-stat-icon">
                    <img src="<?php echo esc_url($icon_url); ?>"
                        data-light-src="<?php echo esc_url(MG_PLUGIN_URL . $base_path . "{$icon}-light.png"); ?>"
                        data-dark-src="<?php echo esc_url(MG_PLUGIN_URL . $base_path . "{$icon}-dark.png"); ?>"
                        alt="<?php echo esc_attr($title) ?>"
                        loading="lazy"
                        width="24"
                        height="24"
                        class="mgwpp-icon">
                </div>
                <div class="mgwpp-stat-info">
                    <h3 class="mgwpp-stat-title"><?php echo esc_html($title); ?></h3>
                    <p class="mgwpp-stat-count"><?php echo number_format_i18n($count); ?></p>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_storage_section($storage_data)
    {
    ?>
        <details>
            <summary>Storage Overview</summary>
            <div class="mgwpp-storage-card">
                <div class="mgwpp-storage-header">
                    <h2 class="mgwpp-section-title"><?php esc_html_e('Storage Overview', 'mini-gallery'); ?></h2>
                    <span class="mgwpp-storage-percent">
                        <?php echo esc_html($storage_data['percent']); ?>%
                    </span>
                </div>
                <div class="mgwpp-progress-bar">
                    <div class="mgwpp-progress-fill"
                        style="width: <?php echo esc_attr($storage_data['percent']); ?>%"></div>
                </div>
                <div class="mgwpp-storage-meta">
                    <span><?php echo esc_html($storage_data['used']); ?></span>
                    <span><?php echo esc_html($storage_data['total']); ?></span>
                </div>
            </div>
        </details>
    <?php
    }

    private static function render_file_type_table($storage_data)
    {
    ?>
        <details>
            <summary>File Type Distribution</summary>

            <div class="mgwpp-file-table">
                <h3 class="mgwpp-section-title"><?php esc_html_e('File Type Distribution', 'mini-gallery'); ?></h3>
                <table class="wp-list-table widefat fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Type', 'mini-gallery'); ?></th>
                            <th><?php esc_html_e('Count', 'mini-gallery'); ?></th>
                            <th><?php esc_html_e('Size', 'mini-gallery'); ?></th>
                            <th><?php esc_html_e('Percentage', 'mini-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storage_data['file_types'] as $ext => $data): ?>
                            <tr>
                                <td>.<?php echo esc_html($ext); ?></td>
                                <td><?php echo number_format($data['count']); ?></td>
                                <td><?php echo esc_html($data['size_formatted']); ?></td>
                                <td>
                                    <div class="mgwpp-percent-bar">
                                        <div class="mgwpp-percent-fill"
                                            style="width: <?php echo esc_attr($data['percent']); ?>%">
                                            <?php echo esc_html($data['percent']); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </details>

    <?php
    }

    private static function render_modules_section($modules, $dark_mode)
    {
    ?>
        <div class="mgwpp-modules-section">
            <h2 class="mgwpp-section-title"><?php esc_html_e('Active Gallery Types', 'mini-gallery'); ?></h2>
            <div class="mgwpp-modules-grid">
                <?php foreach ($modules as $module):
                    $safe_module = sanitize_title($module);
                    $icon_name = $dark_mode ? "module-{$safe_module}-light.png" : "module-{$safe_module}-dark.png";
                    $icon_url = MG_PLUGIN_URL . '/admin/images/icons/' . $icon_name;

                    // Fallback to default icon if variant doesn't exist
                    if (!file_exists(MG_PLUGIN_PATH . '/admin/images/icons/' . $icon_name)) {
                        $icon_url = MG_PLUGIN_URL . '/admin/images/module-' . $safe_module . '.webp';
                    }
                ?>
                    <div class="mgwpp-module-card">
                        <div class="mgwpp-module-content">
                            <div class="mgwpp-module-icon">
                                <img src="<?php echo esc_url($icon_url); ?>"
                                    alt="<?php echo esc_attr($module); ?>"
                                    loading="lazy"
                                    width="32"
                                    height="32">
                            </div>
                            <h4 class="mgwpp-module-title"><?php echo esc_html($module); ?></h4>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            // Simple dark mode toggle script
            document.addEventListener('DOMContentLoaded', function() {
                const themeToggle = document.getElementById('mgwpp-theme-toggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', function() {
                        const container = document.querySelector('.mgwpp-dashboard-container');
                        const isDark = container.classList.contains('mgwpp-dark');

                        // Toggle the class
                        container.classList.toggle('mgwpp-dark');

                        // Update the icon
                        this.innerHTML = isDark ?
                            '<span class="dashicons dashicons-moon"></span>' :
                            '<span class="dashicons dashicons-sun"></span>';

                        // Set cookie to remember preference
                        document.cookie = 'mgwpp_dark_mode=' + (!isDark) + '; path=/; max-age=31536000';
                    });
                }
            });
        </script>
<?php
        // TODO ENQUEUE CORRECTLY
        // In MGWPP_Dashboard_View::render_dashboard()
        echo '<style>';
        include MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css';
        echo '</style>';
    }
}
