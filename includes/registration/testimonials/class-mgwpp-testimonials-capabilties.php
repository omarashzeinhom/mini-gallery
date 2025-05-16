<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Testimonial_Capabilities
{
    public static function mgwpp_testimonial_capabilities()
    {
        // Get the administrator role
        $admin = get_role('administrator');

        // Core testimonial management capabilities
        $testimonial_caps = array(
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

        // Media/attachment specific capabilities
        $media_caps = array(
            'upload_files',                  // Allow file uploads
            'edit_attachments',             // Allow editing media
            'delete_attachments',           // Allow deleting media
            'edit_others_attachments',     // Allow editing others' media
            'delete_others_attachments'    // Allow deleting others' media
        );

        // Add all capabilities to admin
        foreach (array_merge($testimonial_caps, $media_caps) as $cap) {
            $admin->add_cap($cap);
        }

        // Add capabilities to marketing team role
        $marketing = get_role('marketing_team');
        if ($marketing) {
            // Full testimonial capabilities
            foreach ($testimonial_caps as $cap) {
                $marketing->add_cap($cap);
            }

            // Limited media capabilities (only upload and edit their own)
            $marketing->add_cap('upload_files');
            $marketing->add_cap('edit_attachments');
        }

        // Add featured image support for testimonials
        add_post_type_support('mgwpp_testimonial', 'thumbnail');
    }
}
