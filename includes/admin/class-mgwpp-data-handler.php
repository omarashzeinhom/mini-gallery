<?php
// File: includes/admin/class-mgwpp-data-handler.php
if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Data_Handler
{
    public static function get_post_count($post_type)
    {
        $counts = wp_count_posts($post_type);
        return $counts->publish ?? 0;
    }

    // Changed to public
    public static function get_storage_data()
    {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $plugin_image_ids = [];

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
        $suspicious_files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upload_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $file_size = $file->getSize();
                $filepath = $file->getPathname();

                $plugin_images_total += $file_size;
                $file_count++;
                
                if (!isset($all_file_types[$ext])) {
                    $all_file_types[$ext] = ['count' => 0, 'size' => 0];
                }
                $all_file_types[$ext]['count']++;
                $all_file_types[$ext]['size'] += $file_size;

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

    // Changed to public and fixed path
    public static function get_installed_gallery_modules()
    {
        $modules = [];
        $gallery_path = plugin_dir_path(dirname(__FILE__)) . 'includes/gallery-types/';

        if (is_dir($gallery_path)) {
            $files = glob($gallery_path . 'class-mgwpp-*.php');
            
            if ($files === false) {
                return [];
            }

            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $type = str_replace(
                    ['class-mgwpp-', '-gallery', '-carousel', '-slider'],
                    '',
                    $filename
                );
                $modules[] = ucfirst(str_replace('_', ' ', $type));
            }
        }

        return $modules;
    }
    
}
