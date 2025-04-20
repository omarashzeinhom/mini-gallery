<?php
if (!defined('ABSPATH')) {
    exit;
}

// File: includes/admin/views/class-security-view.php
class MGWPP_Security_View
{
    public static function render()
    {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $suspicious_files = self::scan_directory($upload_path);
        
        echo '<div id="mgwpp_security_content" class="mgwpp-tab-content">';
        self::render_security_header();
        self::render_scan_results($suspicious_files);
        self::render_storage_analysis();
        echo '</div>';
    }

    private static function render_security_header()
    {
        ?>
        <h2><?php echo esc_html__('Security Settings', 'mini-gallery'); ?></h2>
        <div class="mgwpp-security-settings">
            <p><?php echo esc_html__('This section includes security scan results and will include more options in future updates.', 'mini-gallery'); ?></p>
        </div>
        <?php
    }

    private static function render_scan_results($suspicious_files)
    {
        ?>
        <div class="mgwpp-scan-results mt-6">
            <h3 class="text-md font-semibold"><?php echo esc_html__('Suspicious File Scan', 'mini-gallery'); ?></h3>
            <?php self::render_suspicious_report($suspicious_files); ?>
        </div>
        <?php
    }

    private static function render_storage_analysis()
    {
        $storage_data = self::get_storage_data();
        ?>
        <div class="mgwpp-storage-analysis mt-6">
            <h3 class="text-md font-semibold"><?php echo esc_html__('Storage Analysis', 'mini-gallery'); ?></h3>
            <?php self::render_storage_section(
                $storage_data['used'],
                $storage_data['total'],
                $storage_data['percent'],
                $storage_data['file_types'],
                $storage_data['files']
            ); ?>
        </div>
        <?php
    }

    public static function scan_directory($path)
    {
        $suspicious_files = [];
        
        if (!is_dir($path)) {
            return $suspicious_files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                
                if (in_array($ext, ['php', 'js', 'exe', 'dll'])) {
                    $content = @file_get_contents($file->getPathname());
                    if ($content && preg_match('/(base64_decode|eval|gzinflate|shell_exec|system|passthru|exec|phpinfo)/i', $content)) {
                        $suspicious_files[] = [
                            'path' => str_replace($path, '', $file->getPathname()),
                            'extension' => $ext,
                            'size' => size_format($file->getSize(), 2)
                        ];
                    }
                }
            }
        }

        return $suspicious_files;
    }

    public static function render_suspicious_report($suspicious_files)
    {
        if (empty($suspicious_files)) {
            echo '<div class="notice notice-success"><p>' . esc_html__('No suspicious files found.', 'mini-gallery') . '</p></div>';
            return;
        }
        
        echo '<div class="notice notice-warning"><p>' .
             esc_html__('Potential security issues found:', 'mini-gallery') .
             '</p></div>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
                <th>' . esc_html__('File Path', 'mini-gallery') . '</th>
                <th>' . esc_html__('Type', 'mini-gallery') . '</th>
                <th>' . esc_html__('Size', 'mini-gallery') . '</th>
              </tr></thead>';
        echo '<tbody>';
        
        foreach ($suspicious_files as $file) {
            echo '<tr>
                    <td>' . esc_html($file['path']) . '</td>
                    <td>' . esc_html($file['extension']) . '</td>
                    <td>' . esc_html($file['size']) . '</td>
                  </tr>';
        }
        
        echo '</tbody></table>';
    }

    private static function get_storage_data()
    {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $plugin_image_ids = [];

        // Get attachment IDs used in plugin post types
        $post_types = ['mgwpp_soora', 'mgwpp_album', 'testimonial'];
        $plugin_query = new WP_Query(
            [
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
            ]
        );

        foreach ($plugin_query->posts as $post_id) {
            $attachments = get_attached_media('image', $post_id);
            foreach ($attachments as $media) {
                $plugin_image_ids[] = $media->ID;
            }
        }

        $plugin_images_total = 0;
        $all_file_types = [];
        $file_count = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upload_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $file_size = $file->getSize();

                $plugin_images_total += $file_size;
                $file_count++;
                
                if (!isset($all_file_types[$ext])) {
                    $all_file_types[$ext] = ['count' => 0, 'size' => 0];
                }
                $all_file_types[$ext]['count'] += 1;
                $all_file_types[$ext]['size'] += $file_size;
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
            'files' => $file_count
        ];
    }

    private static function render_storage_section($used, $total, $percent, $file_types, $file_count)
    {
        ?>
        <div class="mt-4 p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
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
                    <?php foreach ($file_types as $ext => $data) : ?>
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
}
