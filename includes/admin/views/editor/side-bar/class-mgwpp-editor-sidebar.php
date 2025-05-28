<?php

/**
 * Enhanced Gallery Editor Sidebar
 * File: includes/admin/views/editor/class-mgwpp-editor-sidebar.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Editor_Sidebar
{
    private $gallery_data;
    private $media_items;

    public function __construct($gallery_data = [], $media_items = [])
    {
        $this->gallery_data = $gallery_data;
        $this->media_items = $media_items;
    }

    public function render()
    {
        ?>
        <div class="mgwpp-editor-sidebar">
            <?php $this->render_media_library_panel(); ?>
            <?php $this->render_properties_panel(); ?>
        </div>
        <?php
    }

    private function render_media_library_panel()
    {
        $media_panel = new MGWPP_Media_Panel($this->media_items);
        $media_panel->render();
    }

    private function render_properties_panel()
    {
        $properties_panel = new MGWPP_Properties_Panel();
        $properties_panel->render();
    }
}
