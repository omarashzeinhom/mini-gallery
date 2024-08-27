<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete custom post type posts
$mgwpp_soora_posts = get_posts(array(
    'post_type' => 'mgwpp_soora',
    'numberposts' => -1,
    'post_status' => 'any',
));

foreach ($mgwpp_soora_posts as $post) {
    wp_delete_post($post->ID, true);
}

// Remove custom role
remove_role('marketing_team');

// Delete any plugin options stored in the options table
delete_option('mgwpp_some_option_name');

// Delete custom database tables if any
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mgwpp_custom_table");

// Clean up any transients if used
delete_transient('mgwpp_some_transient_name');

// Flush rewrite rules to remove custom post type permalinks
flush_rewrite_rules();
