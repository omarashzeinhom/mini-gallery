<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MGWPP_Testimonial_Capabilities {
    public static function mgwpp_testimonial_capabilities() {
        // Get the administrator role
        $admin = get_role('administrator');
        
        // Add testimonial management capabilities to administrator
        $capabilities = array(
            'edit_testimonial',
            'read_testimonial',
            'delete_testimonial',
            'edit_testimonials',
            'edit_others_testimonials',
            'publish_testimonials',
            'read_private_testimonials',
            'delete_testimonials',
            'delete_private_testimonials',
            'delete_published_testimonials',
            'delete_others_testimonials',
            'edit_private_testimonials',
            'edit_published_testimonials',
            'create_testimonials'
        );

        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }

        // Add testimonial capabilities to marketing team role if it exists
        $marketing = get_role('marketing_team');
        if ($marketing) {
            foreach ($capabilities as $cap) {
                $marketing->add_cap($cap);
            }
        }
    }
}