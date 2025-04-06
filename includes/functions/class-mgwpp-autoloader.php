<?php
if (! defined('ABSPATH')) {
    exit;
}
spl_autoload_register(function ($class) {
    if (strpos($class, 'Mini_Gallery') !== false) {
        $class = strtolower(str_replace(['Mini_Gallery_', '_'], ['','-'], $class));
        $file = plugin_dir_path(__FILE__) . 'includes/class-' . $class . '.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
});
