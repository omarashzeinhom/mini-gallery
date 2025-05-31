<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class MGWPP_Gallery_Capabilities {

    // Assign custom capabilities to roles
    public static function mgwpp_gallery_capabilities() {
        $roles = ['administrator', 'marketing_team'];
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                // Add the custom capabilities to the specified roles
                $role->add_cap('edit_mgwpp_soora');
                $role->add_cap('read_mgwpp_soora');
                $role->add_cap('delete_mgwpp_soora');
                $role->add_cap('edit_mgwpp_sooras');
                $role->add_cap('edit_others_mgwpp_sooras');
                $role->add_cap('publish_mgwpp_sooras');
                $role->add_cap('read_private_mgwpp_sooras');
                $role->add_cap('delete_mgwpp_sooras');
                $role->add_cap('delete_private_mgwpp_sooras');
                $role->add_cap('delete_published_mgwpp_sooras');
                $role->add_cap('delete_others_mgwpp_sooras');
                $role->add_cap('edit_private_mgwpp_sooras');
                $role->add_cap('edit_published_mgwpp_sooras');
                $role->add_cap('create_mgwpp_sooras');
            }
        }
    }
}

// Hook to assign custom capabilities after WordPress initializes the roles
add_action('admin_init', array('MGWPP_Gallery_Capabilities', 'mgwpp_gallery_capabilities'));
?>