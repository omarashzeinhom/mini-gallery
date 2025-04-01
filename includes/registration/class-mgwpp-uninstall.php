<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Uninstall {

    // Cleanup process when the plugin is uninstalled
    public static function mgwpp_plugin_uninstall()
    {
        $sowar = get_posts(array(
            'post_type' => 'mgwpp_soora',
            'numberposts' => -1,
            'post_status' => 'any'
        ));
        foreach ($sowar as $gallery_image) {
            wp_delete_post(intval($gallery_image->ID), true);
        }
        remove_role('marketing_team');
    }

    // Register the uninstall hook
    public static function mgwpp_register_uninstall_hook() {
        register_uninstall_hook(__FILE__, array('MGWPP_Uninstall', 'uninstall'));
    }
}
