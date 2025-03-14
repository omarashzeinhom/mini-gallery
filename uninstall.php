<?php
/**
 * Uninstall Script for Mini Gallery Plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom post type data
$sowar = get_posts(array(
    'post_type' => 'mgwpp_soora',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($sowar as $gallery_image) {
    wp_delete_post(intval($gallery_image->ID), true);
}

// Delete custom role
remove_role('marketing_team');
