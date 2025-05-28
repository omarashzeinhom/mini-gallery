
<?php 
if (!defined('ABSPATH')) exit;

/* includes/admin/editor/properties-panel/tabs/class-mgwpp-layout-tab.php */
class MGWPP_Layout_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content" data-tab="layout">
            <div class="mgwpp-control-group">
                <label><?php _e('Position', 'mini-gallery'); ?></label>
                <div class="mgwpp-position-controls">
                    <div class="mgwpp-position-input">
                        <label><?php _e('X', 'mini-gallery'); ?></label>
                        <input type="number" id="item_position_x" class="mgwpp-pos-x" value="0">
                        <span>px</span>
                    </div>
                    <div class="mgwpp-position-input">
                        <label><?php _e('Y', 'mini-gallery'); ?></label>
                        <input type="number" id="item_position_y" class="mgwpp-pos-y" value="0">
                        <span>px</span>
                    </div>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Size', 'mini-gallery'); ?></label>
                <div class="mgwpp-size-controls">
                    <div class="mgwpp-size-input">
                        <label><?php _e('Width', 'mini-gallery'); ?></label>
                        <input type="number" id="item_width" class="mgwpp-width" value="100" min="1">
                        <span>px</span>
                    </div>
                    <div class="mgwpp-size-input">
                        <label><?php _e('Height', 'mini-gallery'); ?></label>
                        <input type="number" id="item_height" class="mgwpp-height" value="100" min="1">
                        <span>px</span>
                    </div>
                </div>
                <label>
                    <input type="checkbox" id="item_maintain_aspect">
                    <?php _e('Maintain aspect ratio', 'mini-gallery'); ?>
                </label>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Width Settings', 'mini-gallery'); ?></label>
                <div class="mgwpp-dimension-control">
                    <input type="number" id="item_width_value" class="mgwpp-width-value" min="0" max="100" value="100">
                    <select id="item_width_unit" class="mgwpp-width-unit">
                        <option value="%">%</option>
                        <option value="px">px</option>
                        <option value="auto">Auto</option>
                    </select>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Margin', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_margin" min="0" max="100" value="0" step="1">
                    <span class="mgwpp-range-value">0px</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Padding', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_padding" min="0" max="100" value="0" step="1">
                    <span class="mgwpp-range-value">0px</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Rotation', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_rotation" class="mgwpp-rotation" min="-180" max="180" value="0" step="1">
                    <span class="mgwpp-range-value">0°</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Layer Controls', 'mini-gallery'); ?></label>
                <div class="mgwpp-layer-controls">
                    <button type="button" class="button mgwpp-bring-to-front">
                        <?php _e('Bring to Front', 'mini-gallery'); ?>
                    </button>
                    <button type="button" class="button mgwpp-send-to-back">
                        <?php _e('Send to Back', 'mini-gallery'); ?>
                    </button>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Visibility Options', 'mini-gallery'); ?></label>
                <div class="mgwpp-visibility-options">
                    <label>
                        <input type="checkbox" id="item_hide_mobile">
                        <?php _e('Hide on mobile', 'mini-gallery'); ?>
                    </label>
                    <label>
                        <input type="checkbox" id="item_hide_tablet">
                        <?php _e('Hide on tablet', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>
        </div>
    <?php }
}