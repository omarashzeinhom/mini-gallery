<?php 

class MGWPP_Capabilities {

    // Add a custom "Marketing Team" role
    public static function mgwpp_add_marketing_team_role() {
        if (get_role('marketing_team') === null) {
            add_role('marketing_team', 'Marketing Team', array(
                'read' => true,
                'upload_files' => true,
                'edit_files' => true,
                'edit_mgwpp_soora' => true,
                'read_mgwpp_soora' => true,
                'delete_mgwpp_soora' => true,
                'edit_mgwpp_sooras' => true,
                'edit_others_mgwpp_sooras' => true,
                'publish_mgwpp_sooras' => true,
                'read_private_mgwpp_sooras' => true,
                'delete_mgwpp_sooras' => true,
                'delete_private_mgwpp_sooras' => true,
                'delete_published_mgwpp_sooras' => true,
                'delete_others_mgwpp_sooras' => true,
                'edit_private_mgwpp_sooras' => true,
                'edit_published_mgwpp_sooras' => true,
                'create_mgwpp_sooras' => true,
            ));
        }
    }

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

// Hook to initialize custom role on plugin initialization
add_action('init', array('MGWPP_Capabilities', 'mgwpp_add_marketing_team_role'));

// Hook to assign custom capabilities after WordPress initializes the roles
add_action('admin_init', array('MGWPP_Capabilities', 'mgwpp_gallery_capabilities'));
?>