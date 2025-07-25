<?php
if (!defined('ABSPATH')) {
    exit;
}


class MGWPP_Capabilities
{
   

    /**
     * Set capabilities for gallery post type
     */
    public static function mgwpp_gallery_capabilities()
    {
        $roles = ['administrator'];
        $caps = self::get_gallery_caps();
        
        foreach ($roles as $role_slug) {
            $role = get_role($role_slug);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Set capabilities for album post type
     */
    public static function mgwpp_album_capabilities()
    {
        $roles = ['administrator'];
        $caps = self::get_album_caps();
        
        foreach ($roles as $role_slug) {
            $role = get_role($role_slug);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Set capabilities for testimonial post type
     */
    public static function mgwpp_testimonial_capabilities()
    {
        $roles = ['administrator'];
        $caps = self::get_testimonial_caps();
        
        foreach ($roles as $role_slug) {
            $role = get_role($role_slug);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Get gallery post type capabilities
     */
    private static function get_gallery_caps()
    {
        return [
            'edit_mgwpp_soora',
            'read_mgwpp_soora',
            'delete_mgwpp_soora',
            'edit_mgwpp_sooras',
            'edit_others_mgwpp_sooras',
            'publish_mgwpp_sooras',
            'read_private_mgwpp_sooras',
            'delete_mgwpp_sooras',
            'delete_private_mgwpp_sooras',
            'delete_published_mgwpp_sooras',
            'delete_others_mgwpp_sooras',
            'edit_private_mgwpp_sooras',
            'edit_published_mgwpp_sooras',
        ];
    }

    /**
     * Get album post type capabilities
     */
    private static function get_album_caps()
    {
        return [
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
        ];
    }

    /**
     * Get testimonial post type capabilities
     */
    private static function get_testimonial_caps()
    {
        return [
            'edit_mgwpp_testimonial',
            'read_mgwpp_testimonial',
            'delete_mgwpp_testimonial',
            'edit_mgwpp_testimonials',
            'edit_others_mgwpp_testimonials',
            'publish_mgwpp_testimonials',
            'read_private_mgwpp_testimonials',
            'delete_mgwpp_testimonials',
            'delete_private_mgwpp_testimonials',
            'delete_published_mgwpp_testimonials',
            'delete_others_mgwpp_testimonials',
            'edit_private_mgwpp_testimonials',
            'edit_published_mgwpp_testimonials',
        ];
    }

    /**
     * Add custom capabilities to existing roles
     */
    public static function mgwpp_add_custom_capabilities()
    {
        self::mgwpp_gallery_capabilities();
        self::mgwpp_album_capabilities();
        self::mgwpp_testimonial_capabilities();
    }

    /**
     * Remove all custom capabilities on uninstall
     */
    public static function mgwpp_remove_capabilities()
    {
        // Remove capabilities from roles
        $roles = ['administrator'];
        $all_caps = array_merge(
            self::get_gallery_caps(),
            self::get_album_caps(),
            self::get_testimonial_caps()
        );
        
        foreach ($roles as $role_slug) {
            $role = get_role($role_slug);
            if ($role) {
                foreach ($all_caps as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
}
