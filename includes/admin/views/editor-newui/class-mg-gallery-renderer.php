<?php
/**
 * Gallery Renderer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class MG_Gallery_Renderer {
    
    public function render_gallery($gallery_id, $type = 'grid') {
        $gallery_manager = new MG_Gallery_Manager();
        $gallery_data = $gallery_manager->load_gallery($gallery_id);
        
        if (empty($gallery_data['items'])) {
            return '<div class="mg-gallery-empty">No items in this gallery.</div>';
        }
        
        $output = '<div class="mg-gallery mg-gallery-' . esc_attr($type) . '" data-gallery-id="' . esc_attr($gallery_id) . '">';
        
        switch ($type) {
            case 'masonry':
                $output .= $this->render_masonry($gallery_data['items']);
                break;
            case 'carousel':
                $output .= $this->render_carousel($gallery_data['items']);
                break;
            case 'slider':
                $output .= $this->render_slider($gallery_data['items']);
                break;
            case 'custom':
                $output .= $this->render_custom($gallery_data['items']);
                break;
            default:
                $output .= $this->render_grid($gallery_data['items']);
                break;
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    private function render_grid($items) {
        $output = '<div class="mg-gallery-grid">';
        
        foreach ($items as $item) {
            $output .= '<div class="mg-gallery-item" data-item-id="' . esc_attr($item['id']) . '">';
            $output .= $this->render_item($item);
            $output .= '</div>';
        }
        
        $output .= '</div>';
        return $output;
    }
    
    private function render_masonry($items) {
        $output = '<div class="mg-gallery-masonry">';
        
        foreach ($items as $item) {
            $output .= '<div class="mg-gallery-item masonry-item" data-item-id="' . esc_attr($item['id']) . '">';
            $output .= $this->render_item($item);
            $output .= '</div>';
        }
        
        $output .= '</div>';
        return $output;
    }
    
    private function render_carousel($items) {
        $output = '<div class="mg-gallery-carousel">';
        $output .= '<div class="carousel-container">';
        
        foreach ($items as $item) {
            $output .= '<div class="mg-gallery-item carousel-slide" data-item-id="' . esc_attr($item['id']) . '">';
            $output .= $this->render_item($item);
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '<button class="carousel-prev">‹</button>';
        $output .= '<button class="carousel-next">›</button>';
        $output .= '</div>';
        
        return $output;
    }
    
    private function render_slider($items) {
        $output = '<div class="mg-gallery-slider">';
        
        foreach ($items as $index => $item) {
            $active_class = $index === 0 ? ' active' : '';
            $output .= '<div class="mg-gallery-item slider-slide' . $active_class . '" data-item-id="' . esc_attr($item['id']) . '">';
            $output .= $this->render_item($item);
            $output .= '</div>';
        }
        
        $output .= '<div class="slider-dots">';
        foreach ($items as $index => $item) {
            $active_class = $index === 0 ? ' active' : '';
            $output .= '<button class="slider-dot' . $active_class . '" data-slide="' . $index . '"></button>';
        }
        $output .= '</div>';
        
        $output .= '</div>';
        return $output;
    }
    
    private function render_custom($items) {
        $output = '<div class="mg-gallery-custom" style="position: relative; min-height: 500px;">';
        
        foreach ($items as $item) {
            $styles = array();
            
            if (isset($item['position'])) {
                $styles[] = 'left: ' . intval($item['position']['x']) . 'px';
                $styles[] = 'top: ' . intval($item['position']['y']) . 'px';
            }
            
            if (isset($item['dimensions'])) {
                $styles[] = 'width: ' . intval($item['dimensions']['width']) . 'px';
                $styles[] = 'height: ' . intval($item['dimensions']['height']) . 'px';
            }
            
            if (isset($item['rotation'])) {
                $styles[] = 'transform: rotate(' . intval($item['rotation']) . 'deg)';
            }
            
            if (isset($item['zIndex'])) {
                $styles[] = 'z-index: ' . intval($item['zIndex']);
            }
            
            $style_attr = !empty($styles) ? ' style="position: absolute; ' . implode('; ', $styles) . '"' : '';
            
            $output .= '<div class="mg-gallery-item custom-positioned" data-item-id="' . esc_attr($item['id']) . '"' . $style_attr . '>';
            $output .= $this->render_item($item);
            $output .= '</div>';
        }
        
        $output .= '</div>';
        return $output;
    }
    
    private function render_item($item) {
        $output = '';
        
        switch ($item['type']) {
            case 'image':
                $output .= '<img src="' . esc_url($item['url']) . '" alt="' . esc_attr($item['title']) . '" loading="lazy">';
                break;
                
            case 'video':
                $output .= '<video controls>';
                $output .= '<source src="' . esc_url($item['url']) . '" type="video/mp4">';
                $output .= 'Your browser does not support the video tag.';
                $output .= '</video>';
                break;
                
            case 'text':
                $output .= '<div class="mg-text-content">' . wp_kses_post($item['content']) . '</div>';
                break;
                
            case 'button':
                $link = isset($item['link']) ? $item['link'] : '#';
                $target = isset($item['target']) ? $item['target'] : '_self';
                $output .= '<a href="' . esc_url($link) . '" target="' . esc_attr($target) . '" class="mg-button">';
                $output .= esc_html($item['content']);
                $output .= '</a>';
                break;
        }
        
        return $output;
    }
}
