<?php 

class MGWPP_Security_Uploads_Scanner
{
    public static function scan_directory($directory)
    {
        $suspicious_files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $path = $file->getPathname();
                $content = @file_get_contents($path);

                // General suspicious file check
                if ($ext === 'php' || $ext === 'js' || $ext === 'svg') {
                    if (
                        preg_match('/(eval|exec|shell_exec|system|curl_exec|curl_init|file_get_contents|base64_decode|gzinflate)/i', $content)
                        || ($ext === 'svg' && preg_match('/<(script|foreignObject)/i', $content))
                    ) {
                        $suspicious_files[] = [
                            'path' => str_replace($directory, '', $path),
                            'extension' => $ext,
                        ];
                    }
                }
            }
        }

        return $suspicious_files;
    }

    public static function render_suspicious_report($suspicious_files)
    {
        if (!empty($suspicious_files)) {
            ?>
            <div class="mt-4 p-4 border border-red-500 bg-red-100 text-red-800 rounded-md shadow-sm">
                <strong><?php esc_html_e('Warning: Suspicious Files Detected', 'mini-gallery'); ?></strong>
                <p class="text-sm mt-1"><?php esc_html_e('These files may contain dangerous code such as curl, eval, exec, or suspicious SVG content. This section is for advanced debugging and can help identify issues from this or other plugins.', 'mini-gallery'); ?></p>
                <ul class="list-disc list-inside mt-3">
                    <?php foreach ($suspicious_files as $file): ?>
                        <li><code><?php echo esc_html($file['path']); ?></code> (<?php echo esc_html($file['extension']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        } else {
            ?>
            <div class="mt-4 p-4 border border-green-400 bg-green-100 text-green-800 rounded-md shadow-sm">
                <strong><?php esc_html_e('No suspicious files found.', 'mini-gallery'); ?></strong>
            </div>
            <?php
        }
    }
}
