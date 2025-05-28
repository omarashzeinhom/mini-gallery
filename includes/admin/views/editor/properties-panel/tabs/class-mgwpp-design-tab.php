<?php
/* includes/admin/editor/properties-panel/tabs/class-mgwpp-design-tab.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Design_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content" data-tab="design">
            <!-- Size Controls -->
            <div class="mgwpp-control-group">
                <label><?php _e('Width', 'mini-gallery'); ?></label>
                <div class="mgwpp-dimension-control">
                    <input type="number" class="mgwpp-width-value" min="0" max="100" value="100">
                    <select class="mgwpp-width-unit">
                        <option value="%">%</option>
                        <option value="px">px</option>
                        <option value="auto">Auto</option>
                    </select>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Height', 'mini-gallery'); ?></label>
                <div class="mgwpp-dimension-control">
                    <input type="number" class="mgwpp-height-value" min="0" value="auto">
                    <select class="mgwpp-height-unit">
                        <option value="px">px</option>
                        <option value="%">%</option>
                        <option value="vh">vh</option>
                    </select>
                </div>
            </div>

            <!-- Spacing -->
            <div class="mgwpp-control-group">
                <label><?php _e('Spacing', 'mini-gallery'); ?></label>
                <div class="mgwpp-spacing-control">
                    <div class="mgwpp-spacing-input">
                        <label><?php _e('Margin', 'mini-gallery'); ?></label>
                        <input type="number" class="mgwpp-margin" placeholder="-" min="0">
                    </div>
                    <div class="mgwpp-spacing-input">
                        <label><?php _e('Padding', 'mini-gallery'); ?></label>
                        <input type="number" class="mgwpp-padding" placeholder="-" min="0">
                    </div>
                </div>
            </div>

            <!-- Background -->
            <div class="mgwpp-control-group">
                <label><?php _e('Background', 'mini-gallery'); ?></label>
                <div class="mgwpp-background-controls">
                    <input type="color" class="mgwpp-background-color" value="#ffffff">
                    <select class="mgwpp-background-type">
                        <option value="solid"><?php _e('Solid', 'mini-gallery'); ?></option>
                        <option value="gradient"><?php _e('Gradient', 'mini-gallery'); ?></option>
                        <option value="image"><?php _e('Image', 'mini-gallery'); ?></option>
                    </select>
                    <button class="button mgwpp-upload-bg-image"><span class="dashicons dashicons-upload"></span></button>
                </div>
            </div>

            <!-- Border -->
            <div class="mgwpp-control-group">
                <label><?php _e('Border', 'mini-gallery'); ?></label>
                <div class="mgwpp-border-controls">
                    <input type="number" class="mgwpp-border-width" min="0" value="0" placeholder="<?php _e('Width', 'mini-gallery'); ?>">
                    <select class="mgwpp-border-style">
                        <option value="solid"><?php _e('Solid', 'mini-gallery'); ?></option>
                        <option value="dashed"><?php _e('Dashed', 'mini-gallery'); ?></option>
                        <option value="dotted"><?php _e('Dotted', 'mini-gallery'); ?></option>
                    </select>
                    <input type="color" class="mgwpp-border-color" value="#000000">
                </div>
            </div>

            <!-- Border Radius -->
            <div class="mgwpp-control-group">
                <label><?php _e('Border Radius', 'mini-gallery'); ?></label>
                <div class="mgwpp-border-radius-controls">
                    <input type="range" class="mgwpp-border-radius" min="0" max="50" value="0">
                    <span class="mgwpp-range-value">0px</span>
                    <button class="button mgwpp-border-radius-reset"><?php _e('Reset', 'mini-gallery'); ?></button>
                </div>
            </div>

            <!-- Box Shadow -->
            <div class="mgwpp-control-group">
                <label><?php _e('Box Shadow', 'mini-gallery'); ?></label>
                <div class="mgwpp-shadow-controls">
                    <input type="number" class="mgwpp-shadow-x" placeholder="X" min="0">
                    <input type="number" class="mgwpp-shadow-y" placeholder="Y" min="0">
                    <input type="number" class="mgwpp-shadow-blur" placeholder="Blur" min="0">
                    <input type="number" class="mgwpp-shadow-spread" placeholder="Spread" min="0">
                    <input type="color" class="mgwpp-shadow-color" value="rgba(0,0,0,0.1)">
                </div>
            </div>

            <!-- Hover Effects -->
            <div class="mgwpp-control-group">
                <label><?php _e('Hover Effects', 'mini-gallery'); ?></label>
                <div class="mgwpp-hover-controls">
                    <select class="mgwpp-hover-effect">
                        <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                        <option value="zoom"><?php _e('Zoom', 'mini-gallery'); ?></option>
                        <option value="lift"><?php _e('Lift', 'mini-gallery'); ?></option>
                        <option value="fade"><?php _e('Fade', 'mini-gallery'); ?></option>
                    </select>
                    <input type="number" class="mgwpp-hover-transition" min="0" max="1" step="0.1" value="0.3" placeholder="<?php _e('Duration', 'mini-gallery'); ?>">
                </div>
            </div>

            <!-- Typography -->
            <div class="mgwpp-control-group">
                <label><?php _e('Typography', 'mini-gallery'); ?></label>
                <div class="mgwpp-typography-controls">
                    <select class="mgwpp-font-family">
                        <option value="inherit"><?php _e('Inherit', 'mini-gallery'); ?></option>
                        <option value="Arial">Arial</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Times New Roman">Times New Roman</option>
                    </select>
                    <input type="number" class="mgwpp-font-size" min="8" max="72" value="16">
                    <select class="mgwpp-font-weight">
                        <option value="400"><?php _e('Normal', 'mini-gallery'); ?></option>
                        <option value="700"><?php _e('Bold', 'mini-gallery'); ?></option>
                        <option value="300"><?php _e('Light', 'mini-gallery'); ?></option>
                    </select>
                    <input type="color" class="mgwpp-text-color" value="#000000">
                </div>
            </div>

            <!-- Opacity -->
            <div class="mgwpp-control-group">
                <label><?php _e('Opacity', 'mini-gallery'); ?></label>
                <input type="range" class="mgwpp-opacity" min="0" max="1" step="0.1" value="1">
                <span class="mgwpp-range-value">100%</span>
            </div>
        </div>
    <?php }
}