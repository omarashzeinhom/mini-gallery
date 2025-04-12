<?php
if (! defined('ABSPATH')) {
    exit;
}

class MGWPP_Admin
{
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'mgwpp_register_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'mgwpp_enqueue_admin_assets'], 20);
    }

    public static function mgwpp_enqueue_admin_assets()
    {
        // Load media scripts first
        wp_enqueue_media();

        // Register main script
        wp_register_script(
            'mgwpp-admin-scripts',
            MG_PLUGIN_URL . '/admin/js/mg-admin-scripts.js',
            ['jquery'], // Only explicit dependency
            filemtime(MG_PLUGIN_PATH . '/admin/js/mg-admin-scripts.js'),
            true
        );

        // Localization
        wp_localize_script('mgwpp-admin-scripts', 'mgwppMedia', [
            'text_title' => __('Select Gallery Images', 'mini-gallery'),
            'text_select' => __('Add to Gallery', 'mini-gallery'),
            'gallery_success' => __('Gallery saved successfully!', 'mini-gallery'),
            'album_success' => __('Album updated successfully!', 'mini-gallery'),
            'generic_error' => __('An error occurred. Please try again.', 'mini-gallery')
        ]);


        wp_enqueue_style(
            'mg-admin-styles',
            MG_PLUGIN_URL . '/admin/css/mg-admin-styles.css',
            [],
        );


        wp_enqueue_script('mgwpp-admin-scripts');
    }

    public static function mgwpp_register_admin_menu()
    {
        add_menu_page(
            __('Mini Gallery', 'mini-gallery'),
            __('Mini Gallery', 'mini-gallery'),
            'manage_options',
            'mgwpp_dashboard',
            [__CLASS__, 'mgwpp_render_dashboard_page'],
            MG_PLUGIN_URL . '/admin/images/mgwpp-logo-panel.png',
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
    private static function get_storage_data()
    {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];
        $plugin_image_ids = [];

        // Get attachment IDs used in plugin post types
        $post_types = ['mgwpp_soora', 'mgwpp_album', 'testimonial'];
        $plugin_query = new WP_Query([
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        foreach ($plugin_query->posts as $post_id) {
            $attachments = get_attached_media('image', $post_id);
            foreach ($attachments as $media) {
                $plugin_image_ids[] = $media->ID;
            }
        }

        $plugin_images_total = 0;
        $all_file_types = [];
        $file_count = 0;
        $suspicious_files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upload_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $file_size = $file->getSize();
                $filepath = $file->getPathname();

                // Tally total uploads folder usage
                $plugin_images_total += $file_size;
                $file_count++;
                if (!isset($all_file_types[$ext])) {
                    $all_file_types[$ext] = ['count' => 0, 'size' => 0];
                }
                $all_file_types[$ext]['count'] += 1;
                $all_file_types[$ext]['size'] += $file_size;



                // Suspicious file scan (basic)
                if (in_array($ext, ['php', 'js'])) {
                    $content = @file_get_contents($filepath);
                    if ($content && preg_match('/(base64_decode|eval|gzinflate|shell_exec|system)/i', $content)) {
                        $suspicious_files[] = [
                            'path' => str_replace($upload_path, '', $filepath),
                            'extension' => $ext,
                        ];
                    }
                }
            }
        }

        $storage_total = 1024 * 1024 * 1024; // 1GB
        $used_percent = round(($plugin_images_total / $storage_total) * 100, 2);

        foreach ($all_file_types as $ext => &$data) {
            $data['size_formatted'] = size_format($data['size'], 2);
            $data['percent'] = round(($data['size'] / $plugin_images_total) * 100, 2);
        }

        return [
            'used' => size_format($plugin_images_total, 2),
            'total' => size_format($storage_total, 2),
            'percent' => $used_percent,
            'file_types' => $all_file_types,
            'files' => $file_count,
            'suspicious' => $suspicious_files,
        ];
    }

    private static function get_installed_gallery_modules()
    {
        $modules = [];
        $gallery_path = plugin_dir_path(__FILE__) . 'includes/gallery-types/';

        // Debugging: Output the directory path
        echo '<p>Checking gallery path: ' . esc_html($gallery_path) . '</p>';

        if (is_dir($gallery_path)) {
            $files = glob($gallery_path . 'class-mgwpp-*.php');

            // Debugging: Output the files found
            echo '<p>Files found: </p><pre>';
            echo esc_html($files);
            echo '</pre>';

            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $type = str_replace(['class-mgwpp-', '-gallery', '-carousel', '-slider'], '', $filename);
                $modules[] = ucfirst(str_replace('_', ' ', $type));
            }
        }

        return $modules;
    }



    private static function render_dashboard_stats()
    {
        // Get counts
        $total_galleries = self::get_post_count('mgwpp_soora');
        $total_albums = self::get_post_count('mgwpp_album');
        $total_testimonials = self::get_post_count('testimonial');
        $total_items = $total_galleries + $total_albums + $total_testimonials;

        // Get storage data (you'll need to implement these methods)
        $storage_data = self::get_storage_data();
        $storage_used = $storage_data['used'];
        $storage_total = $storage_data['total'];
        $storage_percent = $storage_data['percent'];
        $file_types = $storage_data['file_types'];
        $files = $storage_data['files'];
        // Modules installed
        $installed_modules = self::get_installed_gallery_modules();

?>

        <div class="dashboard-stats theme-light" id="dashboard-stats">
            <!-- Header Section -->
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold"><?php echo esc_html__('Dashboard Statistics', 'mini-gallery'); ?></h2>
                <?php self::render_theme_toggle(); ?>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <?php
                self::render_stat_card(
                    __('Galleries', 'mini-gallery'),
                    $total_galleries,
                    'blue',
                    'mgwpp-galleries-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Albums', 'mini-gallery'),
                    $total_albums,
                    'purple',
                    'mgwpp-albums-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Testimonials', 'mini-gallery'),
                    $total_testimonials,
                    'green',
                    'mgwpp-testimonials-icon-dashboard.webp'
                );

                self::render_stat_card(
                    __('Total Items', 'mini-gallery'),
                    $total_items,
                    'amber',
                    'mgwpp-total-items-icon-dashboard.webp'
                );
                ?>
            </div>

            <!-- Storage Visualization Section -->
            <?php self::render_storage_section($storage_used, $storage_total, $storage_percent, $file_types, $files); ?>
            <h4>Installed Gallery Modules:</h4>
            <ul>
                <?php foreach ($installed_modules as $module): ?>
                    <li>✅ <?php echo esc_html($module); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php
    }

    private static function get_post_count($post_type)
    {
        $counts = wp_count_posts($post_type);
        return isset($counts->publish) ? $counts->publish : 0;
    }

    private static function render_theme_toggle()
    {
    ?>
        <button onclick="toggleDashboardTheme()"
            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            aria-label="<?php esc_attr_e('Toggle theme', 'mini-gallery'); ?>">
            <img id="theme-icon-moon"
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-moon-icon.webp'); ?>"
                alt="<?php esc_attr_e('Theme toggle icon', 'mini-gallery'); ?>"
                class="h-6 w-6">
            <img id="theme-icon-sun"
                src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-sun-icon.webp'); ?>"
                alt="<?php esc_attr_e('Sun icon', 'mini-gallery'); ?>"
                class="h-6 w-6 hidden">
        </button>
    <?php
    }

    private static function render_stat_card($title, $count, $color, $icon)
    {
    ?>
        <div class="stat-card group relative overflow-hidden rounded-lg border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400"><?php echo esc_html($title); ?></p>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <?php echo absint($count); ?>
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-<?php echo esc_attr(sanitize_html_class($color)); ?>-500/10 text-<?php echo esc_attr(sanitize_html_class($color)); ?>-500 dark:bg-<?php echo esc_attr(sanitize_html_class($color)); ?>-400/20 dark:text-<?php echo esc_attr(sanitize_html_class($color)); ?>-300">
                    <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/' . $icon); ?>"
                        alt="<?php echo esc_attr($title); ?>"
                        class="h-10 w-10">
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100 dark:via-gray-600"></div>
        </div>
    <?php
    }


    private static function render_storage_section($used, $total, $percent, $file_types, $file_count)
    {
    ?>
        <div class="mt-8 p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Storage Overview', 'mini-gallery'); ?></h3>

            <div class="mb-6">
                <strong><?php esc_html_e('Used:', 'mini-gallery'); ?></strong>
                <?php echo esc_html($used); ?> /
                <?php echo esc_html($total); ?> (<?php echo esc_html($percent); ?>%)
                <div class="h-4 w-full bg-gray-200 rounded mt-1">
                    <div class="h-4 bg-green-500 rounded" style="width: <?php echo esc_attr($percent); ?>%"></div>
                </div>
            </div>

            <h4 class="text-md font-semibold mt-6 mb-2"><?php esc_html_e('File Types Breakdown', 'mini-gallery'); ?></h4>
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="py-2 pr-4"><?php esc_html_e('Extension', 'mini-gallery'); ?></th>
                        <th class="py-2 pr-4"><?php esc_html_e('Count', 'mini-gallery'); ?></th>
                        <th class="py-2 pr-4"><?php esc_html_e('Size', 'mini-gallery'); ?></th>
                        <th class="py-2"><?php esc_html_e('Usage %', 'mini-gallery'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($file_types as $ext => $data): ?>
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-1 pr-4"><?php echo esc_html($ext); ?></td>
                            <td class="py-1 pr-4"><?php echo esc_html($data['count']); ?></td>
                            <td class="py-1 pr-4"><?php echo esc_html($data['size_formatted']); ?></td>
                            <td class="py-1">
                                <div class="w-full bg-gray-200 rounded h-3 relative">
                                    <div class="absolute top-0 left-0 h-3 bg-blue-500 rounded" style="width: <?php echo esc_attr($data['percent']); ?>%"></div>
                                    <span class="absolute left-2 top-0 text-xs text-white leading-3"><?php echo esc_attr($data['percent']); ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <?php echo esc_html($file_count); ?> <?php esc_html_e('files scanned.', 'mini-gallery'); ?>
            </p>
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
                <div id="mgwpp-album-notice" class="mgwpp-notice" style="display: none;"></div>
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
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="mgwpp_create_gallery">
    <?php wp_nonce_field('mgwpp_create_gallery', 'mgwpp_gallery_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <td><label for="gallery_title"><?php echo esc_html__('Gallery Title:', 'mini-gallery'); ?></label></td>
                        <td><input type="text" id="gallery_title" name="gallery_title" required></td>
                    </tr>
                    <tr>
                        <td><label><?php echo esc_html__('Select Images:', 'mini-gallery'); ?></label></td>
                        <td>
                            <div class="media-selection">
                                <input type="hidden" name="selected_media" id="selected_media" value="">
                                <button type="button" class="button button-primary mgwpp-media-upload">
                                    <?php esc_html_e('Choose Images', 'mini-gallery'); ?>
                                </button>
                                <div class="media-preview" style="margin-top: 10px;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="gallery_type"><?php echo esc_html__('Gallery Type:', 'mini-gallery'); ?></label></td>
                        <td>
                            <select id="gallery_type" name="gallery_type" required>
                                <?php
                                $gallery_types = [
                                    "single_carousel" => ["Single Carousel", "single-carousel.webp", "demo-single-carousel"],
                                    "multi_carousel" => ["Multi Carousel", "multi-carousel.webp", "demo-multi-carousel"],
                                    "grid" => ["Grid Layout", "grid.webp", "demo-grid"],
                                    "mega_slider" => ["Mega Slider", "mega-slider.webp", "demo-mega-slider"],
                                    "full_page_slider" => ["Full Page Slider", "full-page-slider.webp", "demo-full-page-slider"], // New entry
                                    "pro_carousel" => ["Pro Multi Card Carousel", "pro-carousel.webp", "demo-pro-carousel"],
                                    "neon_carousel" => ["Neon Carousel", "neon-carousel.webp", "demo-neon-carousel"],
                                    "threed_carousel" => ["3D Carousel", "3d-carousel.webp", "demo-3d-carousel"],
                                    "spotlight_carousel" => ["Spotlight Carousel", "spotlight-carousel.webp", "demo-spotlight-carousel"],
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
                                value="<?php echo esc_attr__('Create Gallery', 'mini-gallery'); ?>">
                        </td>
                    </tr>
                </table>
            </form>
            <script>
                jQuery(document).ready(function($) {
                    let mediaFrame;
                    const selectedMedia = [];

                    $('.mgwpp-media-upload').click(function(e) {
                        e.preventDefault();

                        if (mediaFrame) {
                            mediaFrame.open();
                            return;
                        }

                        mediaFrame = wp.media({
                            title: '<?php esc_html_e("Select Gallery Images", "mini-gallery"); ?>',
                            multiple: true,
                            library: {
                                type: 'image'
                            },
                            button: {
                                text: '<?php esc_html_e("Select", "mini-gallery"); ?>'
                            }
                        });

                        mediaFrame.on('select', function() {
                            const attachments = mediaFrame.state().get('selection').toJSON();
                            selectedMedia.length = 0;

                            attachments.forEach(attachment => {
                                selectedMedia.push(attachment.id);
                            });

                            $('#selected_media').val(selectedMedia.join(','));
                            updateMediaPreview(attachments);
                        });

                        mediaFrame.open();
                    });

                    function updateMediaPreview(attachments) {
                        const preview = $('.media-preview').empty();
                        attachments.forEach(attachment => {
                            preview.append(`
                        <div class="media-thumbnail">
                            <img src="${attachment.sizes.thumbnail.url}" 
                                 alt="${attachment.alt}" 
                                 style="width: 80px; height: 80px; object-fit: cover; margin: 5px;">
                        </div>
                    `);
                        });
                    }
                });
            </script>
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
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];

        // Scan the uploads folder for suspicious files
        $suspicious_files = MGWPP_Security_Uploads_Scanner::scan_directory($upload_path);
    ?>
        <div id="mgwpp_security_content" class="mgwpp-tab-content">
            <h2><?php echo esc_html__('Security Settings', 'mini-gallery'); ?></h2>

            <div class="mgwpp-security-settings">
                <p><?php echo esc_html__('This section includes security scan results and will include more options in future updates.', 'mini-gallery'); ?></p>
            </div>

            <div class="mgwpp-scan-results mt-6">
                <h3 class="text-md font-semibold"><?php echo esc_html__('Suspicious File Scan', 'mini-gallery'); ?></h3>
                <?php MGWPP_Security_Uploads_Scanner::render_suspicious_report($suspicious_files); ?>
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
                            $author = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_author', true));
                            $position = sanitize_text_field(get_post_meta($testimonial->ID, '_mgwpp_position', true));
                            $content = wp_trim_words($testimonial->post_content, 20);
                    ?>
                            <tr>
                                <td><?php echo esc_html($author); ?></td>
                                <td><?php echo esc_html($position); ?></td>
                                <td><?php echo wp_kses_post($content); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($testimonial->ID)); ?>" class="button button-primary">
                                        <?php echo esc_html__('Edit', 'mini-gallery'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(
                                                    admin_url('post.php?post=' . $testimonial->ID . '&action=delete'),
                                                    'delete-post_' . $testimonial->ID
                                                )); ?>" class="button button-danger">
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
