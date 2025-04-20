<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Album_Capabilities
{
    public static function mgwpp_album_capabilities()
    {
        // Get the administrator role
        $admin = get_role('administrator');
        
        // Add album management capabilities to administrator
        $capabilities = array(
            'edit_mgwpp_album',
            'read_mgwpp_album',
            'delete_mgwpp_album',
            'edit_mgwpp_albums',
            'edit_others_mgwpp_albums',
            'publish_mgwpp_albums',
            'read_private_mgwpp_albums',
            'delete_mgwpp_albums',
            'delete_private_mgwpp_albums',
            'delete_published_mgwpp_albums',
            'delete_others_mgwpp_albums',
            'edit_private_mgwpp_albums',
            'edit_published_mgwpp_albums',
            'create_mgwpp_albums'
        );

        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }

        // Add album capabilities to marketing team role if it exists
        $marketing = get_role('marketing_team');
        if ($marketing) {
            foreach ($capabilities as $cap) {
                $marketing->add_cap($cap);
            }
        }
    }
}
