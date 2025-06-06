<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Security_View
{

    public static function render()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $suspicious_files = self::scan_directory($upload_path);
        $storage_data = self::get_storage_data();
?>
        <div class="mgwpp-dashboard-container">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php MGWPP_Inner_Header::render(); ?>
                    <div class="mgwpp-security-content">
                        <?php
                        self::render_security_header();
                        self::render_scan_results($suspicious_files);
                        self::render_storage_analysis($storage_data);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_security_header()
    {
    ?>
        <div class="mgwpp-security-header">
            <h2><?php echo esc_html__('Security Settings', 'mini-gallery'); ?></h2>
            <p><?php echo esc_html__('This section includes security scan results and will include more options in future updates.', 'mini-gallery'); ?></p>
        </div>
    <?php
    }

    private static function render_scan_results($suspicious_files)
    {
    ?>
        <div class="mgwpp-scan-results">
            <h3><?php echo esc_html__('Suspicious File Scan', 'mini-gallery'); ?></h3>
            <?php self::render_suspicious_report($suspicious_files); ?>
        </div>
    <?php
    }

    private static function render_storage_analysis($storage_data)
    {
    ?>
        <div class="mgwpp-storage-analysis">
            <h3><?php echo esc_html__('Storage Analysis', 'mini-gallery'); ?></h3>
            <?php self::render_storage_section($storage_data); ?>
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
        $post_types = ['mgwpp_soora', 'mgwpp_album', 'mgwpp_testimonial'];
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
    private static function render_storage_section($storage_data)
    {
        $used = $storage_data['used'];
        $total = $storage_data['total'];
        $percent = $storage_data['percent'];
        $file_types = $storage_data['file_types'];
        $file_count = $storage_data['files'];
    ?>
        <div class="mgwpp-storage-card">
            <div class="mgwpp-storage-overview">
                <strong><?php esc_html_e('Used:', 'mini-gallery'); ?></strong>
                <?php echo esc_html($used); ?> /
                <?php echo esc_html($total); ?> (<?php echo esc_html($percent); ?>%)
                <div class="mgwpp-progress-bar">
                    <div class="mgwpp-progress-fill" style="width: <?php echo esc_attr($percent); ?>%"></div>
                </div>
            </div>

            <h4 class="mgwpp-storage-subtitle"><?php esc_html_e('File Types Breakdown', 'mini-gallery'); ?></h4>
            <table class="mgwpp-storage-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Extension', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Count', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Size', 'mini-gallery'); ?></th>
                        <th><?php esc_html_e('Usage %', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($file_types as $ext => $data): ?>
                        <tr>
                            <td><?php echo esc_html($ext); ?></td>
                            <td><?php echo esc_html($data['count']); ?></td>
                            <td><?php echo esc_html($data['size_formatted']); ?></td>
                            <td>
                                <div class="mgwpp-usage-bar">
                                    <div class="mgwpp-usage-fill" style="width: <?php echo esc_attr($data['percent']); ?>%"></div>
                                    <span class="mgwpp-usage-text"><?php echo esc_html($data['percent']); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="mgwpp-storage-summary">
                <?php echo esc_html($file_count); ?> <?php esc_html_e('files scanned.', 'mini-gallery'); ?>
            </p>
        </div>
<?php
    }
}
