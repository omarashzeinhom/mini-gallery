<?php
class MGWPP_Admin
{
    public static function mgwpp_enqueue_admin_assets()
    {
        wp_register_script('mgwpp-admin-scripts', plugin_dir_url(__FILE__) . 'admin/js/mg-scripts.js', array('jquery'), '1.0', true);
        wp_enqueue_script('mgwpp-admin-scripts');

        wp_register_style('mgwpp-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/mg-styles.css', array(), '1.0');
        wp_enqueue_style('mgwpp-admin-styles');
    }

    public static function mgwpp_render_plugin_page()
    {
?>
        <div>
            <h1><?php echo esc_html__('Mini Gallery', 'mini-gallery'); ?></h1>

            <div>
                <label>
                    <input type="radio" name="mgwpp_menu_selection" class="app__dashboard-selection" value="dashboard" checked />
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

            <!-- Dashboard Tab -->
            <div id="mgwpp_dashboard_content" class="mgwpp-tab-content">
                <h2><?php echo esc_html__('Dashboard Overview', 'mini-gallery'); ?></h2>
                <?php self::render_dashboard_stats(); ?>
            </div>

            <!-- Albums Tab -->
            <div id="mgwpp_albums_content" class="mgwpp-tab-content" style="display: none;">
                <h2><?php echo esc_html__('Create New Album', 'mini-gallery'); ?></h2>
                
                <!-- Album Creation Form -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="mgwpp_create_album">
                    <input type="hidden" name="mgwpp_album_submit_nonce" value="<?php echo esc_attr(wp_create_nonce('mgwpp_album_submit_nonce')); ?>">

                    <table class="form-table">
                        <tr>
                            <td><label for="album_title"><?php echo esc_html__('Album Title:', 'mini-gallery'); ?></label></td>
                            <td><input type="text" id="album_title" name="album_title" required></td>
                        </tr>
                        <tr>
                            <td><label for="album_description"><?php echo esc_html__('Album Description:', 'mini-gallery'); ?></label></td>
                            <td><textarea id="album_description" name="album_description" rows="3" class="album__description"></textarea></td>
                        </tr>
                        <tr>
                            <td><label><?php echo esc_html__('Select Galleries:', 'mini-gallery'); ?></label></td>
                            <td>
                                <?php
                                $galleries = get_posts(array(
                                    'post_type' => 'mgwpp_soora',
                                    'numberposts' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ));
                                
                                if ($galleries) {
                                    foreach ($galleries as $gallery) {
                                        echo '<label style="display: block; margin-bottom: 5px;">';
                                        echo '<input type="checkbox" name="album_galleries[]" value="' . esc_attr($gallery->ID) . '"> ';
                                        echo esc_html($gallery->post_title);
                                        echo '</label>';
                                    }
                                } else {
                                    echo '<p>' . esc_html__('No galleries available. Create some galleries first.', 'mini-gallery') . '</p>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <input type="submit" class="button button-primary" value="<?php echo esc_attr__('Create Album', 'mini-gallery'); ?>">
                            </td>
                        </tr>
                    </table>
                </form>

                <h2><?php echo esc_html__('Existing Albums', 'mini-gallery'); ?></h2>
                
                <!-- Display existing albums in a table -->
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Album Title', 'mini-gallery'); ?></th>
                            <th><?php echo esc_html__('Number of Galleries', 'mini-gallery'); ?></th>
                            <th><?php echo esc_html__('Shortcode', 'mini-gallery'); ?></th>
                            <th><?php echo esc_html__('Actions', 'mini-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $albums = get_posts(array(
                            'post_type' => 'mgwpp_album',
                            'numberposts' => -1
                        ));

                        if ($albums) {
                            foreach ($albums as $album) {
                                $galleries = get_post_meta($album->ID, '_mgwpp_album_galleries', true);
                                $gallery_count = is_array($galleries) ? count($galleries) : 0;
                        ?>
                                <tr>
                                    <td><?php echo esc_html($album->post_title); ?></td>
                                    <td><?php echo esc_html($gallery_count); ?></td>
                                    <td><pre>[mgwpp_album id="<?php echo esc_attr($album->ID); ?>"]</pre></td>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($album->ID)); ?>" class="button button-secondary"><?php echo esc_html__('Edit', 'mini-gallery'); ?></a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_album&album_id=' . $album->ID), 'mgwpp_delete_album_' . $album->ID)); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this album?', 'mini-gallery')); ?>')"><?php echo esc_html__('Delete', 'mini-gallery'); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <details>
                                            <summary><?php echo esc_html__('Album Contents', 'mini-gallery'); ?></summary>
                                            <?php
                                            if ($galleries && is_array($galleries)) {
                                                echo '<ul class="mgwpp-album-galleries-list">';
                                                foreach ($galleries as $gallery_id) {
                                                    $gallery = get_post($gallery_id);
                                                    if ($gallery) {
                                                        echo '<li>' . esc_html($gallery->post_title) . '</li>';
                                                    }
                                                }
                                                echo '</ul>';
                                            } else {
                                                echo '<p>' . esc_html__('No galleries in this album.', 'mini-gallery') . '</p>';
                                            }
                                            ?>
                                        </details>
                                        <hr style="border: 1px solid black;" />
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="4">' . esc_html__('No albums found.', 'mini-gallery') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Galleries Tab -->
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
                                    <td>
                                        <?php echo esc_html($gallery->post_title); ?>
                                        <?php echo esc_attr('( ID:', 'mgwpp-gallery'); ?>
                                        <?php echo esc_html($gallery->ID); ?>
                                        <?php echo esc_attr(')', 'mgwpp-gallery'); ?>
                                    </td>
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
                                <tr>
                                    <td colspan="4">
                                        <details>
                                            <summary><?php echo esc_html__('Click to view gallery preview', 'mini-gallery'); ?></summary>
                                            <h3><?php echo esc_html__('Gallery Preview', 'mini-gallery'); ?></h3>
                                            <div class="mgwpp-gallery-preview">
                                                <?php echo do_shortcode('[mgwpp_gallery id="' . esc_attr($gallery->ID) . '"]'); ?>
                                            </div>
                                        </details>
                                        <hr style="border: 1px solid black;" />
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

            <!-- Security Tab -->
            <div id="mgwpp_security_content" class="mgwpp-tab-content" style="display: none;">
                <h2><?php echo esc_html__('Security Settings', 'mini-gallery'); ?></h2>
                <?php self::render_security_content(); ?>
            </div>
        </div>

        <script>
            // JavaScript to toggle content visibility based on selected radio button
            const radios = document.querySelectorAll('input[name="mgwpp_menu_selection"]');
            const tabContents = document.querySelectorAll('.mgwpp-tab-content');

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    tabContents.forEach(tab => tab.style.display = 'none');
                    const selectedTab = `mgwpp_${this.value}_content`;
                    document.getElementById(selectedTab).style.display = 'block';
                });
            });
        </script>
