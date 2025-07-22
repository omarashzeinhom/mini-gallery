<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';


class MGWPP_Dashboard_View
{
    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mini-gallery'));
        }

        $stats = [
            'galleries' => MGWPP_Data_Handler::get_post_count('mgwpp_soora'),
            'albums' => MGWPP_Data_Handler::get_post_count('mgwpp_album'),
            'testimonials' => MGWPP_Data_Handler::get_post_count('mgwpp_testimonial'),
            'storage-usage' => MGWPP_Data_Handler::get_storage_data()['percent']
        ];

        ?>
        <div class="mgwpp-dashboard-container">
            <div class="mgwpp-dashboard-wrapper">
                <div class="mgwpp-glass-container">
                    <?php
                    MGWPP_Inner_Header::render();
                    self::render_stats_grid($stats);
                    self::render_storage_section(MGWPP_Data_Handler::get_storage_data());
                    ?>
                </div>
            </div>
        </div>
        <?php
    }




    private static function render_stats_grid($stats)
    {
        $admin_url = admin_url('admin.php');
        ?>
        <div class="mgwpp-stats-grid">
            <?php
            self::render_stat_card(
                __('Galleries', 'mini-gallery'),
                $stats['galleries'],
                'gallery',
                add_query_arg('page', 'mgwpp_galleries', $admin_url)
            );

            self::render_stat_card(
                __('Albums', 'mini-gallery'),
                $stats['albums'],
                'album',
                add_query_arg('page', 'mgwpp_albums', $admin_url)
            );

            self::render_stat_card(
                __('Testimonials', 'mini-gallery'),
                $stats['testimonials'],
                'testimonial',
                add_query_arg('page', 'mgwpp_testimonials', $admin_url)
            );

            self::render_stat_card(
                __('Storage Usage', 'mini-gallery'),
                $stats['storage-usage'],
                'storage-usage',
                add_query_arg('page', 'mgwpp_security', $admin_url)
            );
            ?>
        </div>
        <?php
    }

    private static function render_stat_card($title, $count, $icon, $url = '')
    {
        $icon_url = MG_PLUGIN_URL . "/includes/admin/images/icons/{$icon}.png";

        // Format storage usage as "85% used"
        if ($icon === 'storage-usage') {
            $display_value = esc_html($count) . '% used';
        } else {
            $display_value = number_format_i18n($count);
        }

        //  link wrapper if URL is provided
        $card_content = sprintf(
            '<div class="mgwpp-stat-content">
            <div class="mgwpp-stat-icon">
                <img src="%s" alt="%s" loading="lazy" width="64" height="64">
            </div>
            <div class="mgwpp-stat-info">
                <h3 class="mgwpp-stat-title">%s</h3>
                <p class="mgwpp-stat-count">%s</p>
            </div>
        </div>',
            esc_url($icon_url),
            esc_attr($title),
            esc_html($title),
            $display_value
        );

        // Wrap with link if URL exists
        if ($url) {
            $card_content = sprintf(
                '<a href="%s" class="mgwpp-stat-card-link mgwpp-link-no-decoration">%s</a>',
                esc_url($url),
                $card_content
            );
        }

        echo wp_kses_post('<div class="mgwpp-stat-card">' . $card_content . '</div>');
    }

    private static function render_storage_section($storage_data)
    {
        ?>
        <?php
    }
}
