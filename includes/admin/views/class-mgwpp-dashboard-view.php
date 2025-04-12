<?php
// File: includes/admin/views/class-mgwpp-dashboard-view.php
if (!defined('ABSPATH')) exit;

class MGWPP_Dashboard_View {

    public static function render_dashboard($stats, $storage) {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Dashboard Overview', 'mini-gallery') ?></h1>
            
            <div class="mgwpp-dashboard-grid">
                <?php self::render_stats_cards($stats); ?>
                <?php self::render_storage_section($storage); ?>
                <?php self::render_gallery_modules(); ?>
            </div>
        </div>
        <?php
    }

    private static function render_stats_cards($stats) {
        ?>
        <div class="mgwpp-stats-grid">
            <div class="mgwpp-stat-card">
                <h3><?php esc_html_e('Galleries', 'mini-gallery') ?></h3>
                <div class="stat-value"><?php echo esc_html($stats['galleries']) ?></div>
            </div>
            <div class="mgwpp-stat-card">
                <h3><?php esc_html_e('Albums', 'mini-gallery') ?></h3>
                <div class="stat-value"><?php echo esc_html($stats['albums']) ?></div>
            </div>
            <div class="mgwpp-stat-card">
                <h3><?php esc_html_e('Testimonials', 'mini-gallery') ?></h3>
                <div class="stat-value"><?php echo esc_html($stats['testimonials']) ?></div>
            </div>
        </div>
        <?php
    }

    private static function render_storage_section($storage) {
        ?>
        <div class="mgwpp-storage-section">
            <h2><?php esc_html_e('Storage Overview', 'mini-gallery') ?></h2>
            <div class="storage-progress">
                <div class="progress-bar" style="width: <?php echo esc_attr($storage['percent']) ?>%"></div>
                <span><?php echo esc_html($storage['percent']) ?>%</span>
            </div>
            <div class="storage-meta">
                <span><?php echo esc_html($storage['used']) ?> / <?php echo esc_html($storage['total']) ?></span>
            </div>
        </div>
        <?php
    }

    private static function render_gallery_modules() {
        $modules = glob(plugin_dir_path(__FILE__) . '../gallery-types/*', GLOB_ONLYDIR);
        ?>
        <div class="mgwpp-modules-section">
            <h2><?php esc_html_e('Installed Gallery Types', 'mini-gallery') ?></h2>
            <div class="modules-grid">
                <?php foreach ($modules as $module) : ?>
                    <div class="module-card">
                        <h4><?php echo esc_html(basename($module)) ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}