<?php
    }

  
    private static function render_dashboard_stats() {
        $gallery_post_statuses = wp_count_posts('mgwpp_soora');
        $album_post_statuses = wp_count_posts('mgwpp_album');
    
        $total_galleries = isset($gallery_post_statuses->publish) ? $gallery_post_statuses->publish : 0;
        $total_albums = isset($album_post_statuses->publish) ? $album_post_statuses->publish : 0;
        ?>
        <div class="mgwpp-dashboard-stats">
            <div class="stat-box">
                <h3><?php echo esc_html__('Total Galleries', 'mini-gallery'); ?></h3>
                <p class="stat-number"><?php echo esc_html($total_galleries); ?></p>
            </div>
            <div class="stat-box">
                <h3><?php echo esc_html__('Total Albums', 'mini-gallery'); ?></h3>
                <p class="stat-number"><?php echo esc_html($total_albums); ?></p>
            </div>
        </div>
        <?php
    }
    
    private static function render_security_content() {
        ?>
        <div class="mgwpp-security-settings">
            <p><?php echo esc_html__('Security settings and role management will be available in future updates.', 'mini-gallery'); ?></p>
        </div>
        <?php
    }

    public static function mgwpp_add_menu()
    {
        if (current_user_can('edit_mgwpp_sooras')) {
            add_menu_page(
                'Add New Mini Gallery',
                'Mini Gallery',
                'edit_mgwpp_sooras',
                'mini-gallery',
                array('MGWPP_Admin', 'mgwpp_render_plugin_page'),
                'dashicons-format-gallery',
                7
            );
        }
    }

    public static function mgwpp_register_menu()
    {
        add_action('admin_menu', array('MGWPP_Admin', 'mgwpp_add_menu'));
    }
}

// Hook the admin enqueue scripts
add_action('admin_enqueue_scripts', array('MGWPP_Admin', 'mgwpp_enqueue_admin_assets'));

// Enable shortcodes in widgets and content
add_filter('widget_text', 'do_shortcode');
add_filter('the_content', 'do_shortcode');
?>
