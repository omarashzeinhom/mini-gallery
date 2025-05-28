<?php
/* includes/admin/editor/properties-panel/tabs/class-mgwpp-animation-tab.php */
if (!defined('ABSPATH')) exit;

/* includes/admin/editor/properties-panel/tabs/class-mgwpp-animation-tab.php */
class MGWPP_Animation_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content" data-tab="animation">
            <div class="mgwpp-control-group">
                <label><?php _e('Entrance Animation', 'mini-gallery'); ?></label>
                <select id="item_entrance_animation" class="mgwpp-entrance-animation">
                    <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                    <option value="fadeIn"><?php _e('Fade In', 'mini-gallery'); ?></option>
                    <option value="slideInLeft"><?php _e('Slide In Left', 'mini-gallery'); ?></option>
                    <option value="slideInRight"><?php _e('Slide In Right', 'mini-gallery'); ?></option>
                    <option value="slideInUp"><?php _e('Slide In Up', 'mini-gallery'); ?></option>
                    <option value="slideInDown"><?php _e('Slide In Down', 'mini-gallery'); ?></option>
                    <option value="zoomIn"><?php _e('Zoom In', 'mini-gallery'); ?></option>
                    <option value="zoomOut"><?php _e('Zoom Out', 'mini-gallery'); ?></option>
                    <option value="bounceIn"><?php _e('Bounce In', 'mini-gallery'); ?></option>
                    <option value="rotateIn"><?php _e('Rotate In', 'mini-gallery'); ?></option>
                    <option value="flipInX"><?php _e('Flip In X', 'mini-gallery'); ?></option>
                    <option value="flipInY"><?php _e('Flip In Y', 'mini-gallery'); ?></option>
                </select>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Animation Duration', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_animation_duration" min="0.1" max="3" value="0.5" step="0.1">
                    <span class="mgwpp-range-value">0.5s</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Animation Delay', 'mini-gallery'); ?></label>
                <div class="mgwpp-range-control">
                    <input type="range" id="item_animation_delay" min="0" max="5" value="0" step="0.1">
                    <span class="mgwpp-range-value">0s</span>
                </div>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Hover Animation', 'mini-gallery'); ?></label>
                <select id="item_hover_animation">
                    <option value="none"><?php _e('None', 'mini-gallery'); ?></option>
                    <option value="scale"><?php _e('Scale', 'mini-gallery'); ?></option>
                    <option value="rotate"><?php _e('Rotate', 'mini-gallery'); ?></option>
                    <option value="shake"><?php _e('Shake', 'mini-gallery'); ?></option>
                    <option value="pulse"><?php _e('Pulse', 'mini-gallery'); ?></option>
                    <option value="bounce"><?php _e('Bounce', 'mini-gallery'); ?></option>
                </select>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Animation Easing', 'mini-gallery'); ?></label>
                <select id="item_animation_easing">
                    <option value="ease"><?php _e('Ease', 'mini-gallery'); ?></option>
                    <option value="ease-in"><?php _e('Ease In', 'mini-gallery'); ?></option>
                    <option value="ease-out"><?php _e('Ease Out', 'mini-gallery'); ?></option>
                    <option value="ease-in-out"><?php _e('Ease In Out', 'mini-gallery'); ?></option>
                    <option value="linear"><?php _e('Linear', 'mini-gallery'); ?></option>
                </select>
            </div>

            <div class="mgwpp-control-group">
                <label>
                    <input type="checkbox" id="item_animation_loop">
                    <?php _e('Loop animation', 'mini-gallery'); ?>
                </label>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Preview Animation', 'mini-gallery'); ?></label>
                <button type="button" class="button mgwpp-preview-animation">
                    <?php _e('Preview', 'mini-gallery'); ?>
                </button>
            </div>
        </div>
    <?php }
}

