<?php
class MGWPP_Admin
{
    // Correct method name to match the action hook
    public static function mgwpp_enqueue_assets()
    {
        wp_register_script('mgwpp-admin-scripts', plugin_dir_url(__FILE__) . 'admin/js/mg-scripts.js', array('jquery'), '1.0', true);
        wp_enqueue_script('mgwpp-admin-scripts');
        
        wp_register_style('mgwpp-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-styles.css', array(), '1.0');
        wp_enqueue_style('mgwpp-admin-styles');
    }

    // You may have other methods, like rendering the plugin page, for admin menu
    public static function mgwpp_render_plugin_page()
    {
        ?>
        <div>
    <h1><?php echo esc_html__('Mini Gallery', 'mini-gallery'); ?></h1>

    <div>
        <label>
            <input type="radio" name="mgwpp_menu_selection" class="app__dashboard-selection" value="dashboard"
                checked />
            DashBoard
        </label>
        <label>
            <input type="radio" name="mgwpp_menu_selection" class="app__dashboard-selection" value="albums" />
            Albums
        </label>
        <label>
            <input type="radio" name="mgwpp_menu_selection" class="app__dashboard-selection" value="galleries" />
            Galleries
        </label>
        <label>
            <input type="radio" name="mgwpp_menu_selection" class="app__dashboard-selection" value="security" />
            Security
        </label>
    </div>

    <!-- Display content based on the selected tab -->
    <div id="mgwpp_dashboard_content" class="mgwpp-tab-content">
        <h2><?php echo esc_html__('Dashboard Content', 'mini-gallery'); ?></h2>
        <!-- Dashboard content goes here -->
        <h2><?php echo esc_html__('Albums', 'mini-gallery'); ?></h2>
        <h2><?php echo esc_html__('Galleries', 'mini-gallery'); ?></h2>

    </div>

    <div id="mgwpp_albums_content" class="mgwpp-tab-content" style="display: none;">
    <h2><?php echo esc_html__('Create New Album', 'mini-gallery'); ?></h2>

    </div>

    <div id="mgwpp_galleries_content" class="mgwpp-tab-content" style="display: none;">
    <h2><?php echo esc_html__('Create New Gallery', 'mini-gallery'); ?></h2>

    <!-- Form for creating new gallery -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="mgwpp_upload">
        <input type="hidden" name="mgwpp_upload_nonce" value="<?php echo esc_attr(wp_create_nonce('mgwpp_upload_nonce')); ?>">

        <table class="form-table">
            <tr>
                <td><label for="sowar"><?php echo esc_html__('Select Images:', 'mini-gallery'); ?></label></td>
                <td><input type="file" id="sowar" name="sowar[]" accept="image/*" required multiple></td>
            </tr>
            <tr>
                <td><label for="image_title"><?php echo esc_html__('Gallery Title:', 'mini-gallery'); ?></label></td>
                <td><input type="text" id="image_title" name="image_title" required></td>
            </tr>
            <tr>
                <td><label for="gallery_type"><?php echo esc_html__('Gallery Type:', 'mini-gallery'); ?></label></td>
                <td>
                    <select id="gallery_type" name="gallery_type" required>
                        <option value="single_carousel"><?php echo esc_html__('Single Carousel', 'mini-gallery'); ?></option>
                        <option value="multi_carousel"><?php echo esc_html__('Multi Carousel', 'mini-gallery'); ?></option>
                        <option value="grid"><?php echo esc_html__('Grid Layout', 'mini-gallery'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" class="button button-primary" value="<?php echo esc_attr__('Upload Images', 'mini-gallery'); ?>">
                </td>
            </tr>
        </table>
    </form>

    <h2><?php echo esc_html__('Existing Galleries', 'mini-gallery'); ?></h2>

    <!-- Display existing galleries in a table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__('Gallery Title', 'mini-gallery'); ?></th>
                <th><?php echo esc_html__('Gallery Type', 'mini-gallery'); ?></th>
                <th><?php echo esc_html__('Shortcode', 'mini-gallery'); ?></th>
                <th><?php echo esc_html__('Actions', 'mini-gallery'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $galleries = get_posts(array(
                    'post_type' => 'mgwpp_soora',
                    'numberposts' => -1
                ));
                if ($galleries) {
                    foreach ($galleries as $gallery) {
            ?>
            <tr>
                <td><?php echo esc_html($gallery->post_title); ?> (ID: <?php echo esc_html($gallery->ID); ?>)</td>
                <td>
                    <?php
                        $gallery_type = get_post_meta($gallery->ID, 'gallery_type', true);
                        echo esc_html(ucfirst($gallery_type));
                    ?>
                </td>
                <td>
                    <pre><?php echo esc_html('[mgwpp_gallery id="' . esc_attr($gallery->ID) . '"]'); ?></pre>
                </td>
                <td>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . esc_attr($gallery->ID)), 'mgwpp_delete_gallery')); ?>" class="button button-secondary"><?php echo esc_html__('Delete Gallery', 'mini-gallery'); ?></a>
                </td>
            </tr>
            <!-- Gallery Details with Preview inside a <details> -->
            <tr>
                <td colspan="4">
                    <details>
                        <summary><?php echo esc_html__('Click to view gallery preview', 'mini-gallery'); ?></summary>
                        
                        <!-- Gallery Preview Section -->
                        <h3><?php echo esc_html__('Gallery Preview', 'mini-gallery'); ?></h3>
                        <div class="mgwpp-gallery-preview">
                            <?php echo do_shortcode('[mgwpp_gallery id="' . esc_attr($gallery->ID) . '"]'); ?>
                        </div>
                        <hr style="border: 1px solid #ccc;">
                    </details>
                </td>
            </tr>
            <?php
                    }
                } else {
            ?>
            <tr>
                <td colspan="4"><?php echo esc_html__('No galleries found.', 'mini-gallery'); ?></td>
            </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
</div>




    <div id="mgwpp_security_content" class="mgwpp-tab-content" style="display: none;">
        <h2><?php echo esc_html__('Security Content', 'mini-gallery'); ?></h2>
        <!-- Security content goes here -->
    </div>

</div>

<script>
// JavaScript to toggle content visibility based on selected radio button
const radios = document.querySelectorAll('input[name="mgwpp_menu_selection"]');
const tabContents = document.querySelectorAll('.mgwpp-tab-content');

radios.forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all tab content
        tabContents.forEach(tab => tab.style.display = 'none');
        // Show the selected tab content
        const selectedTab = `mgwpp_${this.value}_content`;
        document.getElementById(selectedTab).style.display = 'block';
    });
});
</script>
<?php
    }

    public static function mgwpp_add_menu() {
        // Check if the current user has permission to edit the galleries
        if (current_user_can('edit_mgwpp_sooras')) {
            add_menu_page(
                'Add New Mini Gallery',               // Page title
                'Mini Gallery',                       // Menu title
                'edit_mgwpp_sooras',                  // Capability
                'mini-gallery',                       // Menu slug
                array('MGWPP_Admin', 'mgwpp_render_plugin_page'), // Function to render the page
                'dashicons-format-gallery',           // Icon
                6                                     // Position in menu
            );
        }
    }

     // Register the admin menu
     public static function mgwpp_register_menu() {
        add_action('admin_menu', array('MGWPP_Admin', 'mgwpp_add_menu'));
    }
}

// Correctly hook into the action using the updated method name
add_action('admin_enqueue_scripts', array('MGWPP_Admin', 'mgwpp_enqueue_assets'));



?>

