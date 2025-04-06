<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class MGWPP_Dashboard {

    /**
     * Get storage data for the plugin, including file usage, types, and suspicious files.
     */
    private static function get_storage_data() {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $plugin_image_ids = [];

        // Get attachment IDs used in plugin post types
        $post_types = ['mgwpp_soora', 'mgwpp_album', 'testimonial'];
        $plugin_query = new WP_Query([
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        foreach ($plugin_query->posts as $post_id) {
            $attachments = get_attached_media('image', $post_id);
            foreach ($attachments as $media) {
                $plugin_image_ids[] = $media->ID;
            }
        }

        $plugin_images_total = 0;
        $all_file_types = [];
        $file_count = 0;
        $suspicious_files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upload_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $file_size = $file->getSize();
                $filepath = $file->getPathname();

                // Tally total uploads folder usage
                $plugin_images_total += $file_size;
                $file_count++;
                if (!isset($all_file_types[$ext])) {
                    $all_file_types[$ext] = ['count' => 0, 'size' => 0];
                }
                $all_file_types[$ext]['count'] += 1;
                $all_file_types[$ext]['size'] += $file_size;

                // Suspicious file scan (basic)
                if (in_array($ext, ['php', 'js'])) {
                    $content = @file_get_contents($filepath);
                    if ($content && preg_match('/(base64_decode|eval|gzinflate|shell_exec|system)/i', $content)) {
                        $suspicious_files[] = [
                            'path' => str_replace($upload_path, '', $filepath),
                            'extension' => $ext,
                        ];
                    }
                }
            }
        }

        $storage_total = 1024 * 1024 * 1024; // 1GB
        $used_percent = round(($plugin_images_total / $storage_total) * 100, 2);

        foreach ($all_file_types as $ext => &$data) {
            $data['size_formatted'] = size_format($data['size'], 2);
            $data['percent'] = round(($data['size'] / $plugin_images_total) * 100, 2);
        }

        return [
            'used' => size_format($plugin_images_total, 2),
            'total' => size_format($storage_total, 2),
            'percent' => $used_percent,
            'file_types' => $all_file_types,
            'files' => $file_count,
            'suspicious' => $suspicious_files,
        ];
    }

    /**
     * Get a list of installed gallery modules.
     */
    private static function get_installed_gallery_modules() {
        $modules = [];
        $gallery_path = plugin_dir_path(__FILE__) . 'includes/gallery-types/';
    
        if (is_dir($gallery_path)) {
            $files = glob($gallery_path . 'class-mgwpp-*.php');
    
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $type = str_replace(['class-mgwpp-', '-gallery', '-carousel', '-slider'], '', $filename);
                $modules[] = ucfirst(str_replace('_', ' ', $type));
            }
        }
    
        return $modules;
    }

    /**
     * Render the dashboard statistics overview.
     */
    private static function render_dashboard_stats() {
        // Get counts
        $total_galleries = self::get_post_count('mgwpp_soora');
        $total_albums = self::get_post_count('mgwpp_album');
        $total_testimonials = self::get_post_count('testimonial');
        $total_items = $total_galleries + $total_albums + $total_testimonials;

        // Get storage data
        $storage_data = self::get_storage_data();
        $storage_used = $storage_data['used'];
        $storage_total = $storage_data['total'];
        $storage_percent = $storage_data['percent'];
        $file_types = $storage_data['file_types'];
        $files = $storage_data['files'];

        // Get installed gallery modules
        $installed_modules = self::get_installed_gallery_modules();

        ?>
        <div class="dashboard-stats theme-light" id="dashboard-stats">
            <!-- Header Section -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold"><?php echo esc_html__('Dashboard Statistics', 'mini-gallery'); ?></h2>
                <?php self::render_theme_toggle(); ?>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <?php
                self::render_stat_card(
                    __('Galleries', 'mini-gallery'),
                    $total_galleries,
                    'blue',
                    'mgwpp-galleries-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Albums', 'mini-gallery'),
                    $total_albums,
                    'purple',
                    'mgwpp-albums-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Testimonials', 'mini-gallery'),
                    $total_testimonials,
                    'green',
                    'mgwpp-testimonials-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Total Items', 'mini-gallery'),
                    $total_items,
                    'amber',
                    'mgwpp-total-items-icon-dashboard.webp'
                );
                ?>
            </div>

            <!-- Storage Visualization Section -->
            <?php self::render_storage_section($storage_used, $storage_total, $storage_percent, $file_types, $files); ?>
            <h4>Installed Gallery Modules:</h4>
            <ul>
                <?php foreach ($installed_modules as $module): ?>
                    <li>✅ <?php echo esc_html($module); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Get the post count for a specific post type.
     */
    private static function get_post_count($post_type) {
        $counts = wp_count_posts($post_type);
        return isset($counts->publish) ? $counts->publish : 0;
    }

    /**
     * Render the theme toggle button.
     */
    private static function render_theme_toggle() {
        ?>
        <button onclick="toggleDashboardTheme()"
            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            aria-label="<?php esc_attr_e('Toggle theme', 'mini-gallery'); ?>">
            <img id="theme-icon-moon"
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-moon-icon.webp'); ?>"
                alt="<?php esc_attr_e('Theme toggle icon', 'mini-gallery'); ?>"
                class="h-6 w-6">
            <img id="theme-icon-sun"
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-sun-icon.webp'); ?>"
                alt="<?php esc_attr_e('Sun icon', 'mini-gallery'); ?>"
                class="h-6 w-6 hidden">
        </button>
        <?php
    }

    /**
     * Render a stat card for the dashboard.
     */
    private static function render_stat_card($title, $count, $color, $icon) {
        ?>
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php echo esc_html($title); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($count); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-<?php echo esc_attr(sanitize_html_class($color)); ?>-500/10 text-<?php echo esc_attr(sanitize_html_class($color)); ?>-500 dark:bg-<?php echo esc_attr(sanitize_html_class($color)); ?>-400/20 dark:text-<?php echo esc_attr(sanitize_html_class($color)); ?>-300">
                    <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/' . $icon); ?>"
                        alt="<?php echo esc_attr($title); ?>"
                        class="h-10 w-10">
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
        <?php
    }

    /**
     * Render the storage section with file usage details.
     */
    private static function render_storage_section($used, $total, $percent, $file_types, $file_count) {
        ?>
        <div class="mt-8 p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Storage Overview', 'mini-gallery'); ?></h3>

            <div class="mb-6">
                <strong><?php esc_html_e('Used:', 'mini-gallery'); ?></strong>
                <?php echo esc_html($used); ?> /
                <?php echo esc_html($total); ?> (<?php echo esc_html($percent); ?>%)
                <div class="h-4 w-full bg-gray-200 rounded mt-1">
                    <div class="h-4 bg-green-500 rounded" style="width: <?php echo esc_attr($percent); ?>%"></div>
                </div>
            </div>

            <h4 class="text-md font-semibold mt-6 mb-2"><?php esc_html_e('File Types Breakdown', 'mini-gallery'); ?></h4>
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="py-2 pr-4"><?php esc_html_e('Extension', 'mini-gallery'); ?></th>
                        <th class="py-2 pr-4"><?php esc_html_e('Count', 'mini-gallery'); ?></th>
                        <th class="py-2 pr-4"><?php esc_html_e('Size', 'mini-gallery'); ?></th>
                        <th class="py-2"><?php esc_html_e('Usage %', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($file_types as $ext => $data): ?>
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-1 pr-4"><?php echo esc_html($ext); ?></td>
                            <td class="py-1 pr-4"><?php echo esc_html($data['count']); ?></td>
                            <td class="py-1 pr-4"><?php echo esc_html($data['size_formatted']); ?></td>
                            <td class="py-1">
                                <div class="w-full bg-gray-200 rounded h-3 relative">
                                    <div class="absolute top-0 left-0 h-3 bg-blue-500 rounded" style="width: <?php echo esc_attr($data['percent']); ?>%"></div>
                                    <span class="absolute left-2 top-0 text-xs text-white leading-3"><?php echo esc_attr($data['percent']); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <?php echo esc_html($file_count); ?> <?php esc_html_e('files scanned.', 'mini-gallery'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the dashboard page.
     */
    public static function mgwpp_render_dashboard_page() {
        echo '<div class="wrap"><h1>' . esc_html__('Dashboard Overview', 'mini-gallery') . '</h1>';
        self::render_dashboard_stats();
        echo '</div>';
    }
}

