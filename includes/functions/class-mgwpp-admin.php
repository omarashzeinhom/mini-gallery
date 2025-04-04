<?php
if (! defined('ABSPATH')) {
    exit;
}
class MGWPP_Admin
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'mgwpp_register_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'mgwpp_enqueue_admin_assets']);
    }

    public static function mgwpp_enqueue_admin_assets()
    {
        wp_register_script('mgwpp-admin-scripts', MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js', array('jquery'), '1.0', true);
        wp_enqueue_script('mgwpp-admin-scripts');

        wp_register_style('mgwpp-admin-styles', MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css', array(), '1.0');
        wp_enqueue_style('mgwpp-admin-styles');
    }

    public static function mgwpp_register_admin_menu()
    {
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [__CLASS__, 'mgwpp_render_dashboard_page'],
            'dashicons-format-gallery',
            20
        );

        add_submenu_page(
            'mgwpp_dashboard',
            __('Dashboard', 'mini-gallery'),
            __('Dashboard', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [__CLASS__, 'mgwpp_render_dashboard_page']
        );

        add_submenu_page(
            'mgwpp_dashboard',
            __('Albums', 'mini-gallery'),
            __('Albums', 'mini-gallery'),
            'manage_options',
            'mgwpp_albums',
            [__CLASS__, 'mgwpp_render_albums_page']
        );

        add_submenu_page(
            'mgwpp_dashboard',
            __('Galleries', 'mini-gallery'),
            __('Galleries', 'mini-gallery'),
            'manage_options',
            'mgwpp_galleries',
            [__CLASS__, 'mgwpp_render_galleries_page']
        );

        add_submenu_page(
            'mgwpp_dashboard',
            __('Testimonials', 'mini-gallery'),
            __('Testimonials', 'mini-gallery'),
            'manage_options',
            'mgwpp_testimonials',
            [__CLASS__, 'mgwpp_render_testimonials_page']
        );

        add_submenu_page(
            'mgwpp_dashboard',
            __('Security', 'mini-gallery'),
            __('Security', 'mini-gallery'),
            'manage_options',
            'mgwpp_security',
            [__CLASS__, 'mgwpp_render_security_page']
        );
    }

    private static function render_dashboard_stats()
    {
        $gallery_post_statuses = wp_count_posts('mgwpp_soora');
        $album_post_statuses = wp_count_posts('mgwpp_album');
    
        $total_galleries = isset($gallery_post_statuses->publish) ? $gallery_post_statuses->publish : 0;
        $total_albums = isset($album_post_statuses->publish) ? $album_post_statuses->publish : 0;
    
        $testimonial_counts = wp_count_posts('testimonial');
        $total_testimonials = isset($testimonial_counts->publish) ? $testimonial_counts->publish : 0;
       
        ?>



<div class="dashboard-stats theme-light" id="dashboard-stats">
    <?php
       // Temporary test in your PHP
$test_url = MG_PLUGIN_URL . '/admin/images/single-carousel.webp';
echo '<img src="'.esc_url($test_url).'" style="width:100px">';
;?>
        <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold"><?php echo esc_html__('Dashboard Statistics', 'mini-gallery'); ?></h2>
        <button 
            onclick="toggleDashboardTheme()"
            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            aria-label="<?php esc_attr_e('Toggle theme', 'mini-gallery'); ?>"
        >
            <img 
                id="theme-icon-moon" 
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-moon-toggle-icon.webp'); ?>" 
                alt="<?php esc_attr_e('Theme toggle icon', 'mini-gallery'); ?>"
                class="h-5 w-5"
                height="125"
                width="125"
            >
            <img 
                id="theme-icon-sun" 
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-sun-icon.webp'); ?>" 
                alt="<?php esc_attr_e('Sun icon', 'mini-gallery'); ?>"
                class="h-5 w-5 hidden"
            >
        </button>
    </div>
    
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Galleries Card -->
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php esc_html_e('Galleries', 'mini-gallery'); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($total_galleries); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 text-blue-500 dark:bg-blue-400/20 dark:text-blue-300">
                    <img 
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-galleries-icon-dashboard.webp'); ?>" 
                        alt="<?php esc_attr_e('Galleries icon', 'mini-gallery'); ?>"
                        class="h-6 w-6"
                    >
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
        
        <!-- Albums Card -->
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php esc_html_e('Albums', 'mini-gallery'); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($total_albums); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-500/10 text-purple-500 dark:bg-purple-400/20 dark:text-purple-300">
                    <img 
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-albums-icon-dashboard.webp'); ?>" 
                        alt="<?php esc_attr_e('Albums icon', 'mini-gallery'); ?>"
                        class="h-6 w-6"
                    >
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
        
        <!-- Testimonials Card -->
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php esc_html_e('Testimonials', 'mini-gallery'); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($total_testimonials); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500/10 text-green-500 dark:bg-green-400/20 dark:text-green-300">
                    <img 
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-testimonials-icon-dashboard.webp'); ?>" 
                        alt="<?php esc_attr_e('Testimonials icon', 'mini-gallery'); ?>"
                        class="h-6 w-6"
                    >
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
        
        <!-- Total Items Card -->
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php esc_html_e('Total Items', 'mini-gallery'); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($total_galleries + $total_albums + $total_testimonials); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 dark:bg-amber-400/20 dark:text-amber-300">
                    <img 
                        src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-total-items-icon-dashboard.webp'); ?>" 
                        alt="<?php esc_attr_e('Total items icon', 'mini-gallery'); ?>"
                        class="h-6 w-6"
                    >
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
    </div>
</div>
        <?php
    }
    public static function mgwpp_render_dashboard_page()
    {
        echo '<div class="wrap"><h1>' . esc_html__('Dashboard Overview', 'mini-gallery') . '</h1>';
        self::render_dashboard_stats();
        echo '</div>';
    }

    public static function mgwpp_render_albums_page()
    {
    ?>
        <div id="mgwpp_albums_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Create New Album', 'mini-gallery'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mgwpp_create_album">
                <input type="hidden" name="mgwpp_album_submit_nonce"
                    value="<?php echo esc_attr(wp_create_nonce('mgwpp_album_submit_nonce')); ?>">
                <table class="form-table">
                    <tr>
                        <td><label for="album_title"><?php echo esc_html__('Album Title:', 'mini-gallery'); ?></label></td>
                        <td><input type="text" id="album_title" name="album_title" required></td>
                    </tr>
                    <tr>
                        <td><label
                                for="album_description"><?php echo esc_html__('Album Description:', 'mini-gallery'); ?></label>
                        </td>
                        <td><textarea id="album_description" name="album_description" rows="3"
                                class="album__description"></textarea></td>
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
                            <input type="submit" class="button button-primary"
                                value="<?php echo esc_attr__('Create Album', 'mini-gallery'); ?>">
                        </td>
                    </tr>
                </table>
            </form>

            <h2><?php echo esc_html__('Existing Albums', 'mini-gallery'); ?></h2>
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
                                <td>
                                    <pre>[mgwpp_album id="<?php echo esc_attr($album->ID); ?>"]</pre>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($album->ID)); ?>"
                                        class="button button-secondary"><?php echo esc_html__('Edit', 'mini-gallery'); ?></a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_album&album_id=' . $album->ID), 'mgwpp_delete_album_' . $album->ID)); ?>"
                                        class="button button-secondary"
                                        onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this album?', 'mini-gallery')); ?>')"><?php echo esc_html__('Delete', 'mini-gallery'); ?></a>
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
    <?php
    }

    public static function mgwpp_render_galleries_page()
    {
    ?>
        <div id="mgwpp_galleries_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Create New Gallery', 'mini-gallery'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="mgwpp_upload">
                <input type="hidden" name="mgwpp_upload_nonce"
                    value="<?php echo esc_attr(wp_create_nonce('mgwpp_upload_nonce')); ?>">
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
                                <?php
                                $gallery_types = [
                                    "single_carousel"     => ["Single Carousel", "single-carousel.webp", "demo-single-carousel"],
                                    "multi_carousel"      => ["Multi Carousel", "multi-carousel.webp", "demo-multi-carousel"],
                                    "grid"                => ["Grid Layout", "grid.webp", "demo-grid"],
                                    "mega_slider"         => ["Mega Slider", "mega-slider.webp", "demo-mega-slider"],
                                    "pro_carousel"        => ["Pro Multi Card Carousel", "pro-carousel.webp", "demo-pro-carousel"],
                                    "neon_carousel"       => ["Neon Carousel", "neon-carousel.webp", "demo-neon-carousel"],
                                    "threed_carousel"     => ["3D Carousel", "3d-carousel.webp", "demo-3d-carousel"],
                                    "testimonials_carousel" => ["Testimonials Carousel", "testimonials.webp", "demo-testimonials"]
                                ];
                                foreach ($gallery_types as $key => $info) {
                                    echo sprintf(
                                        '<option value="%s" data-image="%s" data-demo="%s">%s</option>',
                                        esc_attr($key),
                                        esc_url(MG_PLUGIN_URL . '/admin/images/' . $info[1]),
                                        esc_url('https://your-demo-site.com/' . $info[2]),
                                        esc_html($info[0])
                                    );
                                }
                                ?>
                            </select>

                            <div id="gallery_preview" style="display: none; margin-top: 10px;">
                                <img id="preview_img" src="" alt="" style="">
                                <a id="preview_demo" href="" target="_blank" style="display: block; margin-top: 5px; text-decoration: none; color: #0073aa; font-weight: bold;"><?php echo esc_html__('View Demo', 'mini-gallery'); ?></a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="submit" class="button button-primary"
                                value="<?php echo esc_attr__('Upload Images', 'mini-gallery'); ?>">
                        </td>
                    </tr>
                </table>
            </form>

            <h2><?php echo esc_html__('Existing Galleries', 'mini-gallery'); ?></h2>
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
                            $gallery_type = get_post_meta($gallery->ID, 'gallery_type', true);
                    ?>
                            <tr>
                                <td><?php echo esc_html($gallery->post_title); ?> (ID: <?php echo esc_html($gallery->ID); ?>)</td>
                                <td><?php echo esc_html(ucfirst($gallery_type)); ?></td>
                                <td>
                                    <pre>[mgwpp_gallery id="<?php echo esc_attr($gallery->ID); ?>"]</pre>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mgwpp_delete_gallery&gallery_id=' . $gallery->ID), 'mgwpp_delete_gallery')); ?>"
                                        class="button button-secondary"><?php echo esc_html__('Delete Gallery', 'mini-gallery'); ?></a>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=mgwpp-edit-gallery&gallery_id=' . $gallery->ID), 'mgwpp_edit_gallery')); ?>"
                                        class="button button-primary">Edit Gallery</a>
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
                        echo '<tr><td colspan="4">' . esc_html__('No galleries found.', 'mini-gallery') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public static function mgwpp_render_security_page()
    {
    ?>
        <div id="mgwpp_security_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Security Settings', 'mini-gallery'); ?></h2>
            <div class="mgwpp-security-settings">
                <p><?php echo esc_html__('Security settings and role management will be available in future updates.', 'mini-gallery'); ?>
                </p>
            </div>
        </div>
    <?php
    }

    public static function mgwpp_render_testimonials_page()
    {
    ?>
        <div id="mgwpp_testimonials_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Manage Testimonials', 'mini-gallery'); ?></h2>

            <div class="mgwpp-testimonial-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=testimonial')); ?>" class="button button-primary">
                    <?php echo esc_html__('Add New Testimonial', 'mini-gallery'); ?>
                </a>
            </div>

            <h3><?php echo esc_html__('Existing Testimonials', 'mini-gallery'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Author', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Position/Company', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Testimonial', 'mini-gallery'); ?></th>
                        <th><?php echo esc_html__('Actions', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $testimonials = get_posts([
                        'post_type' => 'testimonial',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    ]);

                    if ($testimonials) {
                        foreach ($testimonials as $testimonial) {
                            $author = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_testimonial_author', true));
                            $position = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_testimonial_position', true));
                    ?>
                            <tr>
                                <td><?php echo esc_html($author); ?></td>
                                <td><?php echo esc_html($position); ?></td>
                                <td><?php echo wp_kses_post(wp_trim_words($testimonial->post_content, 20)); ?></td>
                                <td><?php echo esc_html($author); ?></td>
                                <td><?php echo esc_html($position); ?></td>
                                <td><?php echo wp_kses_post(wp_trim_words($testimonial->post_content, 20)); ?></td>
                                <td><?php echo wp_kses_post(wp_trim_words($testimonial->post_content, 20)); ?></td>
                                <td><?php echo wp_kses_post(wp_trim_words($testimonial->post_content, 20)); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($testimonial->ID)); ?>" class="button button-primary">
                                        <?php echo esc_html__('Edit', 'mini-gallery'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('post.php?post=' . $testimonial->ID . '&action=delete'), 'delete-post_' . $testimonial->ID)); ?>" class="button button-danger">
                                        <?php echo esc_html__('Delete', 'mini-gallery'); ?>
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="4">' . esc_html__('No testimonials found.', 'mini-gallery') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
<?php
    }
}

MGWPP_Admin::init();
