<?php
/* Properties Tabs File: includes/admin/views/editor/properties-panel/class-mgwpp-properties-tabs.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Properties_Tabs {
    public function __construct() {
        $this->render();
    }

    public function render() { ?>
        <div class="mgwpp-properties-tabs">
            <nav class="mgwpp-tab-nav">
                <?php $this->render_tab_headers(); ?>
            </nav>
            <?php $this->render_tab_contents(); ?>
        </div>
    <?php }

    private function render_tab_headers() {
        $tabs = ['content', 'design', 'animation', 'advanced'];
        foreach ($tabs as $tab) {
            echo '<button class="nav-tab" data-target="'.$tab.'">'.
                esc_html(ucfirst($tab)).'</button>';
        }
    }

    private function render_tab_contents() {
        new MGWPP_Content_Tab();
        new MGWPP_Design_Tab();
        new MGWPP_Animation_Tab();
        new MGWPP_Advanced_Tab();
    }
}