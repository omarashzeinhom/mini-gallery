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

        //  album management capabilities to administrator
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

       
    }
}


add_filter('map_meta_cap', function ($caps, $cap, $user_id, $args) {
    if ('delete_post' === $cap && isset($args[0])) {
        $post = get_post($args[0]);
        if ('mgwpp_album' === $post->post_type) {
            $caps = ['delete_mgwpp_album'];
        }
    }
    return $caps;
}, 10, 4);
