<?php
// File: includes/admin/class-mgwpp-data-manager.php
if (!defined('ABSPATH')) exit;

class MGWPP_Data_Manager {
    
    public static function get_post_counts() {
        return [
            'galleries' => self::count_post_type('mgwpp_soora'),
            'albums' => self::count_post_type('mgwpp_album'),
            'testimonials' => self::count_post_type('testimonial')
        ];
    }

    public static function count_post_type($post_type) {
        $counts = wp_count_posts($post_type);
        return $counts->publish ?? 0;
    }

    public static function get_storage_data() {
        $upload_dir = wp_upload_dir();
        $data = [
            'used' => 0,
            'total' => 1 * 1024 * 1024 * 1024, // 1GB
            'files' => [],
            'suspicious' => []
        ];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($upload_dir['basedir'], RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    self::process_file($file, $data);
                }
            }
        } catch (Exception $e) {
            //error_log('MGWPP Storage Scan Error: ' . $e->getMessage());
        }

        $data['used'] = size_format($data['used'], 2);
        $data['total'] = size_format($data['total'], 2);
        $data['percent'] = round(($data['used'] / $data['total']) * 100, 2);

        return $data;
    }

    private static function process_file($file, &$data) {
        $path = $file->getPathname();
        $size = $file->getSize();
        $ext = strtolower($file->getExtension());

        $data['used'] += $size;
        
        if (!isset($data['files'][$ext])) {
            $data['files'][$ext] = [
                'count' => 0,
                'size' => 0,
                'size_formatted' => ''
            ];
        }

        $data['files'][$ext]['count']++;
        $data['files'][$ext]['size'] += $size;
        $data['files'][$ext]['size_formatted'] = size_format($data['files'][$ext]['size'], 2);

        if (in_array($ext, ['php', 'js'])) {
            self::check_suspicious_file($path, $data);
        }
    }

    private static function check_suspicious_file($path, &$data) {
        $content = @file_get_contents($path);
        if ($content && preg_match('/(base64_decode|eval|gzinflate|shell_exec|system)/i', $content)) {
            $data['suspicious'][] = [
                'path' => str_replace(wp_upload_dir()['basedir'], '', $path),
                'extension' => pathinfo($path, PATHINFO_EXTENSION)
            ];
        }
    }
}