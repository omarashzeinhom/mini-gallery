<?php
class MGWPP_Security_Uploads_Scanner
{
    const SCAN_THRESHOLD = 5242880; // 5MB
    const DANGEROUS_PERMISSIONS = '0777';

    public static function scan_directory($directory)
    {
        $suspicious_files = [];
        if (!is_dir($directory) || !is_readable($directory)) return $suspicious_files;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $dangerous_extensions = ['php', 'js', 'svg', 'htaccess', 'phar', 'exe', 'dll', 'py', 'pl'];
        $double_ext_regex = '/\.(php|js|exe|dll)(\.|$)/i';

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;

            $path = $file->getPathname();
            $ext = strtolower($file->getExtension());
            $perms = substr(sprintf('%o', $file->getPerms()), -4);

            // Basic checks
            if (
                in_array($ext, $dangerous_extensions) ||
                preg_match($double_ext_regex, $file->getFilename()) ||
                $file->getSize() > self::SCAN_THRESHOLD ||
                $perms === self::DANGEROUS_PERMISSIONS
            ) {
                $suspicious_files[$path] = [
                    'path' => str_replace($directory, '', $path),
                    'extension' => $ext,
                    'perms' => $perms,
                    'size' => size_format($file->getSize(), 2)
                ];
                continue;
            }

            // Content analysis
            try {
                $content = file_get_contents($path);
                $is_suspicious = false;

                // Check for dangerous patterns
                $patterns = [
                    '/(eval\(|exec\(|shell_exec\(|system\(|curl_exec\()/i',
                    '/(base64_decode|gzinflate|str_rot13|create_function)/i',
                    '/(<\?php\s+?\?\>)/',
                    '/(script|iframe|onload|onerror|javascript:)/i'
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $is_suspicious = true;
                        break;
                    }
                }

                // Special SVG checks
                if ($ext === 'svg' && preg_match('/<(script|foreignObject|animate)/i', $content)) {
                    $is_suspicious = true;
                }

                if ($is_suspicious) {
                    $suspicious_files[$path] = [
                        'path' => str_replace($directory, '', $path),
                        'extension' => $ext,
                        'perms' => $perms,
                        'size' => size_format($file->getSize(), 2)
                    ];
                }
            } catch (Exception $e) {
                error_log("Security scan error: " . $e->getMessage());
            }
        }

        return $suspicious_files;
    }

    public static function render_scanner_ui()
    {
?>
        <div class="mgwpp-scanner-container">
            <div class="mgwpp-scanner-header">
                <h2><span class="shield-icon">🛡️</span> File Security Scanner</h2>
            </div>

            <div class="mgwpp-scan-controls">
                <button
                    id="start-scan"
                    class="mgwpp-scan-button"
                    data-nonce="<?php echo wp_create_nonce('security_scan_nonce'); ?>">
                    <span class="scan-icon">🔍</span> Start Security Scan
                </button>
                <div id="scan-status" class="mgwpp-scan-status">
                    <div class="mgwpp-loader"></div>
                    <span>Scanning files...</span>
                </div>
            </div>

            <div id="scan-results" class="mgwpp-scan-results"></div>
        </div>

        <style>
            .mgwpp-scanner-container {
                max-width: 800px;
                margin: 20px auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
                padding: 25px;
            }

            .mgwpp-scanner-header h2 {
                margin: 0 0 25px 0;
                color: #1a237e;
                font-size: 24px;
            }

            .shield-icon {
                margin-right: 10px;
            }

            .mgwpp-scan-button {
                background: #2196F3;
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
            }

            .mgwpp-scan-button:hover {
                background: #1976D2;
                transform: translateY(-1px);
            }

            .mgwpp-scan-button:disabled {
                background: #90CAF9;
                cursor: not-allowed;
            }

            .scan-icon {
                margin-right: 8px;
            }

            .mgwpp-scan-status {
                display: none;
                margin-top: 15px;
                align-items: center;
                color: #666;
            }

            .mgwpp-loader {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #2196F3;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                animation: mgwpp-spin 1s linear infinite;
                margin-right: 10px;
            }

            .mgwpp-scan-results {
                margin-top: 20px;
            }

            .mgwpp-alert {
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .mgwpp-alert.danger {
                background: #ffebee;
                border: 1px solid #ffcdd2;
                color: #b71c1c;
            }

            .mgwpp-alert.success {
                background: #e8f5e9;
                border: 1px solid #c8e6c9;
                color: #2e7d32;
            }

            .mgwpp-file-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                margin-bottom: 15px;
                font-weight: bold;
            }

            .mgwpp-file-item {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                padding: 10px;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 4px;
            }

            .mgwpp-file-item:hover {
                background: #f5f5f5;
            }

            .mgwpp-badge {
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 0.9em;
            }

            .mgwpp-badge.red {
                background: #ffebee;
                color: #b71c1c;
            }

            @keyframes mgwpp-spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>


        <script>
            jQuery(document).ready(function($) {
                $('#start-scan').click(function() {
                    var $btn = $(this);
                    var $status = $('#scan-status');
                    var $results = $('#scan-results');

                    $btn.prop('disabled', true);
                    $status.removeClass('hidden');
                    $results.empty();

                    $.post(ajaxurl, {
                        action: 'mgwpp_security_scan',
                        nonce: $btn.data('nonce')
                    }, function(response) {
                        $results.html(response.data.html);
                    }).fail(function(xhr) {
                        $results.html(`<div class="p-4 bg-red-100 text-red-800 rounded-md">Error: ${xhr.responseText}</div>`);
                    }).always(function() {
                        $btn.prop('disabled', false);
                        $status.addClass('hidden');
                    });
                });
            });
        </script>
    <?php
    }

    public static function render_suspicious_report($suspicious_files) {
        ?>
        <div class="mgwpp-alert <?php echo !empty($suspicious_files) ? 'mgwpp-danger' : 'mgwpp-success'; ?>">
            <?php if (!empty($suspicious_files)) : ?>
                <div class="mgwpp-alert-header">
                    <h3>⚠️ Potential Security Issues Found</h3>
                </div>
    
                <table class="mgwpp-file-table">
                    <thead>
                        <tr>
                            <th>File Path</th>
                            <th>Type</th>
                            <th>Permissions</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suspicious_files as $file) : ?>
                        <tr class="mgwpp-file-row">
                            <td class="mgwpp-file-path"><?php echo esc_html($file['path']); ?></td>
                            <td><span class="mgwpp-badge mgwpp-red"><?php echo strtoupper($file['extension']); ?></span></td>
                            <td><?php echo esc_html($file['perms']); ?></td>
                            <td><?php echo esc_html($file['size']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    
                <div class="mgwpp-security-tips">
                    <p>Security recommendations:</p>
                    <ul class="mgwpp-tips-list">
                        <li>Remove unnecessary executable files</li>
                        <li>Set proper file permissions (644 for files, 755 for directories)</li>
                        <li>Scan for malware using external tools</li>
                    </ul>
                </div>
            <?php else : ?>
                <div class="mgwpp-success-message">
                    <h3>✅ All Clear! No Suspicious Files Found</h3>
                    <p>Regular security scans help maintain your site's integrity.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
