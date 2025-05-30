<?php
/**
 * Gallery Renderer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MGWPP_Gallery_Renderer {
    public function render_gallery($gallery_id) {
        // Load gallery data
        global $wpdb;
        $table_name = $wpdb->prefix . 'mgwpp_galleries';
        $gallery = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d", $gallery_id
        ), ARRAY_A);
        
        if (!$gallery) return '';
        
        $slides = json_decode($gallery['slides'], true);
        $settings = json_decode($gallery['settings'], true);
        
        ob_start();
        ?>
        <div class="mgwpp-gallery" 
             data-show-nav="<?= esc_attr($settings['show_nav']) ?>" 
             data-show-pagination="<?= esc_attr($settings['show_pagination']) ?>">
            
            <?php foreach ($slides as $index => $slide): ?>
            <div class="mgwpp-slide" 
                 style="background-color: <?= esc_attr($slide['background']['color']) ?>;
                        <?= $slide['background']['image'] ? 'background-image: url(' . esc_url($slide['background']['image']) . ');' : '' ?>"
                 data-animation-in="<?= esc_attr($slide['animation']['in']) ?>"
                 data-animation-out="<?= esc_attr($slide['animation']['out']) ?>">
                
                <?php foreach ($slide['elements'] as $element): ?>
                <div class="mgwpp-element mgwpp-<?= esc_attr($element['type']) ?>"
                     style="left: <?= $element['position']['x'] ?>px;
                            top: <?= $element['position']['y'] ?>px;
                            width: <?= $element['size']['width'] ?>px;
                            height: <?= $element['size']['height'] ?>px;">
                    <?= $this->render_element($element) ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_element($element) {
        switch ($element['type']) {
            case 'text':
                return '<div>' . esc_html($element['content']) . '</div>';
            case 'button':
                return '<button>' . esc_html($element['content']) . '</button>';
            case 'image':
                return '<img src="' . esc_url($element['image_url']) . '" alt="' . esc_attr($element['alt']) . '">';
        }
    }
}