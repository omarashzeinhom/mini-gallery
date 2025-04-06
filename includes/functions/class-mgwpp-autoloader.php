<?php
if (! defined('ABSPATH')) {
    exit;
}
spl_autoload_register(function ($class) {
    // Check if the class belongs to Mini_Gallery
    if (strpos($class, 'Mini_Gallery') !== false) {
        // Convert the class name to a slug format (lowercase and replace underscores with hyphens)
        $class_slug = strtolower(str_replace(['Mini_Gallery_', '_'], ['', '-'], $class));

        // Define the possible directories based on the singular types
        $directories = ['carousel', 'slider', 'gallery'];

        // Iterate over each directory to check for the file
        foreach ($directories as $directory) {
            $file = plugin_dir_path(__FILE__) . 'includes/' . $directory . '/class-mgwpp-' . $class_slug . '.php';
            if (file_exists($file)) {
                include_once $file;
                return;  // Exit after the file is included
            }
        }
    }
});
