<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once MG_PLUGIN_PATH . 'includes/admin/views/inner-header/class-mgwpp-inner-header.php';

class MGWPP_Extensions_View {
    const SMUSH_API_URL = 'https://api.resmush.it/ws.php';
    private $settings;

    public function __construct() {
        $this->settings = get_option('mgwpp_smush_settings', [
            'enabled'   => 0,
            'quality'   => 92,
            'keep_exif' => 0,
        ]);

        add_action('admin_menu',          [$this, 'add_admin_menu']);
        add_action('admin_init',          [$this, 'register_settings']);
        add_action('admin_post_mgwpp_save_smush_settings',    [$this, 'save_settings']);
        add_filter('wp_handle_upload',    [$this, 'optimize_uploaded_image']);
        add_action('admin_post_mgwpp_save_picogen_settings',  [$this, 'save_picogen_settings']);
        add_action('wp_ajax_mgwpp_test_picogen_api',          [$this, 'test_picogen_api']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'mgwpp-dashboard',
            esc_html__('Extensions', 'mini-gallery'),
            esc_html__('Extensions', 'mini-gallery'),
            'manage_options',
            'mgwpp-extensions',
            [$this, 'render_extensions_page']
        );
    }

    public function register_settings() {
        register_setting('mgwpp_smush_settings_group', 'mgwpp_smush_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);

        register_setting('mgwpp_picogen_settings_group', 'mgwpp_picogen_settings', [
            'sanitize_callback' => [$this, 'sanitize_picogen_settings'],
        ]);
    }

    public function sanitize_settings($input) {
        $input = wp_unslash($input);
        return [
            'enabled'   => isset($input['enabled'])   ? (int) $input['enabled']   : 0,
            'quality'   => isset($input['quality'])   ? max(0, min(100, (int) $input['quality'])) : 92,
            'keep_exif' => isset($input['keep_exif']) ? (int) $input['keep_exif'] : 0,
        ];
    }

    public function save_settings() {
        $data = wp_unslash($_POST);
        if (empty($data['_wpnonce']) || !wp_verify_nonce($data['_wpnonce'], 'mgwpp_smush_settings_nonce')) {
            wp_die(esc_html__('Security check failed', 'mini-gallery'));
        }
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied', 'mini-gallery'));
        }
        $settings = isset($data['mgwpp_smush_settings']) ? $data['mgwpp_smush_settings'] : [];
        update_option('mgwpp_smush_settings', $this->sanitize_settings($settings));
        set_transient('mgwpp_smush_notice', esc_html__('Settings saved successfully', 'mini-gallery'), 30);
        wp_safe_redirect(admin_url('admin.php?page=mgwpp-extensions&tab=smush'));
        exit;
    }

