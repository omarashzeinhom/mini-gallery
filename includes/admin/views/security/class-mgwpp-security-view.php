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
        $suspicious_by_type = self::group_suspicious_files($suspicious_files);
        $system_status = empty($suspicious_files) ? 'secure' : 'warning';
        $storage_status = ($storage_data['percent'] < 80) ? 'optimized' : 'warning';
?>
        <div class="mgwpp-dashboard-container">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php MGWPP_Inner_Header::render(); ?>
                    <div id="webcrumbs">
                        <div class="mgwpp-security-dashboard">
                            <div class="mgwpp-dashboard-header">
                                <div class="mgwpp-header-content">
                                    <div>
                                        <h1 class="mgwpp-title"><?php esc_html_e('Security Dashboard', 'mini-gallery'); ?></h1>
                                        <p class="mgwpp-subtitle"><?php esc_html_e('Comprehensive security monitoring and analysis', 'mini-gallery'); ?></p>
                                    </div>
                                    <div class="mgwpp-header-icons">
                                        <div class="mgwpp-icon-circle">
                                            <span class="dashicons dashicons-shield"></span>
                                        </div>
                                        <div class="mgwpp-icon-circle">
                                            <span class="dashicons dashicons-lock"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mgwpp-stats-grid">
                                <?php self::render_stat_card(
                                    'verified',
                                    'verified',
                                    'System Status',
                                    $system_status === 'secure' ? 'SECURE' : 'WARNING',
                                    $system_status === 'secure' ? 'All systems operational' : 'Potential threats detected',
                                    $system_status
                                ); ?>

                                <?php self::render_stat_card(
                                    'landscape',
                                    'monitored',
                                    'Files Scanned',
                                    'MONITORED',
                                    number_format($storage_data['files']) . ' files analyzed',
                                    'monitored'
                                ); ?>

                                <?php self::render_stat_card(
                                    'storage',
                                    'optimized',
                                    'Storage Used',
                                    strtoupper($storage_status),
                                    $storage_data['used'] . ' / ' . $storage_data['total'] . ' (' . $storage_data['percent'] . '%)',
                                    $storage_status
                                ); ?>
                            </div>

                            <div class="mgwpp-main-cards">
                                <div class="mgwpp-card mgwpp-scan-card">
                                    <div class="mgwpp-card-header mgwpp-scan-header">
                                        <div class="mgwpp-card-title">
                                            <span class="dashicons dashicons-warning"></span>
                                            <h3><?php esc_html_e('Suspicious File Scan', 'mini-gallery'); ?></h3>
                                        </div>
                                    </div>

                                    <div class="mgwpp-card-body">
                                        <?php if (empty($suspicious_files)): ?>
                                            <div class="mgwpp-clean-status">
                                                <div class="mgwpp-status-icon">
                                                    <span class="dashicons dashicons-yes"></span>
                                                </div>
                                                <p><?php esc_html_e('No suspicious files found', 'mini-gallery'); ?></p>
                                            </div>
                                        <?php else: ?>
                                            <div class="mgwpp-warning-status">
                                                <div class="mgwpp-status-icon">
                                                    <span class="dashicons dashicons-no"></span>
                                                </div>
                                                <p><?php echo count($suspicious_files) . ' ' . esc_html__('suspicious files found', 'mini-gallery'); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mgwpp-file-types">
                                            <div class="mgwpp-file-type">
                                                <div class="mgwpp-file-info">
                                                    <span class="dashicons dashicons-media-text"></span>
                                                    <span><?php esc_html_e('PHP Files', 'mini-gallery'); ?></span>
                                                </div>
                                                <span class="mgwpp-file-status <?php echo $suspicious_by_type['php'] > 0 ? 'mgwpp-status-threat' : 'mgwpp-status-clean'; ?>">
                                                    <?php echo $suspicious_by_type['php'] > 0 ? esc_html__('Threat', 'mini-gallery') : esc_html__('Clean', 'mini-gallery'); ?>
                                                </span>
                                            </div>

                                            <div class="mgwpp-file-type">
                                                <div class="mgwpp-file-info">
                                                    <span class="dashicons dashicons-media-code"></span>
                                                    <span><?php esc_html_e('JavaScript Files', 'mini-gallery'); ?></span>
                                                </div>
                                                <span class="mgwpp-file-status <?php echo $suspicious_by_type['js'] > 0 ? 'mgwpp-status-threat' : 'mgwpp-status-clean'; ?>">
                                                    <?php echo $suspicious_by_type['js'] > 0 ? esc_html__('Threat', 'mini-gallery') : esc_html__('Clean', 'mini-gallery'); ?>
                                                </span>
                                            </div>

                                            <div class="mgwpp-file-type">
                                                <div class="mgwpp-file-info">
                                                    <span class="dashicons dashicons-media-default"></span>
                                                    <span><?php esc_html_e('Executable Files', 'mini-gallery'); ?></span>
                                                </div>
                                                <span class="mgwpp-file-status <?php echo ($suspicious_by_type['exe'] + $suspicious_by_type['dll']) > 0 ? 'mgwpp-status-threat' : 'mgwpp-status-clean'; ?>">
                                                    <?php echo ($suspicious_by_type['exe'] + $suspicious_by_type['dll']) > 0 ? esc_html__('Threat', 'mini-gallery') : esc_html__('Clean', 'mini-gallery'); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <button class="mgwpp-admin-button mgwpp-admin-button--primary">
                                            <?php esc_html_e('Run New Scan', 'mini-gallery'); ?>
                                        </button>
                                    </div>
                                </div>

                                <div class="mgwpp-card mgwpp-storage-card">
                                    <div class="mgwpp-card-header mgwpp-storage-header">
                                        <div class="mgwpp-card-title">
                                            <span class="dashicons dashicons-chart-bar"></span>
                                            <h3><?php esc_html_e('Storage Analysis', 'mini-gallery'); ?></h3>
                                        </div>
                                    </div>

                                    <div class="mgwpp-card-body">
                                        <div class="mgwpp-storage-overview">
                                            <div class="mgwpp-storage-info">
                                                <span><?php esc_html_e('Storage Usage', 'mini-gallery'); ?></span>
                                                <span class="mgwpp-storage-percent"><?php echo $storage_data['percent']; ?>%</span>
                                            </div>
                                            <div class="mgwpp-progress-bar">
                                                <div class="mgwpp-progress-fill" style="width: <?php echo $storage_data['percent']; ?>%"></div>
                                            </div>
                                            <div class="mgwpp-storage-numbers">
                                                <span><?php echo $storage_data['used']; ?> <?php esc_html_e('used', 'mini-gallery'); ?></span>
                                                <span><?php echo $storage_data['total']; ?> <?php esc_html_e('total', 'mini-gallery'); ?></span>
                                            </div>
                                        </div>

                                        <h4 class="mgwpp-storage-subtitle"><?php esc_html_e('File Types Breakdown', 'mini-gallery'); ?></h4>

                                        <div class="mgwpp-file-breakdown">
                                            <?php
                                            $top_file_types = array_slice($storage_data['file_types'], 0, 3);
                                            foreach ($top_file_types as $ext => $data):
                                            ?>
                                                <div class="mgwpp-file-breakdown-item">
                                                    <div class="mgwpp-breakdown-header">
                                                        <div class="mgwpp-breakdown-info">
                                                            <span class="mgwpp-breakdown-color" style="background-color: <?php echo self::get_file_type_color($ext); ?>"></span>
                                                            <span class="mgwpp-breakdown-name"><?php echo strtoupper($ext); ?></span>
                                                        </div>
                                                        <span class="mgwpp-breakdown-count"><?php echo $data['count']; ?> <?php esc_html_e('files', 'mini-gallery'); ?></span>
                                                    </div>
                                                    <div class="mgwpp-breakdown-bar">
                                                        <div class="mgwpp-breakdown-fill" style="width: <?php echo $data['percent']; ?>%; background-color: <?php echo self::get_file_type_color($ext); ?>"></div>
                                                        <span class="mgwpp-breakdown-percent"><?php echo $data['percent']; ?>%</span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="mgwpp-storage-summary">
                                            <p>
                                                <?php echo number_format($storage_data['files']); ?>
                                                <?php esc_html_e('files scanned across all directories', 'mini-gallery'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mgwpp-card mgwpp-config-card">
                                <div class="mgwpp-card-header mgwpp-config-header">
                                    <div class="mgwpp-card-title">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <h3><?php esc_html_e('Security Configuration', 'mini-gallery'); ?></h3>
                                    </div>
                                    <button class="mgwpp-admin-button"><?php esc_html_e('Configure', 'mini-gallery'); ?></button>
                                </div>

                                <div class="mgwpp-card-body">
                                    <div class="mgwpp-config-grid">
                                        <?php self::render_config_item('lock', 'File Protection', 'ON', 'Active monitoring', 'file-protection'); ?>
                                        <?php self::render_config_item('shield', 'Malware Scan', 'ON', 'Real-time scanning', 'malware-scan'); ?>
                                        <?php self::render_config_item('update', 'Auto Updates', 'AUTO', 'Security patches', 'auto-updates'); ?>
                                        <?php self::render_config_item('backup', 'Backup', 'DAILY', 'Automated backups', 'backup'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    private static function group_suspicious_files($suspicious_files)
    {
        $grouped = [
            'php' => 0,
            'js' => 0,
            'exe' => 0,
            'dll' => 0,
        ];

        foreach ($suspicious_files as $file) {
            $ext = $file['extension'];
            if (isset($grouped[$ext])) {
                $grouped[$ext]++;
            }
        }

        return $grouped;
    }

    private static function get_file_type_color($ext)
    {
        $colors = [
            'jpg' => '#3B82F6',  // Blue
            'png' => '#10B981',  // Green
            'webp' => '#F59E0B', // Amber
            'gif' => '#8B5CF6',  // Violet
            'svg' => '#EC4899',  // Pink
            'php' => '#777BB4',  // Purple
            'js' => '#F0DB4F',   // Yellow
            'css' => '#264DE4',  // Blue
        ];

        return $colors[strtolower($ext)] ?? '#6B7280'; // Default gray
    }

    private static function render_stat_card($icon, $status_type, $title, $status_text, $description, $status)
    {
        $status_class = 'mgwpp-status-' . $status;
        $icon_class = 'dashicons dashicons-' . $icon;
    ?>
        <div class="mgwpp-stat-card <?php echo $status_class; ?>">
            <div class="mgwpp-stat-header">
                <div class="mgwpp-stat-icon">
                    <span class="<?php echo $icon_class; ?>"></span>
                </div>
                <span class="mgwpp-stat-status"><?php echo $status_text; ?></span>
            </div>
            <h3 class="mgwpp-stat-title"><?php echo $title; ?></h3>
            <p class="mgwpp-stat-description"><?php echo $description; ?></p>
        </div>
    <?php
    }

    private static function render_config_item($icon, $title, $status, $description, $key)
    {
        $icon_class = 'dashicons dashicons-' . $icon;
    ?>
        <div class="mgwpp-config-item">
            <div class="mgwpp-config-header">
                <span class="mgwpp-config-icon <?php echo $icon_class; ?>"></span>
                <span class="mgwpp-config-status"><?php echo $status; ?></span>
            </div>
            <h4 class="mgwpp-config-title"><?php echo $title; ?></h4>
            <p class="mgwpp-config-description"><?php echo $description; ?></p>
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
