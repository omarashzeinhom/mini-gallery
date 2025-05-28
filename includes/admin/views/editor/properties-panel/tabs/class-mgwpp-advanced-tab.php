<?php
/* includes/admin/editor/properties-panel/tabs/class-mgwpp-advanced-tab.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Advanced_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content" data-tab="advanced">
            <div class="mgwpp-control-group">
                <label><?php _e('Custom CSS Class', 'mini-gallery'); ?></label>
                <input type="text" id="item_custom_class" class="mgwpp-custom-class" placeholder="<?php _e('custom-class-name', 'mini-gallery'); ?>">
                <p class="description"><?php _e('Add custom CSS classes separated by spaces', 'mini-gallery'); ?></p>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Custom CSS', 'mini-gallery'); ?></label>
                <textarea id="item_custom_css" class="mgwpp-custom-css" rows="10" placeholder="<?php _e('/* Custom CSS for this item */', 'mini-gallery'); ?>"></textarea>
                <p class="description"><?php _e('Add custom CSS rules for this specific item', 'mini-gallery'); ?></p>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('HTML Attributes', 'mini-gallery'); ?></label>
                <div class="mgwpp-attributes-list">
                    <div class="mgwpp-attribute-row">
                        <input type="text" placeholder="<?php _e('Attribute', 'mini-gallery'); ?>" class="mgwpp-attr-name">
                        <input type="text" placeholder="<?php _e('Value', 'mini-gallery'); ?>" class="mgwpp-attr-value">
                        <button type="button" class="button mgwpp-remove-attribute">×</button>
                    </div>
                </div>
                <button type="button" class="button mgwpp-add-attribute">
                    <?php _e('Add Attribute', 'mini-gallery'); ?>
                </button>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Link Settings', 'mini-gallery'); ?></label>
                <div class="mgwpp-link-settings">
                    <input type="url" id="item_link_url" placeholder="<?php _e('Link URL...', 'mini-gallery'); ?>">
                    <label>
                        <input type="checkbox" id="item_link_new_tab">
                        <?php _e('Open in new tab', 'mini-gallery'); ?>
                    </label>
                    <input type="text" id="item_link_title" placeholder="<?php _e('Link title...', 'mini-gallery'); ?>">
                    <label>
                        <input type="checkbox" id="item_link_nofollow">
                        <?php _e('Add nofollow', 'mini-gallery'); ?>
                    </label>
                    <label>
                        <input type="checkbox" id="item_link_sponsored">
                        <?php _e('Add sponsored', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Conditional Display', 'mini-gallery'); ?></label>
                <div class="mgwpp-conditional-display">
                    <select id="item_display_condition">
                        <option value="always"><?php _e('Always Show', 'mini-gallery'); ?></option>
                        <option value="logged_in"><?php _e('Logged In Users Only', 'mini-gallery'); ?></option>
                        <option value="logged_out"><?php _e('Logged Out Users Only', 'mini-gallery'); ?></option>
                        <option value="user_role"><?php _e('Specific User Role', 'mini-gallery'); ?></option>
                        <option value="date_range"><?php _e('Date Range', 'mini-gallery'); ?></option>
                        <option value="device"><?php _e('Device Type', 'mini-gallery'); ?></option>
                    </select>
                    
                    <div class="mgwpp-condition-details" style="display: none;">
                        <div class="mgwpp-user-role-condition">
                            <select id="item_user_role" multiple>
                                <option value="administrator"><?php _e('Administrator', 'mini-gallery'); ?></option>
                                <option value="editor"><?php _e('Editor', 'mini-gallery'); ?></option>
                                <option value="author"><?php _e('Author', 'mini-gallery'); ?></option>
                                <option value="contributor"><?php _e('Contributor', 'mini-gallery'); ?></option>
                                <option value="subscriber"><?php _e('Subscriber', 'mini-gallery'); ?></option>
                                <option value="customer"><?php _e('Customer', 'mini-gallery'); ?></option>
                            </select>
                        </div>
                        
                        <div class="mgwpp-date-range-condition">
                            <label><?php _e('Start Date', 'mini-gallery'); ?></label>
                            <input type="datetime-local" id="item_start_date">
                            <label><?php _e('End Date', 'mini-gallery'); ?></label>
                            <input type="datetime-local" id="item_end_date">
                        </div>

                        <div class="mgwpp-device-condition">
                            <label>
                                <input type="checkbox" name="item_device_type" value="desktop">
                                <?php _e('Desktop', 'mini-gallery'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="item_device_type" value="tablet">
                                <?php _e('Tablet', 'mini-gallery'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="item_device_type" value="mobile">
                                <?php _e('Mobile', 'mini-gallery'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('SEO Settings', 'mini-gallery'); ?></label>
                <div class="mgwpp-seo-settings">
                    <input type="text" id="item_seo_title" placeholder="<?php _e('SEO Title (max 60 chars)', 'mini-gallery'); ?>" maxlength="60">
                    <textarea id="item_seo_description" rows="3" placeholder="<?php _e('SEO Description (max 160 chars)', 'mini-gallery'); ?>" maxlength="160"></textarea>
                    <label>
                        <input type="checkbox" id="item_noindex">
                        <?php _e('Prevent search engines from indexing this item', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Accessibility', 'mini-gallery'); ?></label>
                <div class="mgwpp-accessibility-settings">
                    <input type="text" id="item_aria_label" placeholder="<?php _e('ARIA label text...', 'mini-gallery'); ?>">
                    <input type="text" id="item_aria_describedby" placeholder="<?php _e('ARIA describedby ID...', 'mini-gallery'); ?>">
                    <input type="text" id="item_aria_labelledby" placeholder="<?php _e('ARIA labelledby ID...', 'mini-gallery'); ?>">
                    <label>
                        <input type="checkbox" id="item_tabindex">
                        <?php _e('Make item focusable (tabindex="0")', 'mini-gallery'); ?>
                    </label>
                    <label>
                        <input type="checkbox" id="item_aria_hidden">
                        <?php _e('Hide from screen readers (aria-hidden)', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Advanced Behavior', 'mini-gallery'); ?></label>
                <div class="mgwpp-behavior-settings">
                    <label>
                        <input type="checkbox" id="item_lazy_load">
                        <?php _e('Enable lazy loading', 'mini-gallery'); ?>
                    </label>
                    <label>
                        <input type="checkbox" id="item_defer_loading">
                        <?php _e('Defer loading until in viewport', 'mini-gallery'); ?>
                    </label>
                    <label>
                        <input type="checkbox" id="item_preload">
                        <?php _e('Preload this item', 'mini-gallery'); ?>
                    </label>
                    <div class="mgwpp-loading-method">
                        <label><?php _e('Loading Method:', 'mini-gallery'); ?></label>
                        <select id="item_loading_method">
                            <option value="auto"><?php _e('Auto', 'mini-gallery'); ?></option>
                            <option value="eager"><?php _e('Eager', 'mini-gallery'); ?></option>
                            <option value="lazy"><?php _e('Lazy', 'mini-gallery'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Custom Data Attributes', 'mini-gallery'); ?></label>
                <div class="mgwpp-data-attributes">
                    <div class="mgwpp-data-attribute-row">
                        <input type="text" class="mgwpp-data-attr-name" placeholder="<?php _e('data-attribute', 'mini-gallery'); ?>">
                        <input type="text" class="mgwpp-data-attr-value" placeholder="<?php _e('value', 'mini-gallery'); ?>">
                        <button type="button" class="button mgwpp-remove-data-attribute">×</button>
                    </div>
                    <button type="button" class="button mgwpp-add-data-attribute">
                        <?php _e('Add Data Attribute', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php }
}