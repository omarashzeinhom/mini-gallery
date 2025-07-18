<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Extensions_View
{
    const SMUSH_API_URL = 'https://api.resmush.it/ws.php';
    private $settings;

    public function __construct()
    {
        $this->settings = get_option('mgwpp_smush_settings', [
            'enabled' => 0,
            'quality' => 92,
            'keep_exif' => 0
        ]);

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_mgwpp_save_smush_settings', [$this, 'save_settings']);
        add_filter('wp_handle_upload', [$this, 'optimize_uploaded_image']);
        add_action('admin_post_mgwpp_save_pico_gen_settings', [$this, 'save_picogen_settings']);
        add_action('wp_ajax_mgwpp_test_picogen_api', [$this, 'test_picogen_api']);
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'mgwpp-dashboard',
            __('Extensions', 'mini-gallery'),
            __('Extensions', 'mini-gallery'),
            'manage_options',
            'mgwpp-extensions',
            [$this, 'render_extensions_page']
        );
    }

    public function register_settings()
    {
        register_setting('mgwpp_smush_settings_group', 'mgwpp_smush_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);

        // Picogen settings
        register_setting('mgwpp_picogen_settings_group', 'mgwpp_picogen_settings', [
            'sanitize_callback' => [$this, 'sanitize_picogen_settings']
        ]);
    }

    public function sanitize_settings($input)
    {
        $output = [];
        $output['enabled'] = isset($input['enabled']) ? (int)$input['enabled'] : 0;
        $output['quality'] = isset($input['quality']) ? max(0, min(100, (int)$input['quality'])) : 92;
        $output['keep_exif'] = isset($input['keep_exif']) ? (int)$input['keep_exif'] : 0;
        return $output;
    }

    public function save_settings()
    {
        if (!isset($_POST['_wpnonce']) ||
            !wp_verify_nonce($_POST['_wpnonce'], 'mgwpp_smush_settings_nonce')
        ) {
            wp_die(esc_html__('Security check failed', 'mini-gallery'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied', 'mini-gallery'));
        }

        if (isset($_POST['mgwpp_smush_settings'])) {
            update_option('mgwpp_smush_settings', $this->sanitize_settings($_POST['mgwpp_smush_settings']));
            $this->settings = get_option('mgwpp_smush_settings');
        }

        set_transient('mgwpp_smush_notice', 'Settings saved successfully', 30);
        wp_redirect(admin_url('admin.php?page=mgwpp-extensions&tab=smush'));
        exit;
    }

    public function render_extensions_page()
    {
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'smush';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Mini Gallery Extensions', 'mini-gallery'); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=mgwpp-extensions&tab=smush" class="nav-tab <?php echo $current_tab === 'smush' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Image Smush', 'mini-gallery'); ?>
                </a>
                <a href="?page=mgwpp-extensions&tab=pro" class="nav-tab <?php echo $current_tab === 'pro' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Pro Extensions', 'mini-gallery'); ?>
                </a>
            </nav>

            <div class="mgwpp-tab-content">
                <?php if ($current_tab === 'smush') : ?>
                    <?php $this->render_smush_tab(); ?>
                <?php elseif ($current_tab === 'pro') : ?>
                    <?php $this->render_pro_tab(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_smush_tab()
    {
        if ($notice = get_transient('mgwpp_smush_notice')) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
            delete_transient('mgwpp_smush_notice');
        }
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mgwpp_save_smush_settings">
            <?php wp_nonce_field('mgwpp_smush_settings_nonce', '_wpnonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Smush', 'mini-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mgwpp_smush_settings[enabled]" value="1"
                                <?php checked($this->settings['enabled'], 1); ?>>
                            <?php esc_html_e('Optimize images on upload', 'mini-gallery'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="smush-quality"><?php esc_html_e('Quality Level', 'mini-gallery'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="smush-quality" name="mgwpp_smush_settings[quality]"
                            min="0" max="100" value="<?php echo esc_attr($this->settings['quality']); ?>">
                        <p class="description">
                            <?php esc_html_e('Default: 92 (90-95 recommended for best quality/size ratio)', 'mini-gallery'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('EXIF Metadata', 'mini-gallery'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mgwpp_smush_settings[keep_exif]" value="1"
                                <?php checked($this->settings['keep_exif'], 1); ?>>
                            <?php esc_html_e('Preserve EXIF metadata', 'mini-gallery'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Keeping EXIF increases file size but preserves camera information', 'mini-gallery'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Settings', 'mini-gallery')); ?>
        </form>

        <div class="mgwpp-api-docs">
            <h3><?php esc_html_e('Smush API Information', 'mini-gallery'); ?></h3>
            <p><?php esc_html_e('Supported image types: JPG, PNG, GIF, BMP', 'mini-gallery'); ?></p>
            <p><?php esc_html_e('Maximum file size: 5MB', 'mini-gallery'); ?></p>
            <p><?php esc_html_e('Required headers sent automatically:', 'mini-gallery'); ?></p>
            <pre>
User-Agent: <?php echo esc_html(get_bloginfo('name') . '/1.0'); ?>
Referer: <?php echo esc_url(home_url()); ?>
            </pre>
        </div>
        <?php
    }

    private function render_pro_tab()
    {
        ?>
        <div class="mgwpp-pro-promo">
            <h2><?php esc_html_e('Premium Extensions', 'mini-gallery'); ?></h2>
            <p><?php esc_html_e('Enhance your galleries with our premium extensions:', 'mini-gallery'); ?></p>

            <div class="mgwpp-pro-features">
                <div class="feature-card">
                    <h3><?php esc_html_e('Cloud Storage', 'mini-gallery'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Amazon S3 integration', 'mini-gallery'); ?></li>
                        <li><?php esc_html_e('Google Cloud Storage', 'mini-gallery'); ?></li>
                        <li><?php esc_html_e('DigitalOcean Spaces', 'mini-gallery'); ?></li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3><?php esc_html_e('AI Enhancements', 'mini-gallery'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Background removal', 'mini-gallery'); ?></li>
                        <li><?php esc_html_e('Image upscaling', 'mini-gallery'); ?></li>
                        <li><?php esc_html_e('Smart cropping', 'mini-gallery'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="mgwpp-cta">
                <a href="#" class="button button-primary button-large">
                    <?php esc_html_e('Upgrade to Pro', 'mini-gallery'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    public function optimize_uploaded_image($file)
    {
        // Bail if optimization is disabled
        if (!$this->settings['enabled']) {
            return $file;
        }

        // Skip non-image files
        $image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
        if (!in_array($file['type'], $image_types)) {
            return $file;
        }

        // Skip files larger than 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            error_log('Smush: Image too large - ' . $file['name']);
            return $file;
        }

        // Prepare API parameters
        $quality = $this->settings['quality'];
        $keep_exif = $this->settings['keep_exif'] ? 'true' : 'false';
        $api_url = add_query_arg([
            'qlty' => $quality,
            'exif' => $keep_exif
        ], self::SMUSH_API_URL);

        // Prepare file for upload
        $upload = [
            'files' => new CURLFile($file['tmp_name'], $file['type'], $file['name'])
        ];

        // Send to Smush API
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $upload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: ' . get_bloginfo('name') . '/1.0',
                'Referer: ' . home_url()
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Handle errors
        if ($error || $http_code !== 200) {
            error_log('Smush API error: ' . $error . ' | HTTP: ' . $http_code);
            return $file;
        }

        $data = json_decode($response);

        if (!$data || isset($data->error)) {
            $error_msg = $data->error ?? 'Unknown error';
            error_log('Smush failed: ' . $error_msg . ' - ' . $file['name']);
            return $file;
        }

        // Download optimized image
        $tmp_file = download_url($data->dest);

        if (is_wp_error($tmp_file)) {
            error_log('Smush download failed: ' . $tmp_file->get_error_message());
            return $file;
        }

        // Replace original with optimized version
        if (copy($tmp_file, $file['tmp_name'])) {
            $file['size'] = filesize($file['tmp_name']);
            error_log('Smush success: ' . $file['name'] . ' saved ' .
                round((1 - $data->dest_size / $data->src_size) * 100, 1) . '%');
        } else {
            error_log('Smush copy failed: ' . $file['name']);
        }

        // Cleanup
        @unlink($tmp_file);

        return $file;
    }

    public function sanitize_picogen_settings($input)
    {
        $output = [];
        $output['api_token'] = isset($input['api_token']) ? sanitize_text_field($input['api_token']) : '';
        $output['default_ratio'] = isset($input['default_ratio']) ? sanitize_text_field($input['default_ratio']) : '16:9';
        $output['enable_generate'] = isset($input['enable_generate']) ? (int)$input['enable_generate'] : 0;
        return $output;
    }

    // Render Picogen tab
    public function render_picogen_tab()
    {
        $settings = get_option('mgwpp_picogen_settings', [
            'api_token' => '',
            'default_ratio' => '16:9',
            'enable_generate' => 0
        ]);
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mgwpp_save_picogen_settings">
            <?php wp_nonce_field('mgwpp_picogen_settings_nonce', '_wpnonce'); ?>

            <div class="mgwpp-extension-card">
                <div class="mgwpp-extension-header">
                    <span class="dashicons dashicons-art"></span>
                    <h2><?php esc_html_e('Picogen AI Integration', 'mini-gallery'); ?></h2>
                </div>

                <div class="mgwpp-extension-body">
                    <div class="mgwpp-setting-group">
                        <label for="picogen-api-token"><?php esc_html_e('API Token', 'mini-gallery'); ?></label>
                        <input type="password" id="picogen-api-token" name="mgwpp_picogen_settings[api_token]"
                            value="<?php echo esc_attr($settings['api_token']); ?>" class="widefat">
                        <p class="description">
                            <?php esc_html_e('Get your API token from the Picogen dashboard', 'mini-gallery'); ?>
                        </p>

                        <button type="button" id="test-picogen-api" class="button button-secondary">
                            <?php esc_html_e('Test API Connection', 'mini-gallery'); ?>
                        </button>
                        <span id="picogen-test-result"></span>
                    </div>

                    <div class="mgwpp-setting-group">
                        <label for="picogen-ratio"><?php esc_html_e('Default Aspect Ratio', 'mini-gallery'); ?></label>
                        <select id="picogen-ratio" name="mgwpp_picogen_settings[default_ratio]">
                            <option value="1:1" <?php selected($settings['default_ratio'], '1:1'); ?>>1:1 (Square)</option>
                            <option value="16:9" <?php selected($settings['default_ratio'], '16:9'); ?>>16:9 (Landscape)</option>
                            <option value="9:16" <?php selected($settings['default_ratio'], '9:16'); ?>>9:16 (Portrait)</option>
                            <option value="4:3" <?php selected($settings['default_ratio'], '4:3'); ?>>4:3 (Standard)</option>
                            <option value="3:2" <?php selected($settings['default_ratio'], '3:2'); ?>>3:2 (Photo)</option>
                        </select>
                    </div>

                    <div class="mgwpp-setting-group">
                        <label class="mgwpp-toggle">
                            <input type="checkbox" name="mgwpp_picogen_settings[enable_generate]" value="1"
                                <?php checked($settings['enable_generate'], 1); ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="mgwpp-toggle-label">
                            <?php esc_html_e('Enable Image Generation', 'mini-gallery'); ?>
                        </span>
                        <p class="description">
                            <?php esc_html_e('Add "Generate with AI" button to gallery editor', 'mini-gallery'); ?>
                        </p>
                    </div>

                    <div class="mgwpp-extension-footer">
                        <?php submit_button(__('Save Settings', 'mini-gallery'), 'primary', 'submit', false); ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="mgwpp-api-docs">
            <h3><?php esc_html_e('Picogen API Features', 'mini-gallery'); ?></h3>
            <div class="mgwpp-feature-grid">
                <div class="mgwpp-feature-card">
                    <h4><span class="dashicons dashicons-format-image"></span> <?php esc_html_e('Text to Image', 'mini-gallery'); ?></h4>
                    <p><?php esc_html_e('Generate images from text prompts', 'mini-gallery'); ?></p>
                </div>
                <div class="mgwpp-feature-card">
                    <h4><span class="dashicons dashicons-admin-customizer"></span> <?php esc_html_e('Image Upscaling', 'mini-gallery'); ?></h4>
                    <p><?php esc_html_e('Enhance image resolution up to 8K', 'mini-gallery'); ?></p>
                </div>
                <div class="mgwpp-feature-card">
                    <h4><span class="dashicons dashicons-blender"></span> <?php esc_html_e('Image Blending', 'mini-gallery'); ?></h4>
                    <p><?php esc_html_e('Combine multiple images creatively', 'mini-gallery'); ?></p>
                </div>
                <div class="mgwpp-feature-card">
                    <h4><span class="dashicons dashicons-remove"></span> <?php esc_html_e('Background Removal', 'mini-gallery'); ?></h4>
                    <p><?php esc_html_e('Automatically remove image backgrounds', 'mini-gallery'); ?></p>
                </div>
            </div>

            <div class="mgwpp-api-reference">
                <h3><?php esc_html_e('API Documentation', 'mini-gallery'); ?></h3>
                <p><?php esc_html_e('Base URL:', 'mini-gallery'); ?> <code>https://api.picogen.io/v1/</code></p>

                <div class="mgwpp-endpoint">
                    <h4><code>POST /job/generate</code></h4>
                    <pre>{
    "prompt": "A beautiful landscape with mountains",
    "ratio": "16:9"
}</pre>
                </div>

                <div class="mgwpp-endpoint">
                    <h4><code>GET /account/info</code></h4>
                    <pre>{
    "balance": 1607,
    "subscription_plan": "pro"
}</pre>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#test-picogen-api').click(function() {
                    const button = $(this);
                    const token = $('#picogen-api-token').val();
                    button.prop('disabled', true).text('Testing...');
                    $('#picogen-test-result').html('<span class="spinner is-active"></span>');

                    $.post(ajaxurl, {
                        action: 'mgwpp_test_picogen_api',
                        _wpnonce: '<?php echo wp_create_nonce('mgwpp_test_picogen'); ?>',
                        api_token: token
                    }, function(response) {
                        button.prop('disabled', false).text('Test API Connection');
                        if (response.success) {
                            $('#picogen-test-result').html(
                                `<div class="notice notice-success"><p>${response.data.message}</p>
                             <p>Balance: ${response.data.balance} credits</p></div>`
                            );
                        } else {
                            $('#picogen-test-result').html(
                                `<div class="notice notice-error"><p>${response.data}</p></div>`
                            );
                        }
                    }).fail(function() {
                        button.prop('disabled', false).text('Test API Connection');
                        $('#picogen-test-result').html(
                            `<div class="notice notice-error"><p>Request failed</p></div>`
                        );
                    });
                });
            });
        </script>
        <?php
    }

    // Save Picogen settings
    public function save_picogen_settings()
    {
        if (!isset($_POST['_wpnonce']) ||
            !wp_verify_nonce($_POST['_wpnonce'], 'mgwpp_picogen_settings_nonce')
        ) {
            wp_die(esc_html__('Security check failed', 'mini-gallery'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied', 'mini-gallery'));
        }

        if (isset($_POST['mgwpp_picogen_settings'])) {
            update_option('mgwpp_picogen_settings', $this->sanitize_picogen_settings($_POST['mgwpp_picogen_settings']));
        }

        set_transient('mgwpp_picogen_notice', 'Picogen settings saved successfully', 30);
        wp_redirect(admin_url('admin.php?page=mgwpp-extensions&tab=picogen'));
        exit;
    }

    // Test Picogen API connection
    public function test_picogen_api()
    {
        check_ajax_referer('mgwpp_test_picogen', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'mini-gallery'));
        }

        $api_token = isset($_POST['api_token']) ? sanitize_text_field($_POST['api_token']) : '';

        if (empty($api_token)) {
            wp_send_json_error(__('API token is empty', 'mini-gallery'));
        }

        $response = wp_remote_get('https://api.picogen.io/v1/account/info', [
            'headers' => [
                'API-Token' => $api_token
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !is_array($data)) {
            wp_send_json_error(__('Invalid API response', 'mini-gallery'));
        }

        if (isset($data[0]) && $data[0] !== null) {
            wp_send_json_error(__('API error: ', 'mini-gallery') . $data[0]);
        }

        if (!isset($data[1]['balance'])) {
            wp_send_json_error(__('Invalid account info response', 'mini-gallery'));
        }

        wp_send_json_success([
            'message' => __('API connection successful!', 'mini-gallery'),
            'balance' => $data[1]['balance']
        ]);
    }
}

// Initialize only in admin
if (is_admin()) {
    new MGWPP_Extensions_View();
}
