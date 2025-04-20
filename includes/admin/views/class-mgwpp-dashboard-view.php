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

?>
        <div class="wrap mgwpp-dashboard">
            <?php
            self::render_header();
            self::render_stats_grid($stats);
            self::render_storage_section($storage_data);
            self::render_file_type_table($storage_data);
            self::render_modules_section($installed_modules);
            ?>
        </div>
    <?php
    }

    private static function render_header()
    {
    ?>
        <header class="mgwpp-header">
            <div class="mgwpp-branding">
                <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-logo.png'); ?>"
                    class="mgwpp-logo"
                    height="125"
                    width="125"
                    alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
                <h1 class="mgwpp-title">
                    <?php esc_html_e('Gallery Dashboard', 'mini-gallery') ?>
                    <span class="mgwpp-version">v2.0</span>
                </h1>
            </div>
            <div class="mgwpp-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>"
                    class="mgwpp-button mgwpp-primary">
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

    private static function render_stat_card($title, $count, $icon)
    {
        $icon_url = MG_PLUGIN_URL . '/admin/images/' . sanitize_file_name($icon);
    ?>
        <div class="mgwpp-stat-card">
            <div class="mgwpp-stat-icon">
                <img src="<?php echo esc_url($icon_url); ?>"
                    alt="<?php echo esc_attr($title) ?>"
                    loading="lazy"
                    width="40"
                    height="40">
            </div>
            <div class="mgwpp-stat-content">
                <h3><?php echo esc_html($title); ?></h3>
                <div class="mgwpp-stat-value"><?php echo wp_kses_post(number_format_i18n($count)); ?></div>
            </div>
        </div>
    <?php
    }

    private static function render_storage_section($storage_data)
    {
    ?>
        <div class="mgwpp-storage-card">
            <div class="mgwpp-storage-header">
                <h2><?php esc_html_e('Storage Overview', 'mini-gallery'); ?></h2>
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
    <?php
    }

    private static function render_file_type_table($storage_data)
    {
    ?>
        <div class="mgwpp-file-table">
            <h3><?php esc_html_e('File Type Distribution', 'mini-gallery'); ?></h3>
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
    <?php
    }

    private static function render_modules_section($modules)
    {
    ?>
        <div class="mgwpp-modules-grid">
            <h2><?php esc_html_e('Active Gallery Types', 'mini-gallery'); ?></h2>
            <div class="mgwpp-modules-list">
                <?php foreach ($modules as $module):
                    $safe_module = sanitize_title($module);
                    $icon_url = MG_PLUGIN_URL . '/admin/images/module-' . $safe_module . '.webp';
                ?>
                    <div class="mgwpp-module-card">
                        <div class="mgwpp-module-icon">
                            <img src="<?php echo esc_url($icon_url); ?>"
                                alt="<?php echo esc_attr($module); ?>"
                                loading="lazy"
                                width="60"
                                height="60">
                        </div>
                        <h4><?php echo esc_html($module); ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

<?php
        // TODO ENQUEUE CORRECTLY
        // In MGWPP_Dashboard_View::render_dashboard()
        echo '<style>';
        include MG_PLUGIN_PATH . '/admin/css/mg-admin-styles.css';
        echo '</style>';
    }
}
