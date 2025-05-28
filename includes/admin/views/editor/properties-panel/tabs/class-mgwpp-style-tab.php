<?php 
if (!defined('ABSPATH')) exit;


/* includes/admin/editor/properties-panel/tabs/class-mgwpp-style-tab.php */
class MGWPP_Style_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content" data-tab="style">
            <div class="mgwpp-control-group">
                <label><?php _e('Background Color', 'mini-gallery'); ?></label>
                <input type="color" id="item_background_color" class="mgwpp-color-picker" value="#ffffff">
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Border Radius', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_border_radius" min="0" max="50" value="0" step="1">
                    <span class="mgwpp-range-value">0px</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Border', 'mini-gallery'); ?></label>
                <div class="mgwpp-border-controls">
                    <div class="mgwpp-border-width">
                        <label><?php _e('Width', 'mini-gallery'); ?></label>
                        <input type="number" id="item_border_width" min="0" max="20" value="0">
                        <span>px</span>
                    </div>
                    <div class="mgwpp-border-style">
                        <label><?php _e('Style', 'mini-gallery'); ?></label>
                        <select id="item_border_style">
                            <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                            <option value="solid"><?php _e('Solid', 'mini-gallery'); ?></option>
                            <option value="dashed"><?php _e('Dashed', 'mini-gallery'); ?></option>
                            <option value="dotted"><?php _e('Dotted', 'mini-gallery'); ?></option>
                        </select>
                    </div>
                    <div class="mgwpp-border-color">
                        <label><?php _e('Color', 'mini-gallery'); ?></label>
                        <input type="color" id="item_border_color" value="#000000">
                    </div>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Shadow', 'mini-gallery'); ?></label>
                <div class="mgwpp-shadow-controls">
                    <label>
                        <input type="checkbox" id="item_enable_shadow">
                        <?php _e('Enable shadow', 'mini-gallery'); ?>
                    </label>
                    <div class="mgwpp-shadow-settings" style="display: none;">
                        <div class="mgwpp-shadow-input">
                            <label><?php _e('Horizontal', 'mini-gallery'); ?></label>
                            <input type="number" id="item_shadow_x" value="0">
                        </div>
                        <div class="mgwpp-shadow-input">
                            <label><?php _e('Vertical', 'mini-gallery'); ?></label>
                            <input type="number" id="item_shadow_y" value="0">
                        </div>
                        <div class="mgwpp-shadow-input">
                            <label><?php _e('Blur', 'mini-gallery'); ?></label>
                            <input type="number" id="item_shadow_blur" value="5" min="0">
                        </div>
                        <div class="mgwpp-shadow-input">
                            <label><?php _e('Color', 'mini-gallery'); ?></label>
                            <input type="color" id="item_shadow_color" value="#000000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Opacity', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_opacity" min="0" max="100" value="100" step="1">
                    <span class="mgwpp-range-value">100%</span>
                </div>
            </div>

            <!-- Text-specific styles -->
            <div class="mgwpp-text-styles" style="display: none;">
                <div class="mgwpp-control-group">
                    <label><?php _e('Font Family', 'mini-gallery'); ?></label>
                    <select id="item_font_family">
                        <option value="inherit"><?php _e('Inherit', 'mini-gallery'); ?></option>
                        <option value="Arial, sans-serif">Arial</option>
                        <option value="Helvetica, sans-serif">Helvetica</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="Times New Roman, serif">Times New Roman</option>
                        <option value="Courier New, monospace">Courier New</option>
                    </select>
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Font Size', 'mini-gallery'); ?></label>
                    <div class="mgwpp-range-control">
                        <input type="range" id="item_font_size" min="8" max="72" value="16" step="1">
                        <span class="mgwpp-range-value">16px</span>
                    </div>
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Text Color', 'mini-gallery'); ?></label>
                    <input type="color" id="item_text_color" value="#000000">
                </div>

                <div class="mgwpp-control-group">
                    <label><?php _e('Text Align', 'mini-gallery'); ?></label>
                    <select id="item_text_align">
                        <option value="left"><?php _e('Left', 'mini-gallery'); ?></option>
                        <option value="center"><?php _e('Center', 'mini-gallery'); ?></option>
                        <option value="right"><?php _e('Right', 'mini-gallery'); ?></option>
                        <option value="justify"><?php _e('Justify', 'mini-gallery'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    <?php }
}

