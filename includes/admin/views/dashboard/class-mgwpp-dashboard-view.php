<?php
if (!defined('ABSPATH')) exit;

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
                    self::render_header();
                    self::render_stats_grid($stats);
                    self::render_storage_section(MGWPP_Data_Handler::get_storage_data());
                    ?>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_header()
    {
    ?>
        <header class="mgwpp-dashboard-header">
            <div class="mgwpp-branding">
                <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/mgwpp-logo.png'); ?>" class="mgwpp-logo" width="50"
                    height="50" alt="<?php esc_attr_e('Mini Gallery', 'mini-gallery') ?>">
                <div class="mgwpp-titles">
                    <h1 class="mgwpp-title">
                        <?php esc_html_e('Mini Gallery Dashboard', 'mini-gallery') ?>
                        <span class="mgwpp-version"><?php get_file_data(__FILE__, array('Version'), 'mini-gallery'); ?> </span>
                    </h1>
                    <p class="mgwpp-subtitle">
                        <?php esc_html_e('Manage your galleries, albums and testimonials', 'mini-gallery') ?>
                    </p>
                </div>
            </div>
            <div class="mgwpp-header-actions">
                <button id="mgwpp-theme-toggle" class="mgwpp-theme-toggle-button">
                    <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/moon-icon.png'); ?>"
                        alt="<?php esc_attr_e('Theme Toggle', 'mini-gallery') ?>" height="35" width="35"
                        data-sun="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/sun-icon.png'); ?>"
                        data-moon="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/moon-icon.png'); ?>">
                </button>
                <button class="mgwpp-admin-button">
                    <a class="mgwpp-admin-link" href="<?php echo esc_url(admin_url('admin.php?page=mgwpp_galleries')); ?>">
                        <?php esc_html_e('New Gallery', 'mini-gallery') ?>
                        <img src="<?php echo esc_url(MG_PLUGIN_URL . '/admin/images/icons/add-new.png'); ?>"
                            alt="<?php esc_html_e('New Gallery', 'mini-gallery') ?>
                            " height="35" width="35" class="mgwpp-admin-button__icon">
                    </a>
                </button>

            </div>
        </header>

    <?php

    }


    private static function render_stats_grid($stats)
    {
    ?>
        <div class="mgwpp-stats-grid">
            <?php
            self::render_stat_card(__('Galleries', 'mini-gallery'), $stats['galleries'], 'gallery');
            self::render_stat_card(__('Albums', 'mini-gallery'), $stats['albums'], 'album');
            self::render_stat_card(__('Testimonials', 'mini-gallery'), $stats['testimonials'], 'testimonial');
            self::render_stat_card(__('Storage Usage', 'mini-gallery'), $stats['storage-usage'], 'storage-usage');

            ?>
        </div>
    <?php
    }

    private static function render_stat_card($title, $count, $icon)
    {
        $icon_url = MG_PLUGIN_URL . "/admin/images/icons/{$icon}.png";

        // Format storage usage as "85% used"
        if ($icon === 'storage-usage') {
            $display_value = esc_html($count) . '% used';
        } else {
            $display_value = number_format_i18n($count);
        }
    ?>
        <div class="mgwpp-stat-card">
            <div class="mgwpp-stat-content">
                <div class="mgwpp-stat-icon">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($title) ?>" loading="lazy" width="64"
                        height="64">
                </div>
                <div class="mgwpp-stat-info">
                    <h3 class="mgwpp-stat-title"><?php echo esc_html($title); ?></h3>
                    <p class="mgwpp-stat-count"><?php echo $display_value; ?></p>
                </div>
            </div>
        </div>
    <?php
    }

    private static function render_storage_section($storage_data)
    {
    ?>
<?php
    }
}
