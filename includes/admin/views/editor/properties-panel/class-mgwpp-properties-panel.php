<?php
/* Properties Panel File: includes/admin/views/editor/properties-panel/class-mgwpp-properties-panel.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Properties_Panel {
    public function render() { ?>
        <div class="mgwpp-properties-panel">
            <div class="mgwpp-panel-header">
                <h3><?php _e('Item Properties', 'mini-gallery'); ?></h3>
                <span class="mgwpp-selected-item-info"></span>
            </div>
            <div class="mgwpp-properties-content">
                <?php $this->render_properties_content(); ?>
            </div>
        </div>
    <?php }

    private function render_properties_content() {
        new MGWPP_Properties_Tabs();
    }
}