    public function render_extensions_page() {
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'smush';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Mini Gallery Extensions', 'mini-gallery'); ?></h1>
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url(add_query_arg(['page'=>'mgwpp-extensions','tab'=>'smush'], admin_url('admin.php'))); ?>"
                   class="nav-tab <?php echo $tab==='smush' ? 'nav-tab-active':''; ?>">
                    <?php esc_html_e('Image Smush', 'mini-gallery'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page'=>'mgwpp-extensions','tab'=>'pro'], admin_url('admin.php'))); ?>"
                   class="nav-tab <?php echo $tab==='pro' ? 'nav-tab-active':''; ?>">
                    <?php esc_html_e('Pro Extensions', 'mini-gallery'); ?>
                </a>
            </nav>
            <div class="mgwpp-tab-content">
                <?php
                if ($tab==='smush') {
                    $this->render_smush_tab();
                } elseif ($tab==='pro') {
                    $this->render_pro_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_smush_tab() {
        if ($notice = get_transient('mgwpp_smush_notice')) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
            delete_transient('mgwpp_smush_notice');
        }
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('mgwpp_smush_settings_nonce','_wpnonce'); ?>
            <input type="hidden" name="action" value="mgwpp_save_smush_settings">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Smush','mini-gallery');?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mgwpp_smush_settings[enabled]" value="1" <?php checked($this->settings['enabled'],1);?>>
                            <?php esc_html_e('Optimize images on upload','mini-gallery');?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="smush-quality"><?php esc_html_e('Quality Level','mini-gallery');?></label></th>
                    <td>
                        <input type="number" id="smush-quality" name="mgwpp_smush_settings[quality]" min="0" max="100" value="<?php echo esc_attr($this->settings['quality']); ?>">
                        <p class="description"><?php esc_html_e('Default: 92 (90-95 recommended)','mini-gallery');?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('EXIF Metadata','mini-gallery');?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mgwpp_smush_settings[keep_exif]" value="1" <?php checked($this->settings['keep_exif'],1);?>>
                            <?php esc_html_e('Preserve EXIF metadata','mini-gallery');?>
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(esc_html__('Save Settings','mini-gallery')); ?>
        </form>
        <div class="mgwpp-api-docs">
            <h3><?php esc_html_e('Smush API Information','mini-gallery');?></h3>
            <p><?php esc_html_e('Supported image types: JPG, PNG, GIF, BMP','mini-gallery');?></p>
            <p><?php esc_html_e('Maximum file size: 5MB','mini-gallery');?></p>
            <pre>
User-Agent: <?php echo esc_html(get_bloginfo('name').'/1.0');?>
Referer: <?php echo esc_url(home_url());?>
            </pre>
        </div>
        <?php
    }

    private function render_pro_tab() {
        ?>
        <div class="mgwpp-pro-promo">
            <h2><?php esc_html_e('Premium Extensions','mini-gallery');?></h2>
            <p><?php esc_html_e('Enhance your galleries with our premium extensions:','mini-gallery');?></p>
            <div class="mgwpp-pro-features">
                <div class="feature-card">
                    <h3><?php esc_html_e('Cloud Storage','mini-gallery');?></h3>
                    <ul>
                        <li><?php esc_html_e('Amazon S3 integration','mini-gallery');?></li>
                        <li><?php esc_html_e('Google Cloud Storage','mini-gallery');?></li>
                        <li><?php esc_html_e('DigitalOcean Spaces','mini-gallery');?></li>
                    </ul>
                </div>
                <div class="feature-card">
                    <h3><?php esc_html_e('AI Enhancements','mini-gallery');?></h3>
                    <ul>
                        <li><?php esc_html_e('Background removal','mini-gallery');?></li>
                        <li><?php esc_html_e('Image upscaling','mini-gallery');?></li>
                        <li><?php esc_html_e('Smart cropping','mini-gallery');?></li>
                    </ul>
                </div>
            </div>
            <div class="mgwpp-cta">
                <a href="#" class="button button-primary button-large"><?php esc_html_e('Upgrade to Pro','mini-gallery');?></a>
            </div>
        </div>
        <?php
    }

    public function optimize_uploaded_image($file) {
        if (!$this->settings['enabled']) {
            return $file;
        }
        $file = wp_unslash($file);
        if (!in_array($file['type'], ['image/jpeg','image/png','image/gif','image/bmp'], true) || $file['size'] > 5*1024*1024) {
            return $file;
        }
        $quality   = (int) $this->settings['quality'];
        $keep_exif = $this->settings['keep_exif'] ? 'true':'false';
        $url       = add_query_arg(['qlty'=>$quality,'exif'=>$keep_exif], self::SMUSH_API_URL);

        $args = [
            'timeout'   => 30,
            'sslverify' => true,
            'headers'   => [
                'User-Agent'=> get_bloginfo('name').'/1.0',
                'Referer'   => home_url(),
            ],
            'body' => ['files'=> new CURLFile($file['tmp_name'],$file['type'],$file['name'])],
        ];
        $resp = wp_remote_post($url, $args);
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp)!==200) {
            if (WP_DEBUG) { error_log('Smush API error: '. wp_remote_retrieve_response_message($resp)); }
            return $file;
        }
        $data = json_decode(wp_remote_retrieve_body($resp));
        if (empty($data) || !empty($data->error)) {
            if (WP_DEBUG) { error_log('Smush failed: '.($data->error??'unknown')); }
            return $file;
        }
        $tmp = download_url($data->dest);
        if (is_wp_error($tmp)) { return $file; }
        if (copy($tmp,$file['tmp_name'])) { $file['size']=filesize($file['tmp_name']); }
        if (file_exists($tmp)) { wp_delete_file($tmp); }
        return $file;
    }

    public function sanitize_picogen_settings($input) {
        $input = wp_unslash($input);
        return [
            'api_token'     => sanitize_text_field($input['api_token']    ?? ''),
            'default_ratio' => sanitize_key($input['default_ratio'] ?? '16:9'),
            'enable_generate'=> isset($input['enable_generate']) ? (int)$input['enable_generate']:0,
        ];
    }

    public function save_picogen_settings() {
        $data = wp_unslash($_POST);
        if (empty($data['_wpnonce']) || !wp_verify_nonce($data['_wpnonce'],'mgwpp_picogen_settings_nonce')) {
            wp_die(esc_html__('Security check failed','mini-gallery')); }
        if (!current_user_can('manage_options')) { wp_die(esc_html__('Permission denied','mini-gallery')); }
        $settings = $data['mgwpp_picogen_settings'] ?? [];
        update_option('mgwpp_picogen_settings',$this->sanitize_picogen_settings($settings));
        set_transient('mgwpp_picogen_notice',esc_html__('Picogen settings saved','mini-gallery'),30);
        wp_safe_redirect(admin_url('admin.php?page=mgwpp-extensions&tab=picogen'));
        exit;
    }

    public function test_picogen_api() {
        check_ajax_referer('mgwpp_test_picogen','_wpnonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Permission denied','mini-gallery'));
        }
        $data = wp_unslash($_POST);
        $token = sanitize_text_field($data['api_token'] ?? '');
        if (empty($token)) {
            wp_send_json_error(esc_html__('API token is empty','mini-gallery'));
        }
        $resp = wp_remote_get('https://api.picogen.io/v1/account/info',[
            'timeout'=>15,
            'headers'=>['API-Token'=>$token],
        ]);
        if (is_wp_error($resp)) {
            wp_send_json_error($resp->get_error_message());
        }
        $body = wp_remote_retrieve_body($resp);
        $data = json_decode($body,true);
        if (!is_array($data)) {
            wp_send_json_error(esc_html__('Invalid API response','mini-gallery'));
        }
        if (!isset($data[1]['balance'])) {
            wp_send_json_error(esc_html__('Invalid account info response','mini-gallery'));
        }
        wp_send_json_success([
            'message'=>esc_html__('API connection successful!','mini-gallery'),
            'balance'=>$data[1]['balance'],
        ]);
    }
}

if (is_admin()) {
    new MGWPP_Extensions_View();
}
