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

    public function enqueue_assets($hook)
    {
        // Only load on our dashboard page
        if ($hook === 'toplevel_page_mgwpp_dashboard') {
            wp_enqueue_style(
                'mg-admin-styles',
                MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
                [],
                filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
            );
        }
    }

    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html_e('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $stats = [
            'galleries' => MGWPP_Data_Handler::get_post_count('mgwpp_soora'),
            'albums' => MGWPP_Data_Handler::get_post_count('mgwpp_album'),
            'testimonials' => MGWPP_Data_Handler::get_post_count('mgwpp_testimonial')
        ];

        $storage_data = MGWPP_Data_Handler::get_storage_data();
        $installed_modules = MGWPP_Data_Handler::get_installed_gallery_modules();

        // Check if dark mode is enabled via user meta or cookie
        $dark_mode = isset($_COOKIE['mgwpp_dark_mode']) && $_COOKIE['mgwpp_dark_mode'] === 'true';
        $container_class = $dark_mode ? 'mgwpp-dashboard-container mgwpp-dark' : 'mgwpp-dashboard-container';
?>
        <div class="<?php echo esc_attr($container_class); ?>">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php
                    self::render_header($dark_mode);
                    self::render_stats_grid($stats);
                    self::render_storage_section($storage_data);
                    self::render_file_type_table($storage_data);
                    self::render_modules_section($installed_modules);
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_header($dark_mode)
    {
    ?>
        <header class="mgwpp-dashboard-header">
            <div class="mgwpp-branding">
                <div class="mgwpp-logo-container">
                    <div class="mgwpp-logo-icon">
                        <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-logo.png'); ?>"
                            height="32"
                            width="32"
                            alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
                    </div>
                    <div class="mgwpp-logo-accent"></div>
                </div>
                <div class="mgwpp-title-container">
                    <h1 class="mgwpp-dashboard-title">
                        <?php esc_html_e('Gallery Dashboard', 'mini-gallery') ?>
                        <span class="mgwpp-version-badge">v1.2</span>
                    </h1>
                    <p class="mgwpp-dashboard-subtitle">
                        <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
                    </p>
                </div>
            </div>
            <div class="mgwpp-header-actions">
                <button class="mgwpp-theme-toggle-button" id="mgwpp-theme-toggle">
                    <?php if ($dark_mode): ?>
                        <span class="dashicons dashicons-sun"></span>
                    <?php else: ?>
                        <span class="dashicons dashicons-moon"></span>
                    <?php endif; ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>"
                    class="mgwpp-new-gallery-button">
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e('New Gallery', 'mini-gallery') ?>
                </a>
            </div>
        </header>
    <?php
    }

    private static function render_stats_grid($stats)
    {
    ?>
        <div class="mgwpp-stats-grid">
            <?php
            self::render_stat_card(
                __('Galleries', 'mini-gallery'),
                $stats['galleries'],
                'mgwpp-galleries-icon.webp'
            );

            self::render_stat_card(
                __('Albums', 'mini-gallery'),
                $stats['albums'],
                'mgwpp-albums-icon.webp'
            );

            self::render_stat_card(
                __('Testimonials', 'mini-gallery'),
                $stats['testimonials'],
                'mgwpp-testimonials-icon.webp'
            );
            ?>
        </div>
    <?php
    }

    private static function render_stat_card($title, $count, $icon, $dark_mode)
    {
        // Remove the extension and dark/light suffix if present
        $icon_base = preg_replace('/-(dark|light)\.(png|webp)$/i', '', $icon);

        // Determine which variant to use (invert logic: dark mode uses light icons)
        $icon_variant = $dark_mode ? 'light' : 'dark';
        $icon_path = "{$icon_base}-{$icon_variant}.png";

        // Fallback to original if specific variant not found
        $icon_url = file_exists(MG_PLUGIN_PATH . '/admin/images/icons/' . $icon_path)
            ? MG_PLUGIN_URL . '/admin/images/icons/' . $icon_path
            : MG_PLUGIN_URL . '/admin/images/' . sanitize_file_name($icon);
    ?>
        <div class="mgwpp-stat-card">
            <div class="mgwpp-stat-content">
                <div class="mgwpp-stat-icon">
                    <img src="<?php echo esc_url($icon_url); ?>"
                        alt="<?php echo esc_attr($title) ?>"
                        loading="lazy"
                        width="24"
                        height="24"
                        class="mgwpp-icon">
                </div>
                <div class="mgwpp-stat-info">
                    <h3 class="mgwpp-stat-title"><?php echo esc_html($title); ?></h3>
                    <p class="mgwpp-stat-count"><?php echo wp_kses_post(number_format_i18n($count)); ?></p>
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

    private static function render_modules_section($modules)
    {
    ?>
        <div class="mgwpp-modules-section">
            <h2 class="mgwpp-section-title"><?php esc_html_e('Active Gallery Types', 'mini-gallery'); ?></h2>
            <div class="mgwpp-modules-grid">
                <?php foreach ($modules as $module):
                    $safe_module = sanitize_title($module);
                    $icon_url = MG_PLUGIN_URL . '/admin/images/module-' . $safe_module . '.webp';
                ?>
                    <div class="mgwpp-module-card">
                        <div class="mgwpp-module-content">
                            <div class="mgwpp-module-icon">
                                <img src="<?php echo esc_url($icon_url); ?>"
                                    alt="<?php echo esc_attr($module); ?>"
                                    loading="lazy"
                                    width="24"
                                    height="24"
                                    class="mgwpp-icon">
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
