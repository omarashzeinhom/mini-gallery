<?php
if (!defined('ABSPATH')) exit;

class MGWPP_Dashboard_View
{
    private $asset_manager;

    public function __construct($asset_manager)
    {
        $this->asset_manager = $asset_manager;
        add_action('admin_menu', [$this, 'init']);
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets($hook)
    {
        if ($hook === 'toplevel_page_mgwpp_dashboard') {
            wp_enqueue_style(
                'mgwpp-admin-styles',
                MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
                [],
                filemtime(MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css')
            );

            wp_enqueue_script(
                'mgwpp-admin-scripts',
                MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
                ['jquery'],
                filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
                true
            );
        }
    }

    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $stats = [
            'galleries' => MGWPP_Data_Handler::get_post_count('mgwpp_soora'),
            'albums' => MGWPP_Data_Handler::get_post_count('mgwpp_album'),
            'testimonials' => MGWPP_Data_Handler::get_post_count('mgwpp_testimonial')
        ];

?>
        <div class="mgwpp-dashboard-container">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php
                    self::render_header();
                    self::render_stats_grid($stats);
                    self::render_storage_section(MGWPP_Data_Handler::get_storage_data());
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_header()
    {
    ?>
        <header class="mgwpp-dashboard-header">
            <div class="mgwpp-branding">
                <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-logo.png'); ?>"
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
                    <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/moon-icon.png'); ?>"
                        alt="<?php esc_attr_e('Theme Toggle', 'mini-gallery') ?>"
                        height="35"
                        width="35"
                        data-sun="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/sun-icon.png'); ?>"
                        data-moon="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/moon-icon.png'); ?>">
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>"
                    class="mgwpp-new-gallery-button">
                    <?php esc_html_e('New Gallery', 'mini-gallery') ?>
                </a>
            </div>
        </header>
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


    private static function render_stats_grid($stats)
    {
    ?>
        <div class="mgwpp-stats-grid">
            <?php
            self::render_stat_card(__('Galleries', 'mini-gallery'), $stats['galleries'], 'gallery');
            self::render_stat_card(__('Albums', 'mini-gallery'), $stats['albums'], 'album');
            self::render_stat_card(__('Testimonials', 'mini-gallery'), $stats['testimonials'], 'testimonial');
            ?>
        </div>
    <?php
    }

    private static function render_stat_card($title, $count, $icon)
    {
        $icon_url = MG_PLUGIN_URL . "/admin/images/icons/{$icon}.png";
    ?>
        <div class="mgwpp-stat-card">
            <div class="mgwpp-stat-content">
                <div class="mgwpp-stat-icon">
                    <img src="<?php echo esc_url($icon_url); ?>"
                        alt="<?php echo esc_attr($title) ?>"
                        loading="lazy"
                        width="24"
                        height="24">
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
}
