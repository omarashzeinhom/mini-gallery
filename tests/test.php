<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MiniGalleryTests extends TestCase
{
    public function testPluginFuncs()
    {
        // Call the static methods to initialize classes
        MGWPP_Gallery_Post_Type::mgwpp_register_gallery_post_type();
        MGWPP_Capabilities::mgwpp_gallery_capabilities();

        MGWPP_Gallery_Manager::mgwpp_register_gallery_delete_action(); // Register gallery deletion
        MGWPP_Uninstall::mgwpp_register_uninstall_hook(); // Register the uninstall hook
        MGWPP_Capabilities::mgwpp_add_marketing_team_role();

        //MGWPP_Admin::mgwpp_register_admin_menu(); // Register the admin menu
        //Albums
        MGWPP_Album_Post_Type::mgwpp_register_album_post_type();
        MGWPP_Album_Capabilities::mgwpp_album_capabilities();
    }
};
