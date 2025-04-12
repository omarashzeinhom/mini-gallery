<?php
// File: includes/admin/views/class-mgwpp-dashboard-view.php
if (!defined('ABSPATH')) exit;

class MGWPP_Dashboard_View {

    public static function render_dashboard() {
        // Calculate stats
        $stats = [
            'galleries' => wp_count_posts('mgwpp_soora')->publish ?? 0,
            'albums' => wp_count_posts('mgwpp_album')->publish ?? 0,
            'testimonials' => wp_count_posts('mgwpp_testimonial')->publish ?? 0,
        ];

        // Calculate storage usage
        $upload_dir = wp_upload_dir();
        $used_bytes = 0;

        if (is_dir($upload_dir['basedir'])) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir['basedir'])) as $file) {
                if ($file->isFile()) {
                    $used_bytes += $file->getSize();
                }
            }
        }

        $used_mb = round($used_bytes / 1048576, 2);
        $total_mb = 1024;
        $percent = min(100, round(($used_mb / $total_mb) * 100, 2));

        $storage = [
            'percent' => $percent,
            'used'    => "{$used_mb}MB",
            'total'   => "{$total_mb}MB",
        ];

        // Render sections
        self::render_stats_cards($stats);
        self::render_storage_section($storage);
        self::render_gallery_modules();
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