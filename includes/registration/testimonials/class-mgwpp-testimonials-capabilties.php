<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Testimonial_Capabilities
{
    public static function mgwpp_testimonial_capabilities()
    {
        $admin = get_role('administrator');

        $testimonial_caps = array(
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
            'create_mgwpp_testimonials'
        );

        // Media/attachment specific capabilities
        $media_caps = array(
            'upload_files',                  // Allow file uploads
            'edit_attachments',             // Allow editing media
            'delete_attachments',           // Allow deleting media
            'edit_others_attachments',     // Allow editing others' media
            'delete_others_attachments'    // Allow deleting others' media
        );

        //  all capabilities to admin
        foreach (array_merge($testimonial_caps, $media_caps) as $cap) {
            $admin->add_cap($cap);
        }

      

        //  featured image support for testimonials
        add_post_type_support('mgwpp_testimonial', 'thumbnail');
    }
